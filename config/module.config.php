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
        ),
    ),
    'translator' => array(
        'locale' => 'en_EN',
    ),
    'service_manager' => array(
        'aliases' => array(
            'translator' => 'MvcTranslator',
            'MelisCalendarTable' => 'MelisCalendar\Model\Tables\MelisCalendarTable',
        ),
        'factories' => array(
            'MelisCalendarService' => 'MelisCalendar\Service\Factory\MelisCalendarServiceFactory',
            
            'MelisCalendar\Model\Tables\MelisCalendarTable' => 'MelisCalendar\Model\Tables\Factory\MelisCalendarTableFactory',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'MelisCalendar\Controller\Dashboard' => 'MelisCalendar\Controller\DashboardController',
            'MelisCalendar\Controller\Calendar' => 'MelisCalendar\Controller\CalendarController',
            'MelisCalendar\Controller\ToolCalendar' => 'MelisCalendar\Controller\ToolCalendarController',
            'MelisCalendar\Controller\MelisSetup' => 'MelisCalendar\Controller\MelisSetupController',
        ),
    ),
    'form_elements' => array(
        'factories' => array(
            'MelisCalendarDraggableInput' => 'MelisCalendar\Form\Factory\MelisCalendarDraggableInputFactory',
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/default.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
