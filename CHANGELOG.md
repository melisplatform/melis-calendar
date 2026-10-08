# Changelog

## v6.0.4 - 2026-09-25
### Security
* **security:** declare the tool key on this module's controllers (audit item 7.0)

## v6.0.3 - 2026-08-20
### Fixed
* **calendar-react:** code icon on the New view toggle
### Docs
* **melisai:** React back-office AI documentation for MelisCalendar

## v6.0.2 - 2026-08-10
### Changed
* Bump postcss from 8.5.15 to 8.5.26 in /ui-react
### Dependencies & build
* **composer:** update docs/homepage links, swap zf2 keyword for laminas, bump php constraint to ^8.3|^8.5

## v6.0.1 - 2026-08-10
### Security
* **security:** add SECURITY.md (private vulnerability reporting policy)
### Added
* **marketplace:** add React back-office screenshots (etc/MarketPlace/images/react)
* **calendar:** full-React migration — custom month calendar with drag-drop events (create by drag, reschedule) + create/edit capabilities (view = create OR edit)
* **react:** brique React melis-calendar (ui-react source + build)
* Add MelisAI module documentation for AI consumption
### Fixed
* **calendar:** make event drag-and-drop work on touch devices
* **calendar-react:** responsive layout — calendar was invisible on mobile
### Changed
* Delete confirmation
### Dependencies & build
* **deps:** require melisplatform/melis-core ^6.0
* Local WIP snapshot before reconcile (20260806-114605)
* Sync ui-react package-lock
### Docs
* **meliscalendar:** rewrite as two-part doc (functional guide + technical reference with examples)

## v5.3.0 - 2024-09-25
### Added
* Implementation on fullcalendar v6
* Implementation of fullcalendar in v6
### Fixed
* Fix issue on calendar
* Fix issue on announcement calendar
* Fix for issue 6976
* Fix issue on calendar icons
* Fix issue 6334
### Changed
* Edit on calendar-tool.js
* Update on html
* Bs5 tab
* JQuery 3.7.1 migration
* Update on jQuery migration
### Dependencies & build
* Rebuilt the asset bundle

## v5.2.0 - 2024-06-06
* Maintenance release.

## v5.1.0 - 2024-02-13
* Maintenance release.

## v5.0.0 - 2022-06-22
### Changed
* Changed deprecated ArraySerializable to ArraySerializableHydrator
