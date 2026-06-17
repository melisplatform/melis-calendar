/**
 * Calendar page shipped by the MelisCalendar module.
 *
 * For now it loads the legacy Melis calendar tool inside an iframe — the same
 * mechanism the shell uses for any tool without a native React page
 * (/melis/react-tool-page?key=<melisKey> renders a standalone tool page; the
 * X-Requested-With header is forced server-side, and injected by the Vite proxy in dev).
 *
 * Later this can be replaced by a native React UI talking to a calendar JSON API.
 */
const MELIS_KEY = 'meliscalendar_tool'

export default function CalendarPage() {
  return (
    <div style={{ height: '100%', width: '100%', display: 'flex', flexDirection: 'column', minHeight: 0 }}>
      <iframe
        src={`/melis/react-tool-page?key=${encodeURIComponent(MELIS_KEY)}`}
        style={{ flex: 1, width: '100%', border: 0, minHeight: 0 }}
        sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-modals"
        title="Calendrier"
      />
    </div>
  )
}
