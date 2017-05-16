<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisCalendarTest\Controller;

use MelisCore\ServiceManagerGrabber;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
class MelisCalendarControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = false;
    protected $sm;
    protected $method = 'save';

    public function setUp()
    {
        $this->sm  = new ServiceManagerGrabber();
    }

    /**
     * Get getCalendarTable table
     * @return mixed
     */
    private function getCalendarTable()
    {
        $conf = $this->sm->getPhpUnitTool()->getTable('MelisCalendar', __METHOD__);
        return $this->sm->getTableMock(new $conf['model'], $conf['model_table'], $conf['db_table_name'], $this->method);
    }


    public function getPayload($method)
    {
        return $this->sm->getPhpUnitTool()->getPayload('MelisCalendar', $method);
    }

    /**
     * START ADDING YOUR TESTS HERE
     */

    public function testFetchAllData()
    {
        $data = $this->getCalendarTable()->fetchAll()->toArray();
        $this->assertNotEmpty($data);
    }

    public function testInsertData()
    {
        $payloads = $this->getPayload(__METHOD__);
        $this->method = 'fetchAll';
        $this->getCalendarTable()->save($payloads['dataToInsert']);
    }


    public function testTableAccessWithPayloadFromConfig()
    {
        $payloads = $this->getPayload(__METHOD__);
        $column =  $payloads['dataToCheck']['column'];
        $value =  $payloads['dataToCheck']['value'];
        $data = $this->getCalendarTable()->getEntryByField($column, $value)->current();
        $this->assertNotEmpty($payloads);
        $this->assertNotEmpty($data);

    }



    public function testRemoveData()
    {
        $payloads = $this->getPayload(__METHOD__);
        $columns = $payloads['dataToRemove']['column'];
        $value   = $payloads['dataToRemove']['value'];

        $this->method = 'fetchAll';
        $this->getCalendarTable()->deleteByField($columns, $value);
        $this->assertTrue(true);
    }




}

