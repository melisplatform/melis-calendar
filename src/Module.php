<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\Hydrator\ObjectProperty;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Stdlib\ArrayUtils;
use Zend\Session\Container;
use MelisCalendar\Model\MelisCalendar;
use MelisCalendar\Model\Tables\MelisCalendarTable;
// use MelisCmsProspects\Listener\MelisCmsProspectFlashMessengerListener;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $this->createTranslations($e);
    }
    
    public function init(ModuleManager $manager)
    {
        
    }

    public function getConfig()
    {
    	$config = array();
    	$configFiles = array(
			include __DIR__ . '/config/module.config.php',
			include __DIR__ . '/config/app.interface.php',
			include __DIR__ . '/config/app.forms.php',
			include __DIR__ . '/config/diagnostic.config.php',
            include __DIR__ . '/config/app.tools.php',
    	);
    	
    	foreach ($configFiles as $file) {
    		$config = ArrayUtils::merge($config, $file);
    	} 
    	
    	return $config;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function createTranslations($e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $translator = $sm->get('translator');

        // Get the locale used from meliscore session
        $container = new Container('meliscore');
        $locale = $container['melis-lang-locale'];

        // Load files
        $translator->addTranslationFile('phparray', __DIR__ . '/language/' . $locale . '.interface.php');
        $translator->addTranslationFile('phparray', __DIR__ . '/language/' . $locale . '.forms.php');
    }
    
    public function getServiceConfig()
    {
        return array(
			'factories' => array(
				'MelisCalendar\Model\Tables\MelisCalendarTable' =>  function($sm) {
					return new MelisCalendarTable($sm->get('MelisCalendarGateway'));
				},
			    'MelisCalendarGateway' => function($sm) {
			         $hydratingResultSet = new HydratingResultSet(new ObjectProperty(), new MelisCalendar());
			         return new TableGateway('melis_calendar', $sm->get('Zend\Db\Adapter\Adapter'), null, $hydratingResultSet);
			    },
			    'MelisCalendar\Service\MelisCalendarService' =>  function($sm) {
					$melisCalendarService = new \MelisCalendar\Service\MelisCalendarService();
					$melisCalendarService->setServiceLocator($sm);
					return $melisCalendarService;
				},
			),
        );
    }
 
}
