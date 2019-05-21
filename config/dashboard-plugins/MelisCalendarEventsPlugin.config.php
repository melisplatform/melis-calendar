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
                        'section' => 'MelisCore',
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