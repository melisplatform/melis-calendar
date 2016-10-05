<?php

return array(
    'plugins' => array(
        'meliscalendar' => array(
            'tools' => array(   
                'meliscalendar_tool' => array(
                    'conf' => array(
                        'title' => 'tr_melistoolcalendar_title_tool',
                        'id' => 'id_melistoolcalendar_title_tool',
                    ),
                    'modals' => array(
                        'melicalendar_modal_event_edit' => array(
                            'id' => 'id_melicalendar_modal_event_edit',
                            'class' => 'fa fa-calendar',
                            'tab-header' => '',
                            'tab-text' => 'tr_melistoolcalendar_update_event_title',
                            'content' => array(
                                'module' => 'MelisCalendar',
                                'controller' => 'Calendar',
                                'action' => 'render-calendar-modal-edit',
                            ),
                        ),
                        'melicalendar_modal_empty_modal' => array( // will be used when a user doesn't have access to the modals
                            'id' => 'id_melicalendar_modal_empty_modal',
                            'class' => 'fa fa-calendar',
                            'tab-header' => '',
                            'tab-text' => 'tr_melistoolcalendar_update_event_title',
                            'content' => array(
                                'module' => 'MelisCalendar',
                                'controller' => 'Calendar',
                                'action' => 'render-modal-empty-handler'
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);