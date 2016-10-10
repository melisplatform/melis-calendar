<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Model\Tables;

use MelisCore\Model\Tables\MelisGenericTable;
use Zend\Db\TableGateway\TableGateway;

class MelisCalendarTable extends MelisGenericTable 
{
    protected $tableGateway;
    protected $idField;
    
    public function __construct(TableGateway $tableGateway)
    {
        parent::__construct($tableGateway);
        $this->idField = 'cal_id';
    }
    
    
    /*
     * SQL for Retrienving Calendar Events for Dashboard Calendar
     */
    public function retrieveDashboardCalendarEvents(){
        $select = $this->tableGateway->getSql()->select();
        // preparing columns for calendar ui
        // as defined column name for calendar ui
        $select->columns(array(new \Zend\Db\Sql\Expression('DISTINCT(cal_date_start) AS dates')));
        $dataCalendar = $this->tableGateway->selectWith($select);
        
        return $dataCalendar;
    }
    
    /*  
     * SQL for Retrienving Calendar Events
     */
    public function retrieveCalendarEvents(){
        
        $select = $this->tableGateway->getSql()->select();
        // preparing columns for calendar ui
        // as defined column name for calendar ui
        $select->columns(
            array(
                new \Zend\Db\Sql\Expression('cal_id AS id'),
                new \Zend\Db\Sql\Expression('CONCAT(cal_id," : ",cal_event_title) AS title'),
                new \Zend\Db\Sql\Expression('cal_date_start AS start'),
                new \Zend\Db\Sql\Expression('cal_date_end AS end'),
            ));
        $dataCalendar = $this->tableGateway->selectWith($select);
        
        return $dataCalendar;
    }
    
    /* 
     * Retrieving Calendar Recent Added
     * Limited to 10 Events
     */
    public function getEventRecentAdded(){
        $select = $this->tableGateway->getSql()->select();
        $select->join('melis_core_user', 'melis_core_user.usr_id = melis_calendar.cal_created_by');
        $select->limit(10);
        $select->order('cal_id DESC');
        $dataCalendar = $this->tableGateway->selectWith($select);
        
        return $dataCalendar;
    }
}