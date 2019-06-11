<?php
return array(
    'plugins' => array(
        'meliscore' => [
            'interface' => [
                'melis_dashboardplugin' => [
                    'interface' => [
                        'melisdashboardplugin_section' => [
                            'interface' => [
                                'MelisCalendarEventsPlugin' => [
                                    'conf' => [
                                        'type' => '/meliscalendar/interface/MelisCalendarEventsPlugin'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ],
        'meliscalendar' => array(
            'ressources' => array(
                'css' => array(
                ),
                'js' => array(
                )
            ),
            'interface' => [
                'MelisCalendarEventsPlugin' => array(
                    'conf' => [
                        'name' => 'MelisCalendarEventsPlugin',
                        'melisKey' => 'MelisCalendarEventsPlugin'
                    ],
                    'datas' => [
                        'plugin_id' => 'CalendarEvents',
                        'name' => 'tr_meliscalendar_dashboard',
                        'description' => 'tr_meliscalendar_dashboard_description',
                        'icon' => 'fa fa-calendar',
                        'thumbnail' => '/MelisCalendar/plugins/images/MelisCalendarEventsPlugin.jpg',
                        'jscallback' => 'initDashboardCalendar()',
                        'max_lines' => 8,
                        'height' => 4,
                        'width' => 6,
                        'x-axis' => 0,
                        'y-axis' => 0,
                        /*
                         * if set this plugin will belong to a specific marketplace section,
                           * if not it will go directly to ( Others ) section
                           *  - available section for dashboard plugins as of 2019-05-16
                           *    - MelisCore
                           *    - MelisCms
                           *    - MelisMarketing
                           *    - MelisSite
                           *    - MelisCommerce
                           *    - Others
                           *    - CustomProjects
                         */
                        'section' => 'MelisMarketing',
                    ],
                    'forward' => array(
                        'module' => 'MelisCalendar',
                        'plugin' => 'MelisCalendarEventsPlugin',
                        'function' => 'calendarEvents',
                        'jscallback' => '',
                        'jsdatas' => array()
                    ),
                ),
            ],
        )
    )
);