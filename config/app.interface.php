<?php 

return array(
    'plugins' => array(
        'meliscore' => array(
            'datas' => array(),
            'interface' => array(
                'meliscore_leftmenu' => array(
                    'interface' => array(
                        'meliscore_toolstree' => array(
                            'interface' => array(
                                'meliscalendar_leftnemu' =>  array(
                                    'conf' => array(
                                        'type' => 'melistoolcalendar/interface/melistoolcalendar_cof'
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'meliscalendar' => array(
            'conf' => array(
                'rightsDisplay' => 'none',
            ),
        ),
        'melistoolcalendar' => array(
            'conf' => array(
                'id' => '',
                'name' => 'tr_melistoolcalendar_tool_calendar',
                'rightsDisplay' => 'none',
            ),
            'ressources' => array(
                'js' => array(
                    //'MelisCore/assets/components/modules/admin/calendar/assets/lib/js/fullcalendar.min.js?v=v1.2.3',
                    'MelisCalendar/plugins/fullcalendar.min.js',
//                     'MelisCalendar/plugins/moment/moment.min.js',
//                     'MelisCalendar/plugins/full-calendar/fullcalendar.min.js',
                    'MelisCalendar/js/tools/calendar-tool.js',
                ),
                'css' => array(
                    //'/MelisCore/assets/components/modules/admin/calendar/assets/lib/css/fullcalendar.css',
                    'MelisCalendar/plugins/fullcalendar.css',
//                     'MelisCalendar/plugins/full-calendar/fullcalendar.css',
                    'MelisCalendar/css/calendar.css'
                ),
            ),
            'datas' => array(
                
            ),
            'interface' => array(
                'melistoolcalendar_cof' => array(
                    'conf' => array(
                        'id' => 'id_meliscalendar_leftnemu',
                        'melisKey' => 'meliscalendar_leftnemu',
                        'name' => 'tr_melistoolcalendar_tool_calendar',
                        'icon' => 'fa fa-calendar',
                    ),
                    'forward' => array(
                        'module' => 'MelisCalendar',
                        'controller' => 'Calendar',
                        'action' => 'render-calendar-leftmenu',
                        'jscallback' => '',
                        'jsdatas' => array()
                    ),
                    'interface' => array(
                        'meliscalendar_tool' => array(
                            'conf' => array(
                                'type' => 'melistoolcalendar/interface/melistoolcalendar_tool'
                            ),
                        ),
                    ),
                ),
                'melistoolcalendar_tool' => array(
                    'conf' => array(
                        'id' => 'id_meliscalendar_tool',
                        'melisKey' => 'meliscalendar_tool',
                        'name' => 'tr_melistoolcalendar_tool_calendar',
                        'icon' => 'fa fa-calendar',
                        'rights_checkbox_disable' => true,
                        'follow_regular_rendering' => false,
                    ),
                    'forward' => array(
                            'module' => 'MelisCalendar',
                            'controller' => 'Calendar',
                            'action' => 'render-calendar',
                            'jscallback' => '',
                            'jsdatas' => array()
                    ),
                    'interface' => array(
                        'melistoolcalendar_tool_calendar_content' => array(
                            'conf' => array(
                                'id' => 'id_melistoolcalendar_tool_calendar_content',
                                'melisKey' => 'melistoolcalendar_tool_calendar_content',
                                'name' => 'tr_melistoolcalendar_tool_calendar',
                            ),
                            'forward' => array(
                                'module' => 'MelisCalendar',
                                'controller' => 'Calendar',
                                'action' => 'render-calendar-tool-calendar-content',
                                'jscallback' => 'initCalendarTool();',
                                'jsdatas' => array()
                            ),
                        ),
                        'melistoolcalendar_tool_create_form' => array(
                            'conf' => array(
                                'id' => 'id_melistoolcalendar_tool_create_form',
                                'melisKey' => 'melistoolcalendar_tool_create_form',
                                'name' => 'tr_melistoolcalendar_form_create',
                            ),
                            'forward' => array(
                                'module' => 'MelisCalendar',
                                'controller' => 'Calendar',
                                'action' => 'render-calendar-tool-create-form',
                                'jscallback' => '',
                                'jsdatas' => array()
                            ),
                        ),
                        'melistoolcalendar_tool_recent_added' => array(
                            'conf' => array(
                                'id' => 'id_melistoolcalendar_tool_recent_added',
                                'melisKey' => 'melistoolcalendar_tool_recent_added',
                                'name' => 'tr_melistoolcalendar_recent_added'
                            ),
                            'forward' => array(
                                'module' => 'MelisCalendar',
                                'controller' => 'Calendar',
                                'action' => 'render-calendar-tool-recent-added',
                                'jscallback' => '',
                                'jsdatas' => array()
                            ),
                        ),
                    )
                ),
                'meliscalendar_tool_edit_event_modal' => array(
                    'conf' => array(
                        'id' => 'id_meliscalendar_tool_edit_event_modal',
                        'melisKey' => 'meliscalendar_tool_edit_event_modal',
                        'name' => 'tr_meliscalendar_tool_edit_event_modal',
                        'icon' => 'fa fa-calendar',
                        'rights_checkbox_disable' => true,
                    ),
                    'forward' => array(
                        'module' => 'MelisCalendar',
                        'controller' => 'Calendar',
                        'action' => 'render-calendar-edit-event-modal',
                        //'jscallback' => 'getEventData();',
                        'jsdatas' => array()
                    ),
                ),
            ),
        ),
        'meliscore_dashboard' => array(
            'interface' => array(
                'meliscore_dashboard_calendar' => array(
                    'conf' => array(
                        'id' => 'id_meliscore_dashboard_calendar',
                        'name' => 'tr_meliscore_center_dashboard_Calendar',
                        'melisKey' => 'meliscore_dashboard_calendar',
                        'width' => 6,
                        'height' => 'dashboard-medium',
                    ),
                    'forward' => array(
                        'module' => 'MelisCalendar',
                        'controller' => 'Dashboard',
                        'action' => 'calendar',
                        'jscallback' => 'initDashboardCalendar();',
                        'jsdatas' => array()
                    ),
                ),
            )
        )
    ),
);