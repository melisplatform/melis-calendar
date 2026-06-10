---
title: MelisCalendar module
package: melisplatform/melis-calendar
doc_type: module-documentation
audience: [users, developers, ai]
language: en
module_version: unversioned
last_reviewed: 2026-06-08
maintainer: Melis Technology
keywords: [calendar, events, scheduling, agenda, dashboard, fullcalendar, melis, back-office]
screenshots_dir: ./images
---

# MelisCalendar — Functional & Technical Documentation (for AI)

> **What this is.** MelisCalendar is a **calendar / event-scheduling** tool for the back-office:
> create dated events in an interactive calendar, drag them to reschedule, and see upcoming
> events at a glance via a **dashboard widget**. It also offers a **service** so other modules
> can add events programmatically.
>
> **Two parts:** **[Part A — Functional Guide](#part-a--functional-guide)** (users) ·
> **[Part B — Technical Reference](#part-b--technical-reference)** (developers/AI, with examples).
> Consumed by the **MelisAI** MCP; the **[Screenshot index](#screenshot-index)** maps filenames.
> Reviewed 2026-06-08.

---
---

# PART A — Functional Guide

## A1. What MelisCalendar lets you do

- **Schedule events** on an interactive calendar (a title + a start/end date).
- **Reschedule** by dragging an event to a new date.
- **Keep track** with a recently-added list and a back-office **dashboard widget**.

It's platform-wide (not tied to a specific site) — a shared agenda for the team.

## A2. The Calendar tool (back-office)

**Where:** back-office left menu → **MelisMarketing** → **Calendar** (calendar icon).

The tool shows an interactive **calendar grid** of your events, a **create-event** form, and a
list of **recently added** events.

![Calendar tool — the calendar](./images/meliscalendar-tool-calandar-display.png)
*The calendar tool — events on a month grid, a create form, and the recent list.*

![An event on the calendar](./images/meliscalendar-tool-calandar-event.png)
*An event shown on the grid.*

**Create an event** with the form (title + start/end dates):

![New event](./images/meliscalendar-tool-calandar-new-event.png)

**Edit an event** (or delete it) from its modal; **reschedule** simply by dragging it on the grid:

![Edit an event](./images/meliscalendar-tool-calandar-event-edition.png)

## A3. The dashboard widget

On the back-office **Dashboard** you can add the **Calendar Events** widget to see your events
without opening the tool.

![Calendar Events dashboard widget](./images/meliscalendar-dashboardplugins-calandar.png)
*The Calendar Events widget on the dashboard.*

It's added from the dashboard's **plugin selector** (MelisMarketing section):

![Dashboard plugin selector](./images/meliscalendar-dashboardplugins-menu-selector.png)

## A4. Common tasks — "How do I…?"

- **Add an event** → Calendar tool → fill the create form (title + dates) → save.
- **Move an event to another date** → drag it on the calendar grid.
- **Edit/cancel an event** → click it → edit or delete.
- **See events on the dashboard** → add the **Calendar Events** widget from the dashboard's plugin menu.

---
---

# PART B — Technical Reference

## B1. Metadata & dependencies

| Item | Value |
|---|---|
| Package | `melisplatform/melis-calendar` · category **`core`** · namespace `MelisCalendar\` · dbdeploy |
| Requires | `melisplatform/melis-core` (`^5.2`) only |
| Calendar UI | **FullCalendar** + Moment.js (`public/plugins/`) |

A core-category module: depends only on `melis-core`; events are platform-global (no site/CMS link).

## B2. Data model

| Table | Role | PK |
|---|---|---|
| `melis_calendar` | An event: `cal_event_title`, `cal_date_start`, `cal_date_end`, audit (`cal_created_by`, `cal_last_update_by`, `cal_date_last_update`, `cal_date_added`) | `cal_id` |

Gateway: `MelisCalendarTable`.

## B3. Service `MelisCalendarService` (with examples)

```php
$cal = $this->getServiceManager()->get('MelisCalendarService');
$cal->addCalendarEvent($postValues);      // title + start/end dates -> create
$cal->reschedCalendarEvent($postValues);  // update dates (drag/resize)
$cal->deleteCalendarEvent($postValues);   // delete
```

Methods: `addCalendarEvent`, `reschedCalendarEvent`, `deleteCalendarEvent`. A
`meliscalendar_save_event_end` event fires on save — hook it to react:

```php
$sharedEvents->attach('MelisCalendar', 'meliscalendar_save_event_end', function ($e) {
    $params = $e->getParams();
    // e.g. notify, sync an external calendar…
}, 10);
```

## B4. Tool, controllers, dashboard, form element

- **Controllers**: `CalendarController` (render the tool: left menu, calendar content, create
  form, recent list, edit-event modal) and `ToolCalendarController` (data + writes:
  `retrieveCalendarEventsAction` feeds FullCalendar, `saveEventAction`, `reschedEventAction`,
  `deleteEventAction`, `searchCalendarEventAction`, `getEventTitleAction`,
  `retrieveDashboardCalendarEventsAction`). Menu/layout in `config/app.interface.php`
  (MelisMarketing section, refresh interval `msg_interval` default 60 s).
- **Dashboard**: `DashboardPlugins/MelisCalendarEventsPlugin` (`calendarEvents`,
  `getDashboardStats`), config `config/dashboard-plugins/MelisCalendarEventsPlugin.config.php`.
- **Form element**: `MelisCalendarDraggableInput` (`src/Form/Factory/`) — drag events onto the grid.
- **Listener**: `MelisCalendarFlashMessengerListener` (back-office).

## B5. Quick code map

```
melis-calendar/
├── config/   module.config.php · app.interface.php (Calendar tool, MelisMarketing) · app.forms.php
│            · app.tools.php · dashboard-plugins/MelisCalendarEventsPlugin.config.php
├── src/   Controller/ (Calendar, ToolCalendar, DashboardPlugins/) · Service/MelisCalendarService
│        · Model/Tables/MelisCalendarTable · Listener/ · Form/Factory/MelisCalendarDraggableInputFactory
├── view/ · public/ (FullCalendar, Moment, tool JS/CSS) · language/ · install/
└── etc/   MarketPlace + MelisAI/doc (this doc)
```

---

## Screenshot index

| Image file | Content |
|---|---|
| `meliscalendar-tool-calandar-display.png` | Calendar tool — the FullCalendar view |
| `meliscalendar-tool-calandar-event.png` | An event shown on the calendar |
| `meliscalendar-tool-calandar-new-event.png` | Create-event form |
| `meliscalendar-tool-calandar-event-edition.png` | Edit-event modal |
| `meliscalendar-dashboardplugins-calandar.png` | Calendar Events dashboard widget |
| `meliscalendar-dashboardplugins-menu-selector.png` | Dashboard plugin selector — add the widget |

---

*Document for AI consumption (MelisAI MCP) — `melisplatform/melis-calendar`. Part A = functional;
Part B = technical with examples. Last reviewed 2026-06-08.*
