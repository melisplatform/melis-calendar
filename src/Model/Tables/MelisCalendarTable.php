<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendar\Model\Tables;

use MelisCore\Model\Tables\MelisGenericTable;
use Laminas\Db\TableGateway\TableGateway;

class MelisCalendarTable extends MelisGenericTable 
{

    // table
    const TABLE = 'melis_calendar';
    // table primary key 
    const PRIMARY_KEY = "cal_id";
    
    public function __construct()
    {
        $this->idField = self::PRIMARY_KEY;
    }
    
    /*
     * SQL for Retrienving Calendar Events for Dashboard Calendar
     */
    public function retrieveDashboardCalendarEvents()
    {
        $select = $this->tableGateway->getSql()->select();
        // preparing columns for calendar ui
        // as defined column name for calendar ui
        $select->columns(array(new \Laminas\Db\Sql\Expression('DISTINCT(cal_date_start) AS dates')));
        $dataCalendar = $this->tableGateway->selectWith($select);
        
        return $dataCalendar;
    }
    
    /*  
     * SQL for Retrienving Calendar Events
     */
    public function retrieveCalendarEvents()
    {
        $select = $this->tableGateway->getSql()->select();
        // preparing columns for calendar ui
        // as defined column name for calendar ui
        $select->columns(
            array(
                new \Laminas\Db\Sql\Expression('cal_id AS id'),
                new \Laminas\Db\Sql\Expression('CONCAT(cal_id," : ",cal_event_title) AS title'),
                new \Laminas\Db\Sql\Expression('cal_date_start AS start'),
                new \Laminas\Db\Sql\Expression('cal_date_end AS end'),
            ));
        $dataCalendar = $this->tableGateway->selectWith($select);
        
        return $dataCalendar;
    }
    
    /* 
     * Retrieving Calendar Recent Added
     * Limited to 10 Events
     */
    public function getEventRecentAdded()
    {
        $select = $this->tableGateway->getSql()->select();
        $select->join('melis_core_user', 'melis_core_user.usr_id = melis_calendar.cal_created_by');
        $select->limit(10);
        $select->order('cal_id DESC');
        $dataCalendar = $this->tableGateway->selectWith($select);
        
        return $dataCalendar;
    }
}
