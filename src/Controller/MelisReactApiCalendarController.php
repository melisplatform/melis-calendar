<?php

namespace MelisCalendar\Controller;

use MelisReactApi\Controller\CapabilityGuardTrait;
use MelisReactApi\Service\Capabilities;

use Laminas\Http\PhpEnvironment\Response as HttpResponse;
use MelisCore\Controller\MelisAbstractActionController;

/**
 * API REST pour l'outil Calendrier de MelisCalendar (table melis_calendar).
 *
 * Couche API (shared) du back-office React ; l'UI = une BRIQUE (calendrier custom + drag-drop).
 * Gabarit full-React (SQL-brut). Un « event » = titre + date de début + date de fin (dates seules).
 *
 * Routes :
 *   GET    /melis/react-api/calendar-events            → events (optionnel ?from=&to= pour la fenêtre)
 *   GET    /melis/react-api/calendar-events/stats      → statistiques (KPI)
 *   POST   /melis/react-api/calendar-events/save       → créer / mettre à jour (drop = create, resched = update dates)
 *   DELETE /melis/react-api/calendar-events/delete/:id → supprimer
 *   GET    /melis/react-api/calendar-events/:id        → détail
 *
 * ⚠️ MELIS_KEY = `meliscalendar_tool` : le nœud FEUILLE du menu. RightsTreeView ne rend les cases de
 * capacités que sur une feuille (le parent `meliscalendar_leftnemu`, ayant un enfant, est un GROUPE
 * sans caps) ; l'octroi/deny de l'utilisateur y est aussi stocké. Cette clé sert donc à `canAccess`
 * (accès outil) ET aux capacités (`denyUnlessCan`/`denyUnlessCanAny` + déclaration + can() front).
 */
class MelisReactApiCalendarController extends MelisAbstractActionController
{
    use CapabilityGuardTrait;

    private const MELIS_KEY = 'meliscalendar_tool';

    // ─── GET /calendar-events ─────────────────────────────────────────────────────

    public function listAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }
        if ($denyCap = $this->denyUnlessCanAny(['create', 'edit'])) { return $denyCap; }

        try {
            $from = $this->cleanDate((string) $this->params()->fromQuery('from', ''));
            $to   = $this->cleanDate((string) $this->params()->fromQuery('to', ''));

            $db = $this->getServiceManager()->get('Laminas\Db\Adapter\AdapterInterface');

            $where = [];
            $params = [];
            // Chevauchement avec la fenêtre [from, to] : start <= to AND end >= from.
            if ($to !== null)   { $where[] = 'cal_date_start <= ?'; $params[] = $to; }
            if ($from !== null) { $where[] = 'cal_date_end >= ?';   $params[] = $from; }
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            $rows = $db->query(
                "SELECT cal_id, cal_event_title, cal_date_start, cal_date_end
                 FROM melis_calendar $whereClause
                 ORDER BY cal_date_start ASC, cal_id ASC",
                $params
            );

            $items = [];
            foreach ($rows as $row) { $items[] = $this->formatEvent((array) $row); }

            return $this->jsonResponse(['success' => true, 'data' => ['items' => $items]]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // ─── GET /calendar-events/stats ───────────────────────────────────────────────

    public function statsAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }
        if ($denyCap = $this->denyUnlessCanAny(['create', 'edit'])) { return $denyCap; }

        try {
            $db  = $this->getServiceManager()->get('Laminas\Db\Adapter\AdapterInterface');
            $row = (array) (iterator_to_array($db->query(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN CURDATE() BETWEEN cal_date_start AND cal_date_end THEN 1 ELSE 0 END) AS ongoing,
                        SUM(CASE WHEN cal_date_start >= CURDATE() THEN 1 ELSE 0 END) AS upcoming
                 FROM melis_calendar",
                []
            ))[0] ?? []);

            return $this->jsonResponse([
                'success' => true,
                'data'    => [
                    'total'    => (int) ($row['total'] ?? 0),
                    'ongoing'  => (int) ($row['ongoing'] ?? 0),
                    'upcoming' => (int) ($row['upcoming'] ?? 0),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // ─── GET /calendar-events/:id ─────────────────────────────────────────────────

    public function getAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }
        if ($denyCap = $this->denyUnlessCan('edit')) { return $denyCap; }

        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id <= 0) { return $this->jsonResponse(['success' => false, 'error' => 'Invalid ID'], 400); }

        try {
            $db   = $this->getServiceManager()->get('Laminas\Db\Adapter\AdapterInterface');
            $rows = iterator_to_array($db->query(
                'SELECT cal_id, cal_event_title, cal_date_start, cal_date_end FROM melis_calendar WHERE cal_id = ?',
                [$id]
            ));
            if (!$rows) { return $this->jsonResponse(['success' => false, 'error' => 'Not found'], 404); }
            return $this->jsonResponse(['success' => true, 'data' => $this->formatEvent((array) $rows[0])]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // ─── POST /calendar-events/save ───────────────────────────────────────────────
    // Drop d'un nouvel event (id absent → create) OU reschedule/édition (id présent → update).

    public function saveAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }

        try {
            $body  = json_decode($this->getRequest()->getContent(), true) ?? [];
            $id    = isset($body['id']) && $body['id'] ? (int) $body['id'] : null;
            if ($denyCap = $this->denyUnlessCan($id ? 'edit' : 'create')) { return $denyCap; }

            $title = trim((string) ($body['title'] ?? ''));
            $start = $this->cleanDate((string) ($body['start'] ?? ''));
            $end   = $this->cleanDate((string) ($body['end'] ?? '')) ?? $start;

            if ($title === '') {
                return $this->jsonResponse(['success' => false, 'error' => 'Le titre de l’événement est obligatoire.'], 400);
            }
            if ($start === null) {
                return $this->jsonResponse(['success' => false, 'error' => 'La date de début est invalide.'], 400);
            }
            if ($end === null) { $end = $start; }
            if ($end < $start) { $end = $start; } // parité legacy : fin >= début

            $db     = $this->getServiceManager()->get('Laminas\Db\Adapter\AdapterInterface');
            $userId = $this->currentUserId();
            $now    = date('Y-m-d H:i:s');

            if ($id) {
                if (!iterator_to_array($db->query('SELECT cal_id FROM melis_calendar WHERE cal_id = ?', [$id]))) {
                    return $this->jsonResponse(['success' => false, 'error' => 'Not found'], 404);
                }
                $db->query(
                    'UPDATE melis_calendar SET cal_event_title = ?, cal_date_start = ?, cal_date_end = ?,
                            cal_last_update_by = ?, cal_date_last_update = ? WHERE cal_id = ?',
                    [$title, $start, $end, $userId, $now, $id]
                );
                return $this->jsonResponse(['success' => true, 'data' => ['id' => $id]]);
            }

            $db->query(
                'INSERT INTO melis_calendar (cal_event_title, cal_date_start, cal_date_end, cal_created_by, cal_date_added)
                 VALUES (?, ?, ?, ?, ?)',
                [$title, $start, $end, $userId, $now]
            );
            $newId = (int) iterator_to_array($db->query('SELECT LAST_INSERT_ID() AS id', []))[0]['id'];

            return $this->jsonResponse(['success' => true, 'data' => ['id' => $newId]], 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // ─── DELETE /calendar-events/delete/:id ───────────────────────────────────────

    public function deleteAction(): HttpResponse
    {
        if ($deny = $this->denyUnlessAccess()) { return $deny; }
        if ($denyCap = $this->denyUnlessCan('edit')) { return $denyCap; } // supprimer = éditer (pas de droit delete distinct)

        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id <= 0) { return $this->jsonResponse(['success' => false, 'error' => 'Invalid ID'], 400); }

        try {
            $db = $this->getServiceManager()->get('Laminas\Db\Adapter\AdapterInterface');
            if (!iterator_to_array($db->query('SELECT cal_id FROM melis_calendar WHERE cal_id = ?', [$id]))) {
                return $this->jsonResponse(['success' => false, 'error' => 'Not found'], 404);
            }
            $db->query('DELETE FROM melis_calendar WHERE cal_id = ?', [$id]);
            return $this->jsonResponse(['success' => true, 'data' => null]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────────

    private function formatEvent(array $r): array
    {
        return [
            'id'    => (int)    $r['cal_id'],
            'title' => (string) $r['cal_event_title'],
            'start' => (string) $r['cal_date_start'],
            'end'   => (string) $r['cal_date_end'],
        ];
    }

    /** Normalise une date `Y-m-d` (accepte aussi un datetime), null si invalide/vide. */
    private function cleanDate(string $s): ?string
    {
        $s = trim($s);
        if ($s === '') { return null; }
        $s = substr($s, 0, 10);
        $d = \DateTime::createFromFormat('Y-m-d', $s);
        return ($d && $d->format('Y-m-d') === $s) ? $s : null;
    }

    private function currentUserId(): ?int
    {
        try {
            $id = $this->getServiceManager()->get('MelisCoreAuth')->getIdentity()->usr_id ?? null;
            return $id ? (int) $id : null;
        } catch (\Throwable) { return null; }
    }

    private function isAuthenticated(): bool
    {
        return $this->getServiceManager()->get('MelisCoreAuth')->hasIdentity();
    }

    private function denyUnlessAccess(): ?HttpResponse
    {
        if (!$this->isAuthenticated()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Unauthenticated'], 401);
        }
        try {
            if (!$this->getServiceManager()->get('MelisCoreRights')->canAccess(self::MELIS_KEY)) {
                return $this->jsonResponse(['success' => false, 'error' => 'Forbidden'], 403);
            }
        } catch (\Throwable) {}
        return null;
    }

    /**
     * Garde de capacité « au moins une parmi » (défaut-allow, admin bypass) — pour la LECTURE du
     * calendrier qui exige `create` OU `edit`. Miroir de CapabilityGuardTrait::denyUnlessCan mais en OR.
     */
    private function denyUnlessCanAny(array $caps): ?HttpResponse
    {
        try {
            $sm   = $this->getServiceManager();
            $auth = $sm->get('MelisCoreAuth');
            if (!empty($auth->getIdentity()->usr_admin)) {
                return null; // admin : tout permis
            }
            $xml    = $auth->getAuthRights();
            $config = $sm->get('config');
            foreach ($caps as $cap) {
                if (Capabilities::isAllowed($config, $xml, self::MELIS_KEY, $cap)) {
                    return null; // au moins une capacité permise → OK
                }
            }
            return $this->jsonResponse(['success' => false, 'error' => 'Forbidden (capabilities: ' . implode('|', $caps) . ')'], 403);
        } catch (\Throwable) {
            return null; // en cas d'erreur, on ne bloque pas (défaut-allow)
        }
    }

    private function jsonResponse(array $data, int $status = 200): HttpResponse
    {
        /** @var HttpResponse $response */
        $response = $this->getResponse();
        $response->setStatusCode($status);
        $response->getHeaders()->addHeaders([
            'Content-Type'           => 'application/json; charset=utf-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setContent(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response;
    }

    private function errorResponse(\Throwable $e, int $status = 500): HttpResponse
    {
        return $this->jsonResponse([
            'success' => false,
            'error'   => $e->getMessage(),
            'file'    => basename($e->getFile()) . ':' . $e->getLine(),
        ], $status);
    }
}
