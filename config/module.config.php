<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

return array(
    'router' => array(
        'routes' => array(
        	'melis-backoffice' => array(
                'child_routes' => array(
                    'application-MelisCalendar' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => 'MelisCalendar',
                            'defaults' => array(
                                '__NAMESPACE__' => 'MelisCalendar\Controller',
                                'controller'    => 'Index',
                                'action'        => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'default' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '/[:controller[/:action]]',
                                    'constraints' => array(
                                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                    ),
                                    'defaults' => array(
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),    
        	),
            /*
           * This route will handle the
           * alone setup of a module
           */
            'setup-melis-calendar' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/MelisCalendar',
                    'defaults' => array(
                        '__NAMESPACE__' => 'MelisCalendar\Controller',
                        'controller'    => '',
                        'action'        => '',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
//
                            ),
                        ),
                    ),
                    'setup' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/setup',
                            'defaults' => array(
                                'controller' => 'MelisCalendar\Controller\MelisSetup',
                                'action' => 'setup-form',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'translator' => array(),
    'service_manager' => array(
        'aliases' => array(
            'MelisCalendarTable' => \MelisCalendar\Model\Tables\MelisCalendarTable::class,
            'MelisCalendarService' => \MelisCalendar\Service\MelisCalendarService::class
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'MelisCalendar\Controller\Calendar' => \MelisCalendar\Controller\CalendarController::class,
            'MelisCalendar\Controller\ToolCalendar' => \MelisCalendar\Controller\ToolCalendarController::class
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'MelisCalendarEventsPlugin' => 'MelisCalendar\Controller\DashboardPlugins\MelisCalendarEventsPlugin',
        )
    ),
    'form_elements' => array(
        'factories' => array(
            'MelisCalendarDraggableInput' => \MelisCalendar\Form\Factory\MelisCalendarDraggableInputFactory::class,
        )
    ),
    'view_manager' => array(
        'doctype'                  => 'HTML5',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/default.phtml',
            
            // Dashboard plugin templates
            'melis-calendar/dashboard/calendar-events' => __DIR__ . '/../view/melis-calendar/dashboard-plugins/calendar-events.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
