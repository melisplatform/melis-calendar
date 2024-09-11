<?php

return array(
    'plugins' => array(
        'meliscore' => array(
            'datas' => array(),
            'interface' => array(
                'meliscore_leftmenu' => array(
                    'interface' => array(
                        'melismarketing_toolstree_section' => array(
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
                    // v6.1.14
                    '/MelisCalendar/plugins/fullcalendar/dist/index.global.min.js',
                    '/MelisCalendar/plugins/fullcalendar/packages/bootstrap5/index.global.min.js',

                    '/MelisCalendar/js/tools/calendar-tool.js',
                ),
                'css' => array(
                    //'/MelisCalendar/plugins/bootstrap-icons/font/bootstrap-icons.min.css',
                    '/MelisCalendar/css/calendar.css'
                ),
                /**
                 * the "build" configuration compiles all assets into one file to make
                 * lesser requests
                 */
                'build' => [
                    // configuration to override "use_build_assets" configuration, if you want to use the normal assets for this module.
                    'disable_bundle' => false,

                    // lists of assets that will be loaded in the layout
                    'css' => [
                        '/MelisCalendar/build/css/bundle.css',

                    ],
                    'js' => [
                        '/MelisCalendar/build/js/bundle.js',
                    ]
                ]
            ),
            'datas' => [
                /**
                 * Used to copy necessary file to
                 * main public/bundles-generated folder
                 */
                'bundle_all_needed_files' => [
                    //will be put inside css folder
                    'css' => [
                        '/plugins/bootstrap-icons/font/fonts'
                    ],
                    //will be put inside js folder
                    'js' => [

                    ]
                ]
            ],
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
    ),
);
