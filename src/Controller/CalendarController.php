<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class CalendarController extends AbstractActionController
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
        $melisMelisCoreConfig = $this->serviceLocator->get('MelisCoreConfig');
        $appConfigForm = $melisMelisCoreConfig->getFormMergedAndOrdered('meliscalendar/forms/melicalendar_event_form','melicalendar_event_form');
        // Factoring Calendar event and pass to view
        $factory = new \Zend\Form\Factory();
        $formElements = $this->getServiceLocator()->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $propertyForm = $factory->createForm($appConfigForm);
        
    	$view = new ViewModel();
    	$view->setVariable('meliscalendar_event_title', $propertyForm);
    	$view->melisKey = $melisKey;
    	return $view;
    }
    
    public function renderCalendarToolCalendarContentAction(){
        $melisKey = $this->params()->fromRoute('melisKey', '');
        $view = new ViewModel();
        $view->melisKey = $melisKey;
        return $view;
    }
    
    public function renderCalendarToolRecentAddedAction(){
        $melisKey = $this->params()->fromRoute('melisKey', '');
        $calendarTable = $this->getServiceLocator()->get('MelisCalendarTable');
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
        $melisMelisCoreConfig = $this->serviceLocator->get('MelisCoreConfig');
        $appConfigForm = $melisMelisCoreConfig->getFormMergedAndOrdered('meliscalendar/forms/melicalendar_event_form','melicalendar_event_form');
        // Factoring Calendar event and pass to view
        $factory = new \Zend\Form\Factory();
        $formElements = $this->serviceLocator->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $propertyForm = $factory->createForm($appConfigForm);
        // Set Calendar Event Title input non-draggable input
        $propertyForm->get('cal_event_title')->setAttribute('data-draggable', false);
        
        $view = new ViewModel();
        $view->setVariable('meliscalendar_event_title', $propertyForm);
        $view->melisKey = $melisKey;
        return $view;
    }
    
    /*
     * Rending Calendar Modal Container
     * */
    public function renderCalendarModalAction(){
    
        $id = $this->params()->fromRoute('id', $this->params()->fromQuery('id', ''));
        $melisKey = $this->params()->fromRoute('melisKey', $this->params()->fromQuery('melisKey', ''));
        
        $view = new ViewModel();
        $view->setTerminal(false);
        $view->id = $id;
        $view->melisKey = $melisKey;
        return $view;
    }
    
    
}