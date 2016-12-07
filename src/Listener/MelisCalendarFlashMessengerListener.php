<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use MelisCore\Listener\MelisCoreGeneralListener;

/**
 * This listener listen to prospects events in order to add entries in the
 * flash messenger
 */
class MelisCalendarFlashMessengerListener extends MelisCoreGeneralListener implements ListenerAggregateInterface
{
	
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
        	'MelisCalendar',
        	array(
        	    'meliscalendar_save_event_end',
        	),
        	function($e){
        		$sm = $e->getTarget()->getServiceLocator();
        		$flashMessenger = $sm->get('MelisCoreFlashMessenger');
        		
        		$params = $e->getParams();
        		$results = $e->getTarget()->forward()->dispatch(
        		    'MelisCore\Controller\MelisFlashMessenger',
        		    array_merge(array('action' => 'log'), $params))->getVariables();
        	},
        -1000);
        
        $this->listeners[] = $callBackHandler;
    }
}