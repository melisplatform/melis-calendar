<?php

return array(

    'plugins' => array(
        'diagnostic' => array(
            'MelisCalendar' => array(
                // location of your test folder inside the module
                'testFolder' => 'test',
                // moduleTestName is the name of your test folder inside 'testFolder'
                'moduleTestName' => 'MelisCalendarTest',
                // this should be properly setup so we can recreate the factory of the database
                'db' => array(
                    // the keys will used as the function name when generated,
                    'getCalendarTable' => array(
                        'model' => 'MelisCalendar\Model\MelisCalendar',
                        'model_table' => 'MelisCalendar\Model\Tables\MelisCalendarTable',
                        'db_table_name' => 'melis_calendar',
                    ),
                ),
                // these are the various types of methods that you would like to give payloads for testing
                // you don't have to put all the methods in the test controller,
                // instead, just put the methods that will be needing or requiring the payloads for your test.
                'methods' => array(
                    // the key name should correspond to what your test method name in the unit test controller
                    'testInsertData' => array(
                        'payloads' => array(
                            'dataToInsert' => array(
                                'cal_event_title' => 'phpunit calendar test',
                                'cal_date_start' => '2016-07-31',
                                'cal_date_end' => '2016-07-31',
                                'cal_created_by' => 17,
                                'cal_last_update_by' => 17,
                                'cal_date_last_update' => date('Y-m-d H:i:s'),
                                'cal_date_added' => date('Y-m-d H:i:s')
                            )
                        ),
                    ),
                    'testTableAccessWithPayloadFromConfig' => array(
                        'payloads' => array(
                            'dataToCheck' => array(
                                'column' => 'cal_event_title',
                                'value' => 'phpunit calendar test'
                            ),
                        ),
                    ),
                    'testRemoveData' => array(
                        'payloads' => array(
                            'dataToRemove' => array(
                                'column' => 'cal_event_title',
                                'value' => 'phpunit calendar test'
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),


);

