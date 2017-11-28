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

## Running the code

### MelisCalendarService  
Aside from the tool coming with module, you can use  the Calendar service to add events from other modules:  
/melis-calendar/src/Service/MelisCalendarService.php  
```
$calendarService  = $this->getServiceLocator()->get('MelisCalendarService');  
```  

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