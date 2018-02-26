# melis-calendar

MelisCalendar is made to provide a calendar tool and dashboard item to help schedule events on the platform.

## Getting Started

These instructions will get you a copy of the project up and running on your machine.  
This Melis Platform module is made to work with the MelisCore.

### Prerequisites

You will need to install melisplatform/melis-core in order to have this module running.  
This will automatically be done when using composer.

### Installing

Run the composer command:
```
composer require melisplatform/melis-calendar
```

### Database    

Database model is accessible on the MySQL Workbench file:  
/melis-calendar/install/sql/model  
Database will be installed through composer and its hooks.  
In case of problems, SQL files are located here:  
/melis-calendar/install/sql  

## Tools & Elements provided

* Dashboard Calendar
* Calendar Tool

## Running the code

### MelisCalendarService  
Aside from the tool coming with module, you can use  the Calendar service to add events from other modules:  
/melis-calendar/src/Service/MelisCalendarService.php  
```
$calendarService  = $this->getServiceLocator()->get('MelisCalendarService');  
```  

### MelisCms Forms  

#### Forms factories
All Melis CMS News forms are built using Form Factories.  
All form configuration are available in the file: /melis-cms-news/config/app.forms.php  
Any module can override or add items in this form by building the keys in an array and marge it in the Module.php config creation part.  
``` 
return array(
	'plugins' => array(

		// meliscalendar array
		'meliscalendar' => array(

			// Form key
			'forms' => array(

				// MelisCalendar Event Form form
				'melicalendar_event_form' => array(
					'attributes' => array(
						'name' => 'calendarform',
						'id' => 'idformcalendar',
						'method' => 'POST',
						'action' => '/melis/MelisCalendar/ToolCalendar/addEvent',
					),
					'hydrator'  => 'Zend\Stdlib\Hydrator\ArraySerializable',
					'elements' => array(  
						array(
							'spec' => array(
									...
							),
						),
					),
					'input_filter' => array(      
						'cal_event_title' => array(
								...
						),   
					),
				),
			), 
		),
	),
),
``` 

#### Forms elements
MelisCmsCalendar provides form elements to be used in forms:  
* MelisCalendarDraggableInput: drag'n'drop input for the calendar 


### Listening to services and update behavior with custom code  
```  
$callBackHandler = $sharedEvents->attach(  
	'MelisCalendar',  
	array(  
	    'meliscalendar_save_event_end',  
	),  
	function($e){  
		$sm = $e->getTarget()->getServiceLocator();  
		
		// Get parameters  
		$params = $e->getParams();  
		
		// Code here  
	},
10);
```  

## Authors

* **Melis Technology** - [www.melistechnology.com](https://www.melistechnology.com/)

See also the list of [contributors](https://github.com/melisplatform/melis-calendar/contributors) who participated in this project.


## License

This project is licensed under the OSL-3.0 License - see the [LICENSE.md](LICENSE.md) file for details