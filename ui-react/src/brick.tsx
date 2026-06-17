import CalendarPage from './CalendarPage'

/**
 * Brick entry point. When this IIFE bundle is loaded by the MelisCore shell, it
 * self-registers its page component under the brick id. The host then mounts it on
 * the route declared in brick.manifest.json (matched by id).
 */
declare global {
  interface Window {
    __melisRegisterBrick?: (b: { id: string; Component: unknown }) => void
  }
}

window.__melisRegisterBrick?.({ id: 'calendar', Component: CalendarPage })
