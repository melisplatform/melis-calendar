<?php

/**
 * Routes + contrôleur React API fournis par MelisCalendar (outil Calendrier, table melis_calendar).
 *
 * S'ajoute aux child_routes de `melis-react-api` (bridge générique). Modularité : contrôleur/routes/
 * invokable dans SON module. ArrayUtils::merge fusionne. Mergé via MelisCalendar\Module::getConfig().
 * Ordre : /stats /save /delete AVANT le catch-all /:id.
 */

return [
    'router' => [
        'routes' => [
            'melis-backoffice' => [
                'child_routes' => [
                    'melis-react-api' => [
                        'child_routes' => [
                            'calendar-events-list' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/calendar-events[/]',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MelisCalendar\Controller',
                                        'controller'    => 'MelisReactApiCalendar',
                                        'action'        => 'list',
                                    ],
                                ],
                            ],
                            'calendar-events-stats' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/calendar-events/stats[/]',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MelisCalendar\Controller',
                                        'controller'    => 'MelisReactApiCalendar',
                                        'action'        => 'stats',
                                    ],
                                ],
                            ],
                            'calendar-events-save' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => '/calendar-events/save[/]',
                                    'defaults' => [
                                        '__NAMESPACE__' => 'MelisCalendar\Controller',
                                        'controller'    => 'MelisReactApiCalendar',
                                        'action'        => 'save',
                                    ],
                                ],
                            ],
                            'calendar-events-delete' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/calendar-events/delete/:id',
                                    'constraints' => ['id' => '[0-9]+'],
                                    'defaults'    => [
                                        '__NAMESPACE__' => 'MelisCalendar\Controller',
                                        'controller'    => 'MelisReactApiCalendar',
                                        'action'        => 'delete',
                                    ],
                                ],
                            ],
                            'calendar-events-item' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '/calendar-events/:id',
                                    'constraints' => ['id' => '[0-9]+'],
                                    'defaults'    => [
                                        '__NAMESPACE__' => 'MelisCalendar\Controller',
                                        'controller'    => 'MelisReactApiCalendar',
                                        'action'        => 'get',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'invokables' => [
            'MelisCalendar\Controller\MelisReactApiCalendar' => \MelisCalendar\Controller\MelisReactApiCalendarController::class,
        ],
    ],
];
