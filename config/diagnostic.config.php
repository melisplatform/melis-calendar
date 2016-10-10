<?php

return array(

    'plugins' => array(
        'diagnostic' => array(
            'MelisCalendar' => array(
                
                // tests to execute
                'basicTest' => array(
                    'controller' => 'Diagnostic',
                    'action' => 'basicTest',
                    'payload' => array(
                        'label' => 'tr_melis_module_rights_dir',
                        'module' => 'MelisCalendar'
                    )
                ),
        
                'testModuleTables' => array(
                    'controller' => 'Diagnostic',
                    'action' => 'testModuleTables',
                    'payload' => array(
                        'label' => 'tr_melis_module_db_test',
                        'tables' => array('melis_calendar')
                    ),
                ),
            ),
        
        ),
    ),


);

