<?php

/**
 * Capacités d'outils — droits avancés du back-office React (module MelisCalendar).
 *
 * Déclaration plate par melisKey dans la clé mergée `melisReactToolCapabilities`. Fichier séparé ;
 * mergé dans MelisCalendar\Module::getConfig(). Default-allow.
 *
 * ⚠️ Clé = `meliscalendar_tool` — le nœud FEUILLE du menu (isTool && !hasNavChild). RightsTreeView
 * ne rend les cases de capacités QUE sur une FEUILLE (cf. CategoryGroup) : le parent
 * `meliscalendar_leftnemu` a un enfant → rendu en GROUPE (sans caps). Le grant/deny de l'utilisateur
 * est aussi stocké sous la feuille `meliscalendar_tool`. C'est donc AUSSI le MELIS_KEY d'accès/caps
 * du contrôleur react-api et le `can()` de la brique. (Le rights_checkbox_disable de la feuille n'empêche
 * pas RightsTreeView de la rendre comme ToolRow cochable.)
 *
 * Deux droits SEULEMENT (pas de `list` ni `delete` distincts) :
 *   - create : créer un événement (drag d'un nouveau titre sur un jour / bouton « Nouvel événement »)
 *   - edit   : déplacer/rééchelonner (drag), éditer OU supprimer un événement existant
 *
 * ⚠️ VOIR le calendrier = `create` OU `edit` (l'un OU l'autre suffit). Si l'utilisateur n'a QUE
 * `create`, il voit le calendrier et peut créer, mais ne peut ni déplacer, ni éditer, ni supprimer.
 * (Côté API : list/stats gardés par « create OU edit » ; save-sans-id par create ; save-avec-id +
 * delete par edit. Côté React : idem via `can('create')` / `can('edit')`.)
 */

return [
    'melisReactToolCapabilities' => [
        'meliscalendar_tool' => ['create', 'edit'],
    ],
];
