<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Controller;

use MelisCore\Controller\MelisAbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Session\Container;

class CalendarController extends MelisAbstractActionController
{
    /**
     * Render the leftmenu
     */
    public function renderCalendarLeftmenuAction()
    {
        $melisKey = $this->params()->fromRoute('melisKey', '');
        $view = new ViewModel();
        $view->melisKey = $melisKey;
        return $view;
    }
    
    public function renderTestAction() 
    {
        $melisKey = $this->params()->fromRoute('melisKey', '');
        $view = new ViewModel();
        $view->melisKey = $melisKey;
        return $view;
    }
    
    /*
    * Render Calendar Tool Page
    */
    public function renderCalendarAction()
    {
        $melisKey = $this->params()->fromRoute('melisKey', '');
        $view = new ViewModel();
        $view->melisKey = $melisKey;
        return $view;
    }
    
    public function renderCalendarToolCreateFormAction(){
        $melisKey = $this->params()->fromRoute('melisKey', '');
        // Get Element form for Calendar event
        $melisMelisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');
        $appConfigForm = $melisMelisCoreConfig->getFormMergedAndOrdered('meliscalendar/forms/melicalendar_event_form','melicalendar_event_form');
        // Factoring Calendar event and pass to view
        $factory = new \Laminas\Form\Factory();
        $formElements = $this->getServiceManager()->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $propertyForm = $factory->createForm($appConfigForm);
        
        $view = new ViewModel();
        $view->setVariable('meliscalendar_event_title', $propertyForm);
        $view->melisKey = $melisKey;
        return $view;
    }

    /*
* Get all calendar data
* return array
*/
    public function getAllCalendarData()
    {
        $success     = 0;
        $request     = $this->getRequest();
        $data        = array();

        if($request->isGet())
        {
            $calendarData  = $this->getServiceManager()->get('MelisCalendarService');

            if(!empty($calendarData)){
                $data    = "";
                $success = 0;

            }
        }

        return $data;
    }

    public function renderCalendarToolCalendarContentAction(){
        $melisKey = $this->params()->fromRoute('melisKey', '');
        $view = new ViewModel();
        $view->melisKey = $melisKey;
        return $view;
    }
    
    public function renderCalendarToolRecentAddedAction(){
        $melisKey = $this->params()->fromRoute('melisKey', '');
        $calendarTable = $this->getServiceManager()->get('MelisCalendarTable');
        // Retrieving Calendar Event for Recent Added
        $calendarEvent = $calendarTable->getEventRecentAdded();
        // Get Current Language
        $container = new Container('meliscore');
        $locale = $container['melis-lang-locale'];
        $view = new ViewModel();
        $view->recentAdded = ($calendarEvent) ? $calendarEvent->toArray() : array();
        $view->melisKey = $melisKey;
        $view->locale = $locale;
        return $view;
    }
    
    /*  
    * Rending Calendar Modal for Updating Calendar Item Event Title
    * */
    public function renderCalendarEditEventModalAction(){
        $melisKey = $this->params()->fromRoute('melisKey', '');
        // Get Element form for Calendar event
        $melisMelisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');
        $appConfigForm = $melisMelisCoreConfig->getFormMergedAndOrdered('meliscalendar/forms/melicalendar_event_form','melicalendar_event_form');
        // Factoring Calendar event and pass to view
        $factory = new \Laminas\Form\Factory();
        $formElements = $this->getServiceManager()->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $propertyForm = $factory->createForm($appConfigForm);
        // Set Calendar Event Title input non-draggable input
        $propertyForm->get('cal_event_title')->setAttribute('data-draggable', false);
        
        
        $calId = $this->params()->fromQuery('cal_id');
        
        if($calId) {
            
            $calendarTable = $this->getServiceManager()->get('MelisCalendarTable');
            
            $resultEvent = $calendarTable->getEntryById($calId);
            
            if (!empty($resultEvent)){
                $eventData = $resultEvent->current();
                
                if (!empty($eventData)){
                    $propertyForm->bind($eventData);
                }
            }
        }
        
        $view = new ViewModel();
        $view->setVariable('meliscalendar_event_title', $propertyForm);
        $view->melisKey = $melisKey;
        $view->calId = $calId;
        return $view;
    }
    
    /*
    * Rending Calendar Modal Container
    * */
    public function renderCalendarModalAction(){
    
        $id = $this->params()->fromRoute('id', $this->params()->fromQuery('id', ''));
        $melisKey = $this->params()->fromRoute('melisKey', $this->params()->fromQuery('melisKey', ''));
        
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->id = $id;
        $view->melisKey = $melisKey;
        return $view;
    }

}
