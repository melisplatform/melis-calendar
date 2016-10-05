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
        
                'fileCreationTest' => array(
                    'controller' => 'Diagnostic',
                    'action' => 'fileCreationTest',
                    'payload' => array(
                        'label' => 'tr_melis_module_basic_action_test',
                        'path' => MELIS_MODULES_FOLDER.'MelisCalendar/public/',
                        'file' => 'test_file_creation.txt'
                    ),
                ),
        
                'testModuleTables' => array(
                    'controller' => 'Diagnostic',
                    'action' => 'testModuleTables',
                    'payload' => array(
                        'label' => 'tr_melis_module_db_test',
                        'tables' => array('melis_calendar', 'melis_melis', 'melis_test')
                    ),
                ),
            ),
        
        ),
    ),


);

