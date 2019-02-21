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
                        'name' => 'tr_meliscalendar_dashboard',
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
                        'width' => 4,
                        'x-axis' => 0,
                        'y-axis' => 0,
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