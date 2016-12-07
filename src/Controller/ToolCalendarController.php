<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * This controller handles the calendar tool
 */
class ToolCalendarController extends AbstractActionController
{
    /**
     * Retrive Calendar Event to initialize calender uifor Dashboard Calendar
     * 
     * @return \Zend\View\Model\JsonModel
     */
    public function retrieveDashboardCalendarEventsAction(){
        $calendarTable = $this->getServiceLocator()->get('MelisCalendarTable');
        $result = $calendarTable->retrieveDashboardCalendarEvents();
        return new JsonModel($result->toArray());
    }
    
    /**
     * Retrieve Calendar Event to initialize calender ui
     * 
     * @return \Zend\View\Model\JsonModel
     */
    public function retrieveCalendarEventsAction(){
        $calendarTable = $this->getServiceLocator()->get('MelisCalendarTable');
        $result = $calendarTable->retrieveCalendarEvents();
        return new JsonModel($result->toArray());
    }
    
    /**
     * This action will save an event to the calendar
     * 
     * @return \Zend\View\Model\JsonModel
     */
    public function saveEventAction(){
        $translator = $this->getServiceLocator()->get('translator');
        
        $request = $this->getRequest();
        // Default Values
        $status  = 0;
        $textMessage = '';
        $errors  = array();
        $textTitle = '';
         
        $responseData = array();
         
        $melisMelisCoreConfig = $this->serviceLocator->get('MelisCoreConfig');
         
        $appConfigForm = $melisMelisCoreConfig->getFormMergedAndOrdered('meliscalendar/forms/melicalendar_event_form','melicalendar_event_form');
         
        $factory = new \Zend\Form\Factory();
        $formElements = $this->serviceLocator->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $propertyForm = $factory->createForm($appConfigForm);
         
        if($request->isPost()) {
             
            $postValues = get_object_vars($request->getPost());
             
            $propertyForm->setData($postValues);
             
            if($propertyForm->isValid()) {
                $calendarService = $this->getServiceLocator()->get('MelisCalendarService');
                $responseData = $calendarService->addCalendarEvent($postValues);
                
                if (!empty($responseData)){
                    $textMessage = $translator->translate('tr_melistoolcalendar_save_event_success');
                    $status = 1;
                }
            }else{
                $errors = $propertyForm->getMessages();
                $textMessage = $translator->translate('tr_melistoolcalendar_form_event_error_msg');
            }
             
            $appConfigForm = $appConfigForm['elements'];
             
            foreach ($errors as $keyError => $valueError)
            {
                foreach ($appConfigForm as $keyForm => $valueForm)
                {
                    if ($valueForm['spec']['name'] == $keyError &&
                        !empty($valueForm['spec']['options']['label']))
                        $errors[$keyError]['label'] = $valueForm['spec']['options']['label'];
                }
            }
        }
         
        $response = array(
            'success' => $status,
            'textTitle' => $translator->translate('tr_melistoolcalendar_save_event_title'),
            'textMessage' => $textMessage,
            'errors' => $errors,
            'event' => $responseData
        );
         
        if ($status){
            $this->getEventManager()->trigger('meliscalendar_save_event_end', $this, $response);
        }
        
        return new JsonModel($response);
    }
    
    /* 
     * Updating Calendar Event by resizing Calendar item event
     */
    public function reschedEventAction(){
        $translator = $this->getServiceLocator()->get('translator');
        
        $request = $this->getRequest();
        // Default Values
        $status  = 0;
        $textMessage = '';
        $errors  = array();
        $textMessage = '';
        $textTitle = '';
         
        if($request->isPost()) {
             
            $postValues = get_object_vars($request->getPost());
             
            if (!empty($postValues)){
                 
                $calendarTable = $this->getServiceLocator()->get('MelisCalendarTable');
                 
                $resultEvent = $calendarTable->getEntryById($postValues['cal_id']);
                 
                if (!empty($resultEvent)){
                     
                    $event = $resultEvent->current();
                     
                    if (!empty($event)){
                         
                        $calendarService = $this->getServiceLocator()->get('MelisCalendarService');
                        $responseData = $calendarService->reschedCalendarEvent($postValues);
                        
                        if (!empty($responseData)){
                            $textMessage = $translator->translate('tr_melistoolcalendar_save_event_success');
                            $status = 1;
                        }
                    }
                }
            }
        }
         
        $response = array(
            'success' => $status,
            'textTitle' => $translator->translate('tr_melistoolcalendar_save_event_title'),
            'textMessage' => $textMessage,
            'errors' => $errors,
        );
        
        if ($status){
            $this->getEventManager()->trigger('meliscalendar_save_event_end', $this, $response);
        }
         
        return new JsonModel($response);
    }
    
    /*  
     * Deleting Calendar item event
     */
    public function deleteEventAction(){
        $translator = $this->getServiceLocator()->get('translator');
        
        $request = $this->getRequest();
        // Default Values
        $status  = 0;
        $textMessage = '';
        $errors  = array();
        $textMessage = '';
        $textTitle = '';
         
        if($request->isPost()) {
             
            $postValues = get_object_vars($request->getPost());
             
            if (!empty($postValues)){
                 
                $calendarService = $this->getServiceLocator()->get('MelisCalendarService');
                $responseData = $calendarService->deleteCalendarEvent($postValues);
                if (!empty($responseData)){
                    $textMessage = $translator->translate('tr_melistoolcalendar_delete_event_success');
                    $status  = 1;
                }else{
                    $textMessage = $translator->translate('tr_melistoolcalendar_delete_event_unable');
                }
            }
        }
         
        $response = array(
            'success' => $status,
            'textTitle' => $translator->translate('tr_melistoolcalendar_delete_event_title'),
            'textMessage' => $textMessage,
            'errors' => $errors,
        );
        
        if ($status){
            $this->getEventManager()->trigger('meliscalendar_save_event_end', $this, $response);
        }
         
        return new JsonModel($response);
    }
    
    /* 
     * Retrieving Calendar item event data for updating
     */
    public function getEventTitleAction(){
        $translator = $this->getServiceLocator()->get('translator');
        
        $request = $this->getRequest();
        // Default Values
        $status  = 0;
        // This can be override with success if result is success
        $textMessage = $translator->translate('tr_melistoolcalendar_unable_get_event');
        $errors  = array();
        $textTitle = '';
        $eventData = array();
        
         
        if($request->isPost()) {
             
            $postValues = get_object_vars($request->getPost());
             
            if (!empty($postValues)){
                 
                $calendarTable = $this->getServiceLocator()->get('MelisCalendarTable');
                 
                $resultEvent = $calendarTable->getEntryById($postValues['cal_id']);
                 
                if (!empty($resultEvent)){
                    $eventData = $resultEvent->current();
                     
                    if (!empty($eventData)){
                        $textMessage = $translator->translate('tr_melistoolcalendar_get_event_success');
                        $status  = 1;
                    }
                }
            }
        }
         
        $response = array(
            'success' => $status,
            'textTitle' => $translator->translate('tr_melistoolcalendar_form_title'),
            'textMessage' => $textMessage,
            'errors' => $errors,
            'eventData' => $eventData
        );
         
        return new JsonModel($response);
    }
}