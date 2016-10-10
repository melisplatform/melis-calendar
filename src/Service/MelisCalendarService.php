<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * This service handles access to the calendar
 */
class MelisCalendarService  implements  ServiceLocatorAwareInterface
{
	protected $serviceLocator;
	
	public function setServiceLocator(ServiceLocatorInterface $sl)
	{
		$this->serviceLocator = $sl;
		return $this;
	}
	
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}	
	
	/* 
	 * Adding and Updating Calendar Event
	 * 
	 * Adding Required Params:
	 *     cal_event_title
	 *     cal_date_start
	 * Updating Required Params:
	 *     cal_id
	 *     cal_event_title
	 *     cal_date_start
	 * Ex. $postValues['cal_event_title']
	 * 
	 * return:
	 *     response array
	 * */
	public function addCalendarEvent($postValues){
	    
	    $responseData = array();
	    
	    $calendarTable = $this->getServiceLocator()->get('MelisCalendarTable');
	    $melisCoreAuth = $this->getServiceLocator()->get('MelisCoreAuth');
	    
	    $userAuthDatas =  $melisCoreAuth->getStorage()->read();
	    $userId = (int) $userAuthDatas->usr_id;
	     
	    if (!isset($postValues['cal_id']))
	    {
	         
	        $postValues['cal_date_end'] = $postValues['cal_date_start'];
	        $postValues['cal_date_last_update'] = $postValues['cal_date_start'];
	        $postValues['cal_date_added'] = date('Y-m-d H:i:s');
	        $postValues['cal_last_update_by'] = $userId;
	        $postValues['cal_created_by'] = $userId;
	        
	        $eventId = $calendarTable->save($postValues);
	        
	        $responseData = array(
	            'id' => $eventId,
	            'title' => $postValues['cal_event_title'],
	            'start' => $postValues['cal_date_start'],
	            'end' => $postValues['cal_date_end'],
	        );
	         
	    }
	    else
	    {
	        $resultEvent = $calendarTable->getEntryById($postValues['cal_id']);
	         
	        if (!empty($resultEvent)){
	             
	            $event = $resultEvent->current();
	             
	            if (!empty($event)){
	                
        	        $postValues['cal_last_update_by'] = $userId;
        	        $postValues['cal_date_last_update'] = date('Y-m-d H:i:s');
        	         
        	        $calendarTable->save($postValues,$postValues['cal_id']);
        	         
        	        $responseData = array(
        	            'id' => $postValues['cal_id'],
        	            'title' => $postValues['cal_event_title'],
                    );
	            }
	        }
	    }
	    
	    return $responseData;
	}
	
	/* 
	 * Resizing Calendar Item Event or Updating the dates of the Item event
	 * 
	 * Required: 
	 *     cal_id
	 * Ex.  $postValues['cal_id']
	 * 
	 * return:
	 *     response array
	 *  */
	public function reschedCalendarEvent($postValues)
	{
	    $responseData = array();
	    
	    $calendarTable = $this->getServiceLocator()->get('MelisCalendarTable');
	    
	    $melisCoreAuth = $this->getServiceLocator()->get('MelisCoreAuth');
	    $userAuthDatas =  $melisCoreAuth->getStorage()->read();
	    $userId = (int) $userAuthDatas->usr_id;
	    
	    $resultEvent = $calendarTable->getEntryById($postValues['cal_id']);
	     
	    if (!empty($resultEvent)){
	         
	        $event = $resultEvent->current();
	         
	        if (!empty($event)){
	            if ($postValues['cal_date_end']==''){
	                $postValues['cal_date_end'] = $postValues['cal_date_start'];
	            }
	            $postValues['cal_last_update_by'] = $userId;
	            $postValues['cal_date_last_update'] = date('Y-m-d H:i:s');
	            $calendarTable->save($postValues,$postValues['cal_id']);
	            $responseData['success'] = 1;
	        }
	    }
	    
	    return $responseData;
	}
	
	/*
	 * Deleting Calendar Item Event
	 *
	 * Required:
	 *     cal_id
	 * Ex.  $postValues['cal_id']
	 *
	 * return:
	 *     response array
	 *  */
	public function deleteCalendarEvent($postValues)
	{
	    $responseData = array();
	    
	    $calendarTable = $this->getServiceLocator()->get('MelisCalendarTable');
	    $resultEvent = $calendarTable->getEntryById($postValues['cal_id']);
	     
	    if (!empty($resultEvent)){
	         
	        $event = $resultEvent->current();
	         
	        if (!empty($event)){
	            $calendarTable->deleteById($postValues['cal_id']);
	            $responseData['success'] = 1;
	        }
	    }
	    
	    return $responseData;
	}
}