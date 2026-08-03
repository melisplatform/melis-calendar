import { useEffect, useMemo, useRef, useState, type CSSProperties, type DragEvent, type PointerEvent as ReactPointerEvent } from 'react'
import { fetchEvents, fetchCalStats, saveEvent, deleteEvent, type CalEvent, type CalStats } from './calendar-api'
import { ViewToggle } from './ViewToggle'

/* ──────────────────────────────────────────────────────────────────────────
 * Brique Calendrier (MelisCalendar) — full React, calendrier MENSUEL custom + drag-drop natif.
 * À gauche : on tape un titre d'événement et on le GLISSE sur un jour → crée l'event ce jour-là.
 * Sur la grille : un event se glisse d'un jour à l'autre → rééchelonnement. Clic → éditer/supprimer.
 * Un event = titre + date de début + date de fin (dates seules). La brique ne peut pas importer les
 * modules de l'hôte : styles inline + variables CSS du thème, mini-dico FR/EN via <html lang>.
 * ────────────────────────────────────────────────────────────────────────── */

const MELIS_KEY_IFRAME = 'meliscalendar_tool'     // zone rendable legacy (vue « Old »)
const CAPS_KEY = 'meliscalendar_tool'              // nœud FEUILLE = où RightsTreeView rend les caps (cf. react.capabilities.php)

function can(cap: string): boolean {
  return (window as unknown as { MelisCan?: (k: string, c: string) => boolean }).MelisCan?.(CAPS_KEY, cap) ?? true
}
function notify(kind: 'ok' | 'ko', title: string, message: string) {
  window.postMessage({ __melisNotif: true, kind, title, message }, '*')
}

// ── i18n ──
type Lang = 'fr' | 'en'
function currentLang(): Lang {
  const l = (document.documentElement.lang || 'en').toLowerCase()
  return l.startsWith('fr') ? 'fr' : 'en'
}
const bcp = () => (currentLang() === 'fr' ? 'fr-FR' : 'en-GB')
const DICT: Record<Lang, Record<string, string>> = {
  fr: {
    title: 'Calendrier', subtitle: 'Glissez un événement sur un jour, ou déplacez-le pour le rééchelonner',
    new_event: 'Nouvel événement', new_event_ph: 'Titre de l’événement…', drag_hint: 'Glissez ce bloc sur un jour du calendrier',
    tap_hint: 'Touchez un jour du calendrier pour placer « {name} » (touchez à nouveau le bloc pour annuler)',
    today: 'Aujourd’hui', month_events: 'Événements du mois', no_events: 'Aucun événement ce mois',
    kpi_total: 'Total', kpi_upcoming: 'À venir',
    edit_title: 'Modifier l’événement', new_title: 'Nouvel événement', f_title: 'Titre', f_start: 'Début', f_end: 'Fin',
    save: 'Enregistrer', cancel: 'Annuler', del: 'Supprimer', saved: 'Enregistré ✓', err_title: 'Le titre est obligatoire.',
    del_title: 'Confirmer la suppression', del_msg: 'Supprimer l’événement « {name} » ? Cette action est irréversible.',
    no_access: 'Vous n’avez pas les droits pour consulter le calendrier.',
  },
  en: {
    title: 'Calendar', subtitle: 'Drag an event onto a day, or move it to reschedule',
    new_event: 'New event', new_event_ph: 'Event title…', drag_hint: 'Drag this block onto a calendar day',
    tap_hint: 'Tap a calendar day to place "{name}" (tap the block again to cancel)',
    today: 'Today', month_events: 'This month’s events', no_events: 'No event this month',
    kpi_total: 'Total', kpi_upcoming: 'Upcoming',
    edit_title: 'Edit event', new_title: 'New event', f_title: 'Title', f_start: 'Start', f_end: 'End',
    save: 'Save', cancel: 'Cancel', del: 'Delete', saved: 'Saved ✓', err_title: 'Title is required.',
    del_title: 'Confirm deletion', del_msg: 'Delete event "{name}"? This cannot be undone.',
    no_access: 'You do not have permission to view the calendar.',
  },
}
function useT() {
  const lang = currentLang()
  return (key: string, vars?: Record<string, string | number>) => {
    let s = DICT[lang][key] ?? key
    if (vars) for (const [k, v] of Object.entries(vars)) s = s.replaceAll(`{${k}}`, String(v))
    return s
  }
}

// ── Dates (YYYY-MM-DD en LOCAL, pas d'UTC) ──
const pad = (n: number) => String(n).padStart(2, '0')
function fmtISO(d: Date): string { return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}` }
function parseISO(s: string): Date { const [y, m, d] = s.split('-').map(Number); return new Date(y, (m || 1) - 1, d || 1) }
function addDays(iso: string, n: number): string { const d = parseISO(iso); d.setDate(d.getDate() + n); return fmtISO(d) }
function diffDays(aIso: string, bIso: string): number { return Math.round((parseISO(aIso).getTime() - parseISO(bIso).getTime()) / 86400000) }
function todayISO(): string { return fmtISO(new Date()) }
function fmtHuman(iso: string): string { return new Intl.DateTimeFormat(bcp(), { day: '2-digit', month: 'short', year: 'numeric' }).format(parseISO(iso)) }
// Grille de 6 semaines (lundi → dimanche) couvrant le mois donné.
function monthGrid(year: number, month0: number): string[][] {
  const first = new Date(year, month0, 1)
  const startOffset = (first.getDay() + 6) % 7 // lundi = 0
  const gridStart = new Date(year, month0, 1 - startOffset)
  const weeks: string[][] = []
  for (let w = 0; w < 6; w++) {
    const row: string[] = []
    for (let d = 0; d < 7; d++) { const cur = new Date(gridStart); cur.setDate(gridStart.getDate() + w * 7 + d); row.push(fmtISO(cur)) }
    weeks.push(row)
  }
  return weeks
}

// ── Styles ──
const card: CSSProperties = { border: '1px solid var(--color-border)', background: 'var(--color-card)', borderRadius: 12, boxShadow: '0 1px 2px rgba(0,0,0,.04)' }
const inputCss: CSSProperties = { height: 38, width: '100%', boxSizing: 'border-box', borderRadius: 8, border: '1px solid var(--color-input,var(--color-border))', background: 'var(--color-card)', color: 'var(--color-foreground)', padding: '0 12px', fontSize: 14, outline: 'none' }
const btnPrimary: CSSProperties = { display: 'inline-flex', alignItems: 'center', gap: 6, height: 36, padding: '0 14px', borderRadius: 8, border: 0, background: 'var(--color-primary)', color: 'var(--color-primary-foreground,#fff)', fontSize: 14, fontWeight: 500, cursor: 'pointer' }
const btnGhost: CSSProperties = { display: 'inline-flex', alignItems: 'center', gap: 6, height: 36, padding: '0 12px', borderRadius: 8, border: '1px solid var(--color-border)', background: 'var(--color-card)', color: 'var(--color-foreground)', fontSize: 14, cursor: 'pointer' }
const label: CSSProperties = { display: 'block', fontSize: 13, fontWeight: 500, marginBottom: 4, color: 'var(--color-foreground)' }
const eventChip = (moving: boolean): CSSProperties => ({
  display: 'block', width: '100%', boxSizing: 'border-box', textAlign: 'left', border: 0, borderRadius: 5, padding: '2px 6px',
  marginTop: 2, fontSize: 12, lineHeight: 1.3, cursor: 'grab', background: 'color-mix(in srgb, var(--color-primary) 16%, transparent)',
  color: 'var(--color-primary)', fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', opacity: moving ? 0.4 : 1,
})

// Charge utile d'un glisser : soit un nouvel event (bloc de gauche), soit un event existant (rééchelonnement).
type DragPayload =
  | { kind: 'new'; title: string }
  | { kind: 'move'; id: number; title: string; start: string; end: string }

function Kpi({ label: lbl, value }: { label: string; value: number | null }) {
  return (
    <div style={{ ...card, display: 'flex', flexDirection: 'column', gap: 2, padding: 14, flex: 1, minWidth: 110 }}>
      <span style={{ fontSize: 12, color: 'var(--color-muted-foreground)' }}>{lbl}</span>
      <span style={{ fontSize: 22, fontWeight: 700 }}>{value == null ? '…' : value}</span>
    </div>
  )
}

// ════════════════════════════════════════════════════════════════════════════
// Brique persistante (manifest) : elle ne lit PAS le route global → aucun freeze nécessaire.
export default function CalendarPage() {
  const t = useT()
  const now = new Date()
  const [year, setYear] = useState(now.getFullYear())
  const [month0, setMonth0] = useState(now.getMonth())
  const [events, setEvents] = useState<CalEvent[]>([])
  const [stats, setStats] = useState<CalStats | null>(null)
  const [tick, setTick] = useState(0)
  const [newTitle, setNewTitle] = useState('')
  const [dragId, setDragId] = useState<number | null>(null)
  const [overDay, setOverDay] = useState<string | null>(null)
  const [armed, setArmed] = useState<DragPayload | null>(null)          // tactile : bloc « armé » par un tap, à poser sur un jour
  const [ghost, setGhost] = useState<{ x: number; y: number; label: string } | null>(null)
  const [editing, setEditing] = useState<CalEvent | 'new' | null>(null)
  const [mode, setMode] = useState<'react' | 'iframe'>('react')
  const [frameLoaded, setFrameLoaded] = useState(false)

  // Responsive : on mesure le CONTENEUR (pas la fenêtre) — la brique vit dans un panneau du shell.
  const rootRef = useRef<HTMLDivElement>(null)
  const [narrow, setNarrow] = useState(false)
  useEffect(() => {
    const el = rootRef.current
    if (!el || typeof ResizeObserver === 'undefined') return
    const ro = new ResizeObserver((entries) => setNarrow(entries[0].contentRect.width < 760))
    ro.observe(el)
    return () => ro.disconnect()
  }, [])

  const weeks = useMemo(() => monthGrid(year, month0), [year, month0])
  const rangeFrom = weeks[0][0]
  const rangeTo = weeks[5][6]

  useEffect(() => { fetchEvents({ from: rangeFrom, to: rangeTo }).then(setEvents).catch(() => setEvents([])) }, [rangeFrom, rangeTo, tick])
  useEffect(() => { fetchCalStats().then(setStats).catch(() => null) }, [tick])

  const reload = () => setTick((x) => x + 1)

  const eventsOn = (dayIso: string) => events.filter((e) => e.start <= dayIso && dayIso <= e.end)
  const monthEvents = useMemo(
    () => [...events].filter((e) => e.start <= rangeTo && e.end >= rangeFrom).sort((a, b) => a.start.localeCompare(b.start)),
    [events, rangeFrom, rangeTo]
  )

  function gotoMonth(delta: number) {
    let m = month0 + delta, y = year
    if (m < 0) { m = 11; y-- } else if (m > 11) { m = 0; y++ }
    setMonth0(m); setYear(y)
  }
  function gotoToday() { const d = new Date(); setYear(d.getFullYear()); setMonth0(d.getMonth()) }

  // ── Dépôt sur un jour (commun au drag natif souris ET au drag tactile) ──
  function applyDrop(payload: DragPayload, dayIso: string) {
    if (payload.kind === 'new') {
      const title = payload.title.trim()
      if (!title || !can('create')) return
      saveEvent({ title, start: dayIso, end: dayIso }).then(() => { setNewTitle(''); reload(); notify('ok', t('title'), t('saved')) }).catch((err) => notify('ko', t('title'), err.message))
    } else if (payload.kind === 'move' && payload.id) {
      if (!can('edit')) return
      if (payload.start === dayIso) return // pas de changement
      const duration = Math.max(0, diffDays(payload.end, payload.start))
      saveEvent({ id: payload.id, title: payload.title, start: dayIso, end: addDays(dayIso, duration) })
        .then(() => { reload(); notify('ok', t('title'), t('saved')) }).catch((err) => notify('ko', t('title'), err.message))
    }
  }

  // ── Drag-drop natif (souris / desktop) ──
  function onDropDay(e: DragEvent, dayIso: string) {
    e.preventDefault(); setOverDay(null); setDragId(null)
    let payload: DragPayload | null = null
    try { payload = JSON.parse(e.dataTransfer.getData('application/json') || e.dataTransfer.getData('text/plain')) } catch { /* */ }
    if (payload) applyDrop(payload, dayIso)
  }

  /* ── Drag TACTILE (Pointer Events) ───────────────────────────────────────
   * Le drag-drop HTML5 natif ne se déclenche JAMAIS au doigt (aucun dragstart
   * sur touch) → sur mobile la poignée est inerte. On double donc le mécanisme :
   * pointerType 'mouse' → on laisse faire le natif (zéro régression desktop) ;
   * sinon on suit le doigt nous-mêmes (fantôme + hit-test sur [data-day]).
   * Un simple tap (sans déplacement) « arme » le bloc : le jour tapé ensuite
   * reçoit l'event — indispensable en vue étroite, où le bloc est SOUS la grille.
   * ──────────────────────────────────────────────────────────────────────── */
  const touchDrag = useRef<{ payload: DragPayload; x0: number; y0: number; moved: boolean; tappable: boolean } | null>(null)
  const overDayRef = useRef<string | null>(null)
  const justDragged = useRef(false)          // un drag tactile se termine par un click « fantôme » : on l'ignore
  const autoScrollDy = useRef(0)
  const autoScrollTimer = useRef<number | null>(null)

  function stopAutoScroll() {
    if (autoScrollTimer.current !== null) { window.clearInterval(autoScrollTimer.current); autoScrollTimer.current = null }
    autoScrollDy.current = 0
  }
  // Le conteneur racine scrolle en vue étroite : on le fait défiler quand le doigt approche d'un bord.
  function autoScroll(clientY: number) {
    const el = rootRef.current
    if (!el) return
    const r = el.getBoundingClientRect()
    const EDGE = 72
    autoScrollDy.current = clientY < r.top + EDGE ? -14 : clientY > r.bottom - EDGE ? 14 : 0
    if (autoScrollDy.current === 0) { stopAutoScroll(); return }
    if (autoScrollTimer.current === null) {
      autoScrollTimer.current = window.setInterval(() => {
        const node = rootRef.current
        if (node) node.scrollTop += autoScrollDy.current
      }, 16)
    }
  }
  useEffect(() => stopAutoScroll, [])

  function onDragPointerDown(e: ReactPointerEvent, payload: DragPayload, tappable: boolean) {
    if (e.pointerType === 'mouse') return                 // desktop : drag natif
    justDragged.current = false
    touchDrag.current = { payload, x0: e.clientX, y0: e.clientY, moved: false, tappable }
    try { (e.currentTarget as HTMLElement).setPointerCapture(e.pointerId) } catch { /* */ }
  }
  function onDragPointerMove(e: ReactPointerEvent) {
    const d = touchDrag.current
    if (!d) return
    if (!d.moved && Math.hypot(e.clientX - d.x0, e.clientY - d.y0) < 8) return
    if (!d.moved) { d.moved = true; setArmed(null); if (d.payload.kind === 'move') setDragId(d.payload.id) }
    setGhost({ x: e.clientX, y: e.clientY, label: d.payload.title.trim() || t('new_event') })
    const cell = (document.elementFromPoint(e.clientX, e.clientY) as HTMLElement | null)?.closest('[data-day]')
    const day = cell?.getAttribute('data-day') ?? null
    overDayRef.current = day
    setOverDay(day)
    autoScroll(e.clientY)
  }
  function onDragPointerUp() {
    const d = touchDrag.current
    touchDrag.current = null
    stopAutoScroll(); setGhost(null); setDragId(null)
    if (!d) return
    if (d.moved) {
      justDragged.current = true
      const day = overDayRef.current
      overDayRef.current = null; setOverDay(null)
      if (day) applyDrop(d.payload, day)
    } else if (d.tappable) {
      setArmed((a) => (a ? null : d.payload))            // tap = arme / désarme
    }
  }
  function onDragPointerCancel() {
    touchDrag.current = null
    stopAutoScroll(); setGhost(null); setDragId(null)
    overDayRef.current = null; setOverDay(null)
  }
  // Un jour tapé alors qu'un bloc est armé reçoit l'event.
  function onDayTap(dayIso: string) {
    if (!armed) return
    applyDrop(armed, dayIso)
    setArmed(null)
  }

  const monthLabel = new Intl.DateTimeFormat(bcp(), { month: 'long', year: 'numeric' }).format(new Date(year, month0, 1))
  const weekdayNames = useMemo(() => {
    const base = new Date(2024, 0, 1) // lundi
    return Array.from({ length: 7 }, (_, i) => { const d = new Date(base); d.setDate(base.getDate() + i); return new Intl.DateTimeFormat(bcp(), { weekday: 'short' }).format(d) })
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  return (
    <div ref={rootRef} style={{ display: 'flex', flexDirection: 'column', gap: 16, padding: narrow ? 12 : 24, height: '100%', boxSizing: 'border-box', overflowX: 'hidden', overflowY: narrow ? 'auto' : 'hidden' }}>
      {/* Header */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12, flexShrink: 0, flexWrap: 'wrap' }}>
        <div style={{ minWidth: 0 }}>
          <h1 style={{ fontSize: 20, fontWeight: 700, margin: 0 }}>{t('title')}</h1>
          <p style={{ fontSize: 14, color: 'var(--color-muted-foreground)', margin: '2px 0 0' }}>{t('subtitle')}</p>
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
          <ViewToggle mode={mode} onChange={(m) => { setMode(m); if (m === 'iframe') setFrameLoaded(true) }} />
          <button style={btnGhost} onClick={reload} title="↻">↻</button>
          {can('create') && <button style={btnPrimary} onClick={() => setEditing('new')}>+ {t('new_event')}</button>}
        </div>
      </div>

      {/* Vue « Old » : outil legacy en iframe */}
      {frameLoaded && (
        <div style={{ ...card, display: mode === 'iframe' ? 'flex' : 'none', flex: 1, minHeight: 480, overflow: 'hidden' }}>
          <iframe src={`/melis/react-tool-page?key=${encodeURIComponent(MELIS_KEY_IFRAME)}`}
            style={{ flex: 1, width: '100%', border: 0 }} title="Calendrier — Vue Melis"
            sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-modals" />
        </div>
      )}

      {/* Vue « New ». Voir le calendrier = create OU edit (l'un ou l'autre). */}
      {(!can('create') && !can('edit')) ? (
        <div style={{ ...card, padding: '40px 16px', textAlign: 'center', fontSize: 14, color: 'var(--color-muted-foreground)' }}>{t('no_access')}</div>
      ) : (
      <div style={{ display: mode === 'react' ? 'grid' : 'none', gridTemplateColumns: narrow ? 'minmax(0, 1fr)' : 'minmax(240px, 300px) minmax(0, 1fr)', gap: 16, flex: narrow ? 'none' : 1, minHeight: 0 }}>
        {/* Panneau gauche — passe SOUS le calendrier en vue étroite */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 14, minHeight: 0, order: narrow ? 2 : 0 }}>
          <div style={{ display: 'flex', gap: 8 }}>
            <Kpi label={t('kpi_total')} value={stats?.total ?? null} />
            <Kpi label={t('kpi_upcoming')} value={stats?.upcoming ?? null} />
          </div>

          {can('create') && (
            <div style={{ ...card, padding: 14, display: 'flex', flexDirection: 'column', gap: 8 }}>
              <label style={label}>{t('new_event')}</label>
              <input style={inputCss} value={newTitle} onChange={(e) => setNewTitle(e.target.value)} placeholder={t('new_event_ph')} maxLength={255} />
              <div
                draggable={newTitle.trim() !== ''}
                onDragStart={(e) => { e.dataTransfer.effectAllowed = 'copy'; e.dataTransfer.setData('application/json', JSON.stringify({ kind: 'new', title: newTitle })) }}
                onPointerDown={(e) => { if (newTitle.trim()) onDragPointerDown(e, { kind: 'new', title: newTitle }, true) }}
                onPointerMove={onDragPointerMove}
                onPointerUp={onDragPointerUp}
                onPointerCancel={onDragPointerCancel}
                style={{ marginTop: 2, borderRadius: 8, border: `1px ${armed?.kind === 'new' ? 'solid' : 'dashed'} var(--color-primary)`, padding: '10px 12px', textAlign: 'center', fontSize: 13, fontWeight: 600,
                  color: newTitle.trim() ? 'var(--color-primary)' : 'var(--color-muted-foreground)',
                  background: armed?.kind === 'new'
                    ? 'color-mix(in srgb, var(--color-primary) 20%, transparent)'
                    : (newTitle.trim() ? 'color-mix(in srgb, var(--color-primary) 8%, transparent)' : 'transparent'),
                  cursor: newTitle.trim() ? 'grab' : 'not-allowed', opacity: newTitle.trim() ? 1 : 0.6, userSelect: 'none',
                  touchAction: newTitle.trim() ? 'none' : 'auto' }}>
                ⠿ {newTitle.trim() || t('new_event')}
              </div>
              <p style={{ margin: 0, fontSize: 12, color: armed ? 'var(--color-primary)' : 'var(--color-muted-foreground)' }}>
                {armed ? t('tap_hint', { name: (armed.title || '').trim() || t('new_event') }) : t('drag_hint')}
              </p>
            </div>
          )}

          <div style={{ ...card, padding: 14, display: 'flex', flexDirection: 'column', gap: 6, minHeight: 0, overflow: 'auto' }}>
            <label style={label}>{t('month_events')}</label>
            {monthEvents.length === 0 ? (
              <p style={{ margin: 0, fontSize: 13, color: 'var(--color-muted-foreground)' }}>{t('no_events')}</p>
            ) : monthEvents.map((ev) => (
              <button key={ev.id} onClick={() => can('edit') && setEditing(ev)}
                style={{ ...eventChip(false), cursor: 'pointer', display: 'flex', flexDirection: 'column', alignItems: 'flex-start', whiteSpace: 'normal', gap: 1 }}>
                <span style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', width: '100%' }}>{ev.title}</span>
                <span style={{ fontSize: 11, fontWeight: 400, opacity: 0.8 }}>{fmtHuman(ev.start)}{ev.end !== ev.start ? ` → ${fmtHuman(ev.end)}` : ''}</span>
              </button>
            ))}
          </div>
        </div>

        {/* Calendrier */}
        <div style={{ ...card, display: 'flex', flexDirection: 'column', minHeight: narrow ? 420 : 0, overflow: 'hidden', order: narrow ? 1 : 0 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 8, padding: narrow ? '10px' : '12px 14px', borderBottom: '1px solid var(--color-border)', flexWrap: 'wrap' }}>
            <button style={{ ...btnGhost, width: 34, height: 34, padding: 0, justifyContent: 'center' }} onClick={() => gotoMonth(-1)}>‹</button>
            <button style={{ ...btnGhost, width: 34, height: 34, padding: 0, justifyContent: 'center' }} onClick={() => gotoMonth(1)}>›</button>
            <button style={{ ...btnGhost, height: 34 }} onClick={gotoToday}>{t('today')}</button>
            <div style={{ flex: 1, minWidth: 0, textAlign: 'center', fontSize: narrow ? 14 : 16, fontWeight: 700, textTransform: 'capitalize' }}>{monthLabel}</div>
            {!narrow && <div style={{ width: 120 }} />}
          </div>

          {/* En-têtes jours */}
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(7, 1fr)', borderBottom: '1px solid var(--color-border)' }}>
            {weekdayNames.map((w, i) => (
              <div key={i} style={{ padding: '6px 8px', textAlign: 'center', fontSize: 11, fontWeight: 600, textTransform: 'uppercase', color: 'var(--color-muted-foreground)' }}>{w}</div>
            ))}
          </div>

          {/* Grille */}
          <div style={{ flex: 1, display: 'grid', gridTemplateRows: narrow ? 'repeat(6, minmax(52px, auto))' : 'repeat(6, 1fr)', minHeight: 0 }}>
            {weeks.map((week, wi) => (
              <div key={wi} style={{ display: 'grid', gridTemplateColumns: 'repeat(7, 1fr)' }}>
                {week.map((dayIso) => {
                  const inMonth = parseISO(dayIso).getMonth() === month0
                  const isToday = dayIso === todayISO()
                  const isOver = overDay === dayIso
                  const dayEvents = eventsOn(dayIso)
                  return (
                    <div key={dayIso}
                      data-day={dayIso}
                      onDragOver={(e) => { e.preventDefault(); if (overDay !== dayIso) setOverDay(dayIso) }}
                      onDragLeave={() => setOverDay((d) => (d === dayIso ? null : d))}
                      onDrop={(e) => onDropDay(e, dayIso)}
                      onClick={() => onDayTap(dayIso)}
                      style={{ borderRight: '1px solid var(--color-border)', borderBottom: '1px solid var(--color-border)', padding: 4, minHeight: 0, overflow: 'hidden',
                        display: 'flex', flexDirection: 'column', cursor: armed ? 'copy' : 'default',
                        background: isOver ?'color-mix(in srgb, var(--color-primary) 10%, transparent)' : (inMonth ? 'transparent' : 'color-mix(in srgb, var(--color-muted,#888) 5%, transparent)') }}>
                      <div style={{ display: 'flex', justifyContent: 'flex-end' }}>
                        <span style={{ fontSize: 12, fontWeight: isToday ? 700 : 500,
                          color: isToday ? 'var(--color-primary-foreground,#fff)' : (inMonth ? 'var(--color-foreground)' : 'var(--color-muted-foreground)'),
                          background: isToday ? 'var(--color-primary)' : 'transparent', borderRadius: 999, minWidth: 20, height: 20, lineHeight: '20px', textAlign: 'center', padding: '0 4px' }}>
                          {parseISO(dayIso).getDate()}
                        </span>
                      </div>
                      <div style={{ flex: 1, overflow: 'auto', minHeight: 0 }}>
                        {dayEvents.map((ev) => (
                          <button key={ev.id + '@' + dayIso}
                            draggable={can('edit') && ev.start === dayIso}
                            onDragStart={(e) => { setDragId(ev.id); e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('application/json', JSON.stringify({ kind: 'move', id: ev.id, title: ev.title, start: ev.start, end: ev.end })) }}
                            onDragEnd={() => setDragId(null)}
                            onPointerDown={(e) => { if (can('edit') && ev.start === dayIso) onDragPointerDown(e, { kind: 'move', id: ev.id, title: ev.title, start: ev.start, end: ev.end }, false) }}
                            onPointerMove={onDragPointerMove}
                            onPointerUp={onDragPointerUp}
                            onPointerCancel={onDragPointerCancel}
                            onClick={(e) => {
                              if (justDragged.current) { justDragged.current = false; e.stopPropagation(); return }
                              if (armed) return
                              e.stopPropagation()
                              if (can('edit')) setEditing(ev)
                            }}
                            title={ev.title}
                            style={{ ...eventChip(dragId === ev.id), touchAction: can('edit') && ev.start === dayIso ? 'none' : 'auto' }}>
                            {ev.title}
                          </button>
                        ))}
                      </div>
                    </div>
                  )
                })}
              </div>
            ))}
          </div>
        </div>
      </div>
      )}

      {/* Fantôme du drag tactile (pointerEvents:none → n'interfère pas avec elementFromPoint) */}
      {ghost && (
        <div style={{ position: 'fixed', left: ghost.x, top: ghost.y, transform: 'translate(-50%, -150%)', zIndex: 80, pointerEvents: 'none',
          borderRadius: 8, padding: '6px 10px', maxWidth: 200, fontSize: 12, fontWeight: 600, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis',
          background: 'var(--color-primary)', color: 'var(--color-primary-foreground,#fff)', boxShadow: '0 4px 14px rgba(0,0,0,.25)' }}>
          {ghost.label}
        </div>
      )}

      {editing && (
        <EventModal event={editing === 'new' ? null : editing}
          onClose={() => setEditing(null)}
          onSaved={() => { setEditing(null); reload() }} />
      )}
    </div>
  )
}

// ── Modale créer / éditer / supprimer ──
function EventModal({ event, onClose, onSaved }: { event: CalEvent | null; onClose: () => void; onSaved: () => void }) {
  const t = useT()
  const isEdit = !!event
  const [title, setTitle] = useState(event?.title ?? '')
  const [start, setStart] = useState(event?.start ?? todayISO())
  const [end, setEnd] = useState(event?.end ?? event?.start ?? todayISO())
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [confirmDel, setConfirmDel] = useState(false)
  const titleRef = useRef<HTMLInputElement>(null)
  useEffect(() => { titleRef.current?.focus() }, [])

  async function submit() {
    setError(null)
    if (!title.trim()) { setError(t('err_title')); return }
    const e = end < start ? start : end
    setSaving(true)
    try { await saveEvent({ id: event?.id ?? null, title: title.trim(), start, end: e }); notify('ok', t('title'), t('saved')); onSaved() }
    catch (err) { setError(err instanceof Error ? err.message : 'error'); setSaving(false) }
  }
  async function doDelete() {
    if (!event) return
    setSaving(true)
    try { await deleteEvent(event.id); notify('ok', t('title'), t('saved')); onSaved() }
    catch (err) { setError(err instanceof Error ? err.message : 'error'); setSaving(false) }
  }

  return (
    <div style={{ position: 'fixed', inset: 0, zIndex: 60, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(0,0,0,.5)' }}
      onClick={(e) => { if (e.target === e.currentTarget) onClose() }}>
      <div style={{ ...card, width: '100%', maxWidth: 460 }}>
        <div style={{ padding: '16px 20px', borderBottom: '1px solid var(--color-border)' }}>
          <h2 style={{ fontSize: 16, fontWeight: 600, margin: 0 }}>{isEdit ? t('edit_title') : t('new_title')}</h2>
        </div>
        <div style={{ padding: 20, display: 'flex', flexDirection: 'column', gap: 14 }}>
          {error && <div style={{ ...card, borderColor: '#fca5a5', background: '#fef2f2', color: '#b91c1c', padding: '8px 12px', fontSize: 13 }}>{error}</div>}
          <div>
            <label style={label}>{t('f_title')}</label>
            <input ref={titleRef} style={inputCss} value={title} onChange={(e) => setTitle(e.target.value)} maxLength={255} />
          </div>
          <div style={{ display: 'flex', gap: 12 }}>
            <div style={{ flex: 1 }}>
              <label style={label}>{t('f_start')}</label>
              <input type="date" style={inputCss} value={start} onChange={(e) => { setStart(e.target.value); if (end < e.target.value) setEnd(e.target.value) }} />
            </div>
            <div style={{ flex: 1 }}>
              <label style={label}>{t('f_end')}</label>
              <input type="date" style={inputCss} value={end} min={start} onChange={(e) => setEnd(e.target.value)} />
            </div>
          </div>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between', gap: 8, padding: '12px 16px', borderTop: '1px solid var(--color-border)' }}>
          <div>
            {isEdit && can('edit') && (
              <button style={{ ...btnGhost, borderColor: '#fca5a5', color: '#dc2626' }} onClick={() => setConfirmDel(true)} disabled={saving}>
                {t('del')}
              </button>
            )}
          </div>
          <div style={{ display: 'flex', gap: 8 }}>
            <button style={btnGhost} onClick={onClose} disabled={saving}>{t('cancel')}</button>
            <button style={btnPrimary} onClick={submit} disabled={saving}>{saving ? '…' : t('save')}</button>
          </div>
        </div>
      </div>

      {confirmDel && event && (
        <div style={{ position: 'fixed', inset: 0, zIndex: 70, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(0,0,0,.5)' }}
          onClick={(e) => { if (e.target === e.currentTarget) setConfirmDel(false) }}>
          <div style={{ ...card, width: '100%', maxWidth: 380, padding: 20 }}>
            <h3 style={{ fontSize: 16, fontWeight: 600, margin: 0 }}>{t('del_title')}</h3>
            <p style={{ marginTop: 8, marginBottom: 0, fontSize: 14, color: 'var(--color-muted-foreground,#6b7280)' }}>
              {t('del_msg', { name: event.title })}
            </p>
            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8, marginTop: 18 }}>
              <button style={btnGhost} onClick={() => setConfirmDel(false)} disabled={saving}>{t('cancel')}</button>
              <button style={{ ...btnGhost, borderColor: '#fca5a5', color: '#dc2626' }} onClick={doDelete} disabled={saving}>
                {saving ? '…' : t('del')}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
