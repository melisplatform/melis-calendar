<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Controller;

use MelisCore\Controller\MelisAbstractActionController;
use Laminas\View\Model\JsonModel;

/**
 * This controller handles the calendar tool
 */
class ToolCalendarController extends MelisAbstractActionController
{
    /**
     * get calendar table
     *
     */
    private function getCalendarTable()
    {
        return $this->getServiceManager()->get('MelisCalendarTable');
    }

    /**
     * Retrive Calendar Event to initialize calender uifor Dashboard Calendar
     * 
     * @return \Laminas\View\Model\JsonModel
     */
    public function retrieveDashboardCalendarEventsAction()
    {
        $calendarTable = $this->getCalendarTable();
        $result = $calendarTable->retrieveDashboardCalendarEvents();

        return new JsonModel($result->toArray());
    }

    /**
     * Retrieve Calendar Event to initialize calender ui
     * 
     * @return \Laminas\View\Model\JsonModel
     */
    public function retrieveCalendarEventsAction()
    {
        $calendarTable = $this->getCalendarTable();
        $result = $calendarTable->retrieveCalendarEvents();

        return new JsonModel($result->toArray());
    }
    
    /**
     * This action will save an event to the calendar
     * 
     * @return \Laminas\View\Model\JsonModel
     */
    public function saveEventAction(){

        $translator = $this->getServiceManager()->get('translator');
        
        $request = $this->getRequest();
        // Default Values
        $status  = 0;
        $textMessage = '';
        $errors  = array();
        $textTitle = '';
        
        $responseData = array();
        
        $melisMelisCoreConfig = $this->getServiceManager()->get('MelisCoreConfig');
        
        $appConfigForm = $melisMelisCoreConfig->getFormMergedAndOrdered('meliscalendar/forms/melicalendar_event_form','melicalendar_event_form');
        
        $factory = new \Laminas\Form\Factory();
        $formElements = $this->getServiceManager()->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $propertyForm = $factory->createForm($appConfigForm);
        $melisTool = $this->getServiceManager()->get('MelisCoreTool');
        if($request->isPost()) {
            
            $postValues = $request->getPost()->toArray();
            $postValues = $melisTool->sanitizePost($postValues);
            $propertyForm->setData($postValues);
            
            
            if (empty($postValues['cal_id']))
            {
                $logTypeCode = 'CALENDAR_EVENT_ADD';
            }
            else
            {
                $logTypeCode = 'CALENDAR_EVENT_UPDATE';
            }
            
            if($propertyForm->isValid()) {
                $calendarService = $this->getServiceManager()->get('MelisCalendarService');
                $responseData = $calendarService->addCalendarEvent($postValues);
                
                if (!empty($responseData)){
                    $textMessage = 'tr_melistoolcalendar_save_event_success';
                    $status = 1;
                }
            }else{
                $errors = $propertyForm->getMessages();
                $textMessage = 'tr_melistoolcalendar_form_event_error_msg';
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
            'textTitle' => 'tr_melistoolcalendar_save_event_title',
            'textMessage' => $textMessage,
            'errors' => $errors,
            'event' => $responseData
        );
        
        $calId = null;
        if (!empty($responseData['id']))
        {
            $calId = $responseData['id'];
        }
        
        if ($status){
            $this->getEventManager()->trigger('meliscalendar_save_event_end', $this, array_merge($response, array('typeCode' => $logTypeCode, 'itemId' => $calId)));
        }
        
        return new JsonModel($response);
    }

    /**
     * Retrieving Calendar searched item event data for updating
     */
    public function searchCalendarEventAction(){
        $translator = $this->getServiceManager()->get('translator');
        $textMessage = '';
        $request = $this->getRequest();
        // Default Values
        $id = null;

        if($request->isPost()) {

            $postValues = $request->getPost()->toArray();

            if (!empty($postValues)){

                $calendarService = $this->getServiceManager()->get('MelisCalendarService');
                $id = $responseData = $calendarService->deleteCalendarEvent($postValues);
            }
        }

        $response = array(
            'success' => '0',
            'textTitle' => 'tr_melistoolcalendar_delete_event_title',
            'textMessage' => $textMessage,
            'errors' => $errors,
        );

        return new JsonModel($response);
    }

    /**
     * Updating Calendar Event by resizing Calendar item event
     * @return \Laminas\View\Model\JsonModel
     */
    public function reschedEventAction(){
        $translator = $this->getServiceManager()->get('translator');
        
        $request = $this->getRequest();
        // Default Values
        $id = null;
        $status  = 0;
        $textMessage = '';
        $errors  = array();
        $textMessage = '';
        $textTitle = '';
        
        if($request->isPost()) {
            
            $postValues = $request->getPost()->toArray();
            
            if (!empty($postValues)){
                
                $calendarTable = $this->getServiceManager()->get('MelisCalendarTable');
                
                $id = $postValues['cal_id'];
                $resultEvent = $calendarTable->getEntryById($id);
                
                if (!empty($resultEvent)){
                    
                    $event = $resultEvent->current();
                    
                    if (!empty($event)){
                        
                        $calendarService = $this->getServiceManager()->get('MelisCalendarService');
                        $responseData = $calendarService->reschedCalendarEvent($postValues);
                        
                        if (!empty($responseData)){
                            $textMessage = 'tr_melistoolcalendar_save_event_success';
                            $status = 1;
                        }
                    }
                }
            }
        }
        
        $response = array(
            'success' => $status,
            'textTitle' => 'tr_melistoolcalendar_save_event_title',
            'textMessage' => $textMessage,
            'errors' => $errors,
        );
        
        if ($status){
            $this->getEventManager()->trigger('meliscalendar_save_event_end', $this, array_merge($response, array('typeCode' => 'CALENDAR_EVENT_DRAG', 'itemId' => $id)));
        }
        
        return new JsonModel($response);
    }
    
    /**
     * Deleting Calendar item event
     * 
     * @return \Laminas\View\Model\JsonModel
     */
    public function deleteEventAction(){
        $translator = $this->getServiceManager()->get('translator');
        
        $request = $this->getRequest();
        // Default Values
        $id = null;
        $status  = 0;
        $textMessage = '';
        $errors  = array();
        $textMessage = '';
        $textTitle = '';
        
        if($request->isPost()) {
            
            $postValues = $request->getPost()->toArray();
            
            if (!empty($postValues)){
                
                $calendarService = $this->getServiceManager()->get('MelisCalendarService');
                $id = $responseData = $calendarService->deleteCalendarEvent($postValues);
                if (!is_null($id)){
                    $textMessage = 'tr_melistoolcalendar_delete_event_success';
                    $status  = 1;
                }else{
                    $textMessage = 'tr_melistoolcalendar_delete_event_unable';
                }
            }
        }
        
        $response = array(
            'success' => $status,
            'textTitle' => 'tr_melistoolcalendar_delete_event_title',
            'textMessage' => $textMessage,
            'errors' => $errors,
        );
        
        if ($status){
            $this->getEventManager()->trigger('meliscalendar_save_event_end', $this, array_merge($response, array('typeCode' => 'CALENDAR_EVENT_DELETE', 'itemId' => $id)));
        }
        
        return new JsonModel($response);
    }
    
    /**
     * Retrieving Calendar item event data for updating
     * 
     * @return \Laminas\View\Model\JsonModel
     */
    public function getEventTitleAction(){
        $translator = $this->getServiceManager()->get('translator');
        
        $request = $this->getRequest();
        // Default Values
        $status  = 0;
        // This can be override with success if result is success
        $textMessage = $translator->translate('tr_melistoolcalendar_unable_get_event');
        $errors  = array();
        $textTitle = '';
        $eventData = array();
        
        
        if($request->isPost()) {
            
            $postValues = $request->getPost()->toArray();
            
            if (!empty($postValues)){
                
                $calendarTable = $this->getServiceManager()->get('MelisCalendarTable');
                
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
