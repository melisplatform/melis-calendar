<?php
return array(
    'plugins' => array(
        'meliscalendar' => array(
            'forms' => array(
                'melicalendar_event_form' => array(
                    'attributes' => array(
                        'name' => 'calendarform',
                        'id' => 'idformcalendar',
                        'method' => 'POST',
                        'action' => '/melis/MelisCalendar/ToolCalendar/addEvent',
                    ),
                    'hydrator'  => 'Laminas\Hydrator\ArraySerializable',
                    'elements' => array(
                        array(
							'spec' => array(
								'name' => 'cal_event_title',
								'type' => 'MelisCalendarDraggableInput',
							    'options' => array(
							      'label' => 'tr_melistoolcalendar_form_event_title',
							      'tooltip' => 'tr_melistoolcalendar_form_event_title tooltip',
							    ),
								'attributes' => array(
									'id' => 'newCalendarEventInt',
									'value' => '',
									'placeholder' => 'tr_melistoolcalendar_form_title',
								    'required' => 'required',
							    ),
						    ),
						),
                    ),
                    'input_filter' => array(
                        'cal_event_title' => array(
                            'name'     => 'cal_event_title',
                            'required' => true,
                            'validators' => array(
                                array(
                                    'name'    => 'StringLength',
                                    'options' => array(
                                        'encoding' => 'UTF-8',
                                        'max'      => 150,
                                        'messages' => array(
                                            \Laminas\Validator\StringLength::TOO_LONG => 'tr_melistoolcalendar_form_event_title_long',
                                        ),
                                    ),
                                ),
                                array(
                                    'name' => 'NotEmpty',
                                    'options' => array(
                                        'messages' => array(
                                            \Laminas\Validator\NotEmpty::IS_EMPTY => 'tr_melistoolcalendar_form_event_title_empty',
                                        ),
                                    ),
                                ),
                            ),
                            'filters'  => array(
                                array('name' => 'StripTags'),
                                array('name' => 'StringTrim'),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
