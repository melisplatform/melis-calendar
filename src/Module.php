<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Stdlib\ArrayUtils;
use Zend\Session\Container;

use MelisCalendar\Listener\MelisCalendarFlashMessengerListener;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $sm = $e->getApplication()->getServiceManager();
        $routeMatch = $sm->get('router')->match($sm->get('request'));
        if (!empty($routeMatch))
        {
            $routeName = $routeMatch->getMatchedRouteName();
            $module = explode('/', $routeName);
             
            if (!empty($module[0]))
            {
                if ($module[0] == 'melis-backoffice')
                {
                    $eventManager->attach(new MelisCalendarFlashMessengerListener());
                }
            }
        }
        
        $this->createTranslations($e);
    }
    
    public function init(ModuleManager $manager)
    {
        
    }

    public function getConfig()
    {
    	$config = array();
    	$configFiles = array(
			include __DIR__ . '/../config/module.config.php',
			include __DIR__ . '/../config/app.interface.php',
			include __DIR__ . '/../config/app.forms.php',
			include __DIR__ . '/../config/diagnostic.config.php',
            include __DIR__ . '/../config/app.tools.php',
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

        if (!empty($locale))
        {   
            $translationType = array(
                'interface',
                'forms',
            );
            
            foreach($translationType as $type){
                
                $transPath = "module/MelisModuleConfig/languages/MelisCalendar/$locale.$type.php";
                
                if(!file_exists($transPath)){
                    
                    // if translation is not found, use melis default translations
                    $locale = (file_exists(__DIR__ . "/../language/$locale.$type.php"))? $locale : "en_EN";
                    $transPath = __DIR__ . "/../language/$locale.$type.php";
                }
                
                $translator->addTranslationFile('phparray', $transPath);
            }
        }
    }
 
}
