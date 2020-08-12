<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Service;

use MelisCore\Service\MelisServiceManager;

/**
 * This service handles access to the calendar
 */
class MelisCalendarService extends MelisServiceManager 
{
	/**
	 * Adding and Updating Calendar Event
	 * 
	 * @param array $postValues
	 * 
	 * if the action is adding new Event, the postValues containing
	 *     cal_event_title
	 *     cal_date_start
	 * if the action is updating the Event title, the postValues containing  
	 *     cal_id
	 *     cal_event_title
	 *     cal_date_start
	 * 
	 * @return Array
	 */
	public function addCalendarEvent($postValues){
		
		$responseData = array();
		
		$calendarTable = $this->getServiceManager()->get('MelisCalendarTable');
		$melisCoreAuth = $this->getServiceManager()->get('MelisCoreAuth');
		
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
	
	/**
	 * Resizing the date range of Calendar Item Event or Updating the dates of the Item event
	 * @param array $postValues, this containing the calendar id, date start, date end
	 *  
	 * @return Array
	 */
	public function reschedCalendarEvent($postValues)
	{
		$responseData = array();
		
		$calendarTable = $this->getServiceManager()->get('MelisCalendarTable');
		
		$melisCoreAuth = $this->getServiceManager()->get('MelisCoreAuth');
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
	
	/**
	 * Deleting Calendar Item Event
	 * 
	 * @param Array $postValues, this containing the calendar id $postValues['cal_id']
	 * 
	 * @return Int|null if the deletion is failed
	 */
	public function deleteCalendarEvent($postValues)
	{
		$calId = null;
		$calendarTable = $this->getServiceManager()->get('MelisCalendarTable');
		$resultEvent = $calendarTable->getEntryById($postValues['cal_id']);
		
		if (!empty($resultEvent)){
			
			$event = $resultEvent->current();
			
			if (!empty($event)){
				$calId = $calendarTable->deleteById($postValues['cal_id']);
			}
		}
		
		return $calId;
	}
}
