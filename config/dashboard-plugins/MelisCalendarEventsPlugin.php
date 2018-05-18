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
                        'thumbnail' => '',
                        'jscallback' => '',
                        'height' => 4,
                        
                        'interface' => array(
                            'meliscalendar_events' => array(
                                'forward' => array(
                                    'module' => 'MelisCalendar',
                                    'plugin' => 'MelisCalendarEventsPlugin',
                                    'function' => 'calendarEvents',
                                    'jscallback' => 'initDashboardCalendar()',
                                    'jsdatas' => array()
                                ),
                            ),
                        )
                    )
                ),
            )
        ),
    );