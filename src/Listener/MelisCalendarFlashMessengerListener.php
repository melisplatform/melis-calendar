<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use MelisCore\Listener\MelisGeneralListener;

/**
 * This listener listen to prospects events in order to add entries in the
 * flash messenger
 */
class MelisCalendarFlashMessengerListener extends MelisGeneralListener implements ListenerAggregateInterface
{
	
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $sharedEvents      = $events->getSharedManager();
        
        $callBackHandler = $sharedEvents->attach(
            'MelisCalendar',
            'meliscalendar_save_event_end',
            function($e){
                $sm = $e->getTarget()->getServiceManager();
                $flashMessenger = $sm->get('MelisCoreFlashMessenger');

                $params = $e->getParams();
                $results = $e->getTarget()->forward()->dispatch(
                    'MelisCore\Controller\MelisFlashMessenger',
                    array_merge(array('action' => 'log'), $params))->getVariables();
            },
            -1000
        );
        
        $this->listeners[] = $callBackHandler;
    }
}
