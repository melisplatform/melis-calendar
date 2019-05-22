<?php 
    return array(
        'plugins' => array(
            'meliscalendar' => array(
                'ressources' => array(
                    'css' => array(
                    ),
                    'js' => array(
                    )
                ),
                'dashboard_plugins' => array(
                    'MelisCalendarEventsPlugin' => array(
                        'plugin_id' => 'CalendarEvents',
                        'name' => 'tr_meliscalendar_dashboard',
                        'description' => 'tr_meliscalendar_dashboard_description',
                        'icon' => 'fa fa-calendar',
                        'thumbnail' => '/MelisCalendar/plugins/images/MelisCalendarEventsPlugin.jpg',
                        'jscallback' => 'initDashboardCalendar()',
                        'height' => 4,
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
                        'interface' => array(
                            'meliscalendar_events' => array(
                                'forward' => array(
                                    'module' => 'MelisCalendar',
                                    'plugin' => 'MelisCalendarEventsPlugin',
                                    'function' => 'calendarEvents'
                                )
                            )
                        )
                    )
                )
            )
        )
    );