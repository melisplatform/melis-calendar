$(function(){
	initDashboardCalendar();
	// bidning tool action
	$("body").on("click", ".delete-event", function(e){ 
		// deleting event using event item delete button
		calendarTool.deleteEvent($(this).data('id'));
	});
	$("body").on("click", ".update-event", function(e){ 
		// editing event title
		// this action will appear the modal for updating event title
		calendarTool.editEventTitle($(this).data('id'));
	});
	$("body").on("click", ".btnSaveEventTitle", function(e){
		// saving event title from modal
		calendarTool.saveEventTitle($(this).data('id'));
	});	
});

// Responsive During Resize
$(window).on('resize',function(){
	responsiveCalendar();
});

// Responsive Function
function responsiveCalendar(){
	if( $(window).width() <= 991){
		$('#id_meliscalendar_tool .row .col-md-4').insertAfter($(this).parent().find('#id_meliscalendar_tool .row .col-md-8'));
	} else {
		$('#id_meliscalendar_tool .row .col-md-4').insertBefore($(this).parent().find('#id_meliscalendar_tool .row .col-md-8'));
	}
}

// Dashboard Calendar Init
window.initDashboardCalendar = function(){
	calendarTool.initDashboardCalendar();
}

// Local variable Full Calendar Ready Status
var fc_ready = false;

// Calendar Tool Init
window.initCalendarTool = function() {
	
	responsiveCalendar();
	// local variable
	var calVal_eventId = null;
	var calVal_id = null;
	var calVal_melisKey = null;
	var flag_requesting = false;
	
	// initialize event title input as dragable element
	$('.melis-draggable-input').draggable({
		zIndex: 999,
		revert: true,      // will cause the event to go back to its
		revertDuration: 0,  //  original position after the drag,
		start: function() { if (typeof mainYScroller != 'undefined') mainYScroller.disable(); },
        stop: function() { if (typeof mainYScroller != 'undefined') mainYScroller.enable(); }
	});

	// mobile FIX to open put text in the box
	$('.melis-draggable-input').on('click', function() {
		$(this).focus();
	});
	
	// calendar initialization
	$('#calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
		},
		buttonText :{
		    today:    translations.tr_melistoolcalendar_fullcalendar_today,
		    month:    'month',
		    week:     'week',
		    day:      'day'
		},
		monthNames : [
            translations.tr_melistoolcalendar_fullcalendar_jan,
            translations.tr_melistoolcalendar_fullcalendar_feb,
            translations.tr_melistoolcalendar_fullcalendar_mar,
            translations.tr_melistoolcalendar_fullcalendar_apr,
            translations.tr_melistoolcalendar_fullcalendar_may,
            translations.tr_melistoolcalendar_fullcalendar_jun,
            translations.tr_melistoolcalendar_fullcalendar_jul,
            translations.tr_melistoolcalendar_fullcalendar_aug,
            translations.tr_melistoolcalendar_fullcalendar_sep,
            translations.tr_melistoolcalendar_fullcalendar_oct,
            translations.tr_melistoolcalendar_fullcalendar_nov,
            translations.tr_melistoolcalendar_fullcalendar_dec,
        ],
	    dayNamesShort : [
            translations.tr_melistoolcalendar_fullcalendar_sun,
            translations.tr_melistoolcalendar_fullcalendar_mon,
            translations.tr_melistoolcalendar_fullcalendar_tue,
            translations.tr_melistoolcalendar_fullcalendar_wed,
            translations.tr_melistoolcalendar_fullcalendar_thu,
            translations.tr_melistoolcalendar_fullcalendar_fri,
            translations.tr_melistoolcalendar_fullcalendar_sat,
        ],
		editable: true,
		droppable: true,
		titleFormat : {
			day: 'YYYY-MM-DD'
		},
		// getting event for calendar from server
		events: '/melis/MelisCalendar/ToolCalendar/retrieveCalendarEvents',
		// calendar event
		// drop new element as new event
		drop: function(date, allDay) 
		{
			var dataString = new Array();
			// get data from input
			dataString.push({
				name: "cal_event_title",
				value: $('#newCalendarEventInt').val()
			});
			// get date data from param
			dataString.push({
				name: "cal_date_start",
				value: $.fullCalendar.formatDate(date, "yyyy-MM-dd")
			});
			
			dataString = $.param(dataString);
			// posting to server as new calendar event
			$.ajax({
		        type        : 'POST', 
		        url         : '/melis/MelisCalendar/ToolCalendar/saveEvent',
		        data		: dataString,
		        dataType    : 'json',
		        encode		: true
			}).done(function(data) {
				if(data.success) {
					if(data.event !== null){
						// get event data from ajax/http request reponse
						var event = data.event;
						// init event Object by using data response
						var eventObject = {
							id: event.id,
							title: event.id + ' : ' + event.title, // concat id and title as calendar item event title
							start: event.start,
							end: event.end,
						};
							
						// retrieve the dropped element's stored Event Object
						var originalEventObject = eventObject;
						
						// we need to copy it, so that multiple events don't have a reference to the same object
						var copiedEventObject = $.extend({}, originalEventObject);
						
						// render the event on the calendar
						// the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
						$('#calendar').fullCalendar('renderEvent', copiedEventObject, true);
						
						// init input as empty
						$('#newCalendarEventInt').val('');
						
						// Reload Recent Added Widget
						melisHelper.zoneReload('id_melistoolcalendar_tool_recent_added','melistoolcalendar_tool_recent_added');
						
						// Notifications
						melisHelper.melisOkNotification(data.textTitle, data.textMessage);
						melisCore.flashMessenger();
					}
				} else {
					melisCoreTool.alertDanger("#siteaddalert", '', data.textMessage);
					melisHelper.melisKoNotification(data.textTitle, data.textMessage, data.errors);
				}
			}).fail(function(){
				alert( translations.tr_meliscore_error_message );
			});
			
		},eventRender: function(event, element) {
			// rendering calendar item event
			// Update and Delete icon render to each calendar event
            element.append( "<span class='event-icon'><i class='fa fa-pencil-square-o update-event' data-id='"+event._id+"'></i><i class='fa fa-trash-o delete-event' data-id='"+event._id+"'></i></span>" );
        },eventResize : function(event){
        	// calendar event that trigger when event resized
			calendarTool.reschedEvent(event);
		},eventDrop: function (event){
			// calendar event that trigger where event drag and drop to another date
			calendarTool.reschedEvent(event);
		},eventAfterAllRender : function(event){
			// Go to specific date
			// This is trigger when updating Calendar Event
			calendarTool.gotoFCdate();
			// Re init fc_ready to ready/ready to maniplulate
			fc_ready = true;
		},loading: function () { 
			// Re init fc_ready to ready/ready to maniplulate
			fc_ready = true;
        }
	});
}

// Local varial Year and Month
var fcal_year_client_event = null;
var fcal_month_client_event = null;

// calendar functionalities
var calendarTool = {
		
	// calendar initialization
	getCalendarData: function() {
		initCalendarTool();
	},
	// dragging the calendar event date covered
	reschedEvent: function(event) {
		// set variable from param
		var id = event.id;
	  	var start = $.fullCalendar.formatDate(event.start, "yyyy-MM-dd");
	  	var end = $.fullCalendar.formatDate(event.end, "yyyy-MM-dd");
	  	
	  	// prepairing dataString to post to server
	  	var dataString = new Array();
	  	dataString.push({
			name: "cal_id",
			value: event.id
		});
	  	
	  	dataString.push({
			name: "cal_date_start",
			value: start
		});
	  	
	  	dataString.push({
			name: "cal_date_end",
			value: end
		});
		dataString = $.param(dataString);
		
	  	$.ajax({
	  		type        : 'POST', 
	        url         : '/melis/MelisCalendar/ToolCalendar/reschedEvent',
	        data		: dataString,
	        dataType    : 'json',
	        encode		: true
	  	}).done(function(data) {
			if(data.success) {
				// Notifications
				melisHelper.melisOkNotification(data.textTitle, data.textMessage);
				melisCore.flashMessenger();
			}else{
				melisCoreTool.alertDanger("#siteaddalert", '', data.textMessage);
				melisHelper.melisKoNotification(data.textTitle, data.textMessage, data.errors);
			}
		}).fail(function(){
			alert( translations.tr_meliscore_error_message );
		});
	},
	// deleting calendar event
	deleteEvent: function(eventId){
		// deletion confirmation
		melisCoreTool.confirm(
		translations.tr_melistoolcalendar_delete_event_btn_yes,
		translations.tr_melistoolcalendar_delete_event_btn_no,
		translations.tr_melistoolcalendar_delete_event_title, 
		translations.tr_melistoolcalendar_delete_event_confirm, 
		function() {
			$.ajax({
		        type        : 'POST', 
		        url         :  '/melis/MelisCalendar/ToolCalendar/deleteEvent',
		        data		: {cal_id: eventId},
		        dataType    : 'json',
		        encode		: true,
		    }).done(function(data) {
		    	if(data.success) {
		    		// updating calendar after deleting event
		    		$('#calendar').fullCalendar('removeEvents',eventId);
		    		// Notifications
					melisHelper.melisOkNotification(data.textTitle, data.textMessage);
					melisCore.flashMessenger();
					
					// Reload Recent Added Widget
					melisHelper.zoneReload('id_melistoolcalendar_tool_recent_added','melistoolcalendar_tool_recent_added');
		    	}else{
		    		melisCoreTool.alertDanger("#siteaddalert", '', data.textMessage);
					melisHelper.melisKoNotification(data.textTitle, data.textMessage, data.errors);
		    	}
			}).fail(function(){
				alert( translations.tr_meliscore_error_message );
			});
		});
	},
	// editing calendar event
	// this function is to show modal for Event title edition
	editEventTitle: function(eventId){
		// initialize of local variable calendar id
		calVal_eventId = eventId;
		calVal_id = 'id_meliscalendar_tool_edit_event_modal';
		calVal_melisKey = 'meliscalendar_tool_edit_event_modal';
		modalUrl = '/melis/MelisCalendar/Calendar/renderCalendarModal';
		// requesitng to create modal and display after
    	melisHelper.createModal(calVal_id, calVal_melisKey, false, {cal_id: eventId}, modalUrl);
	},
	// saving calendar event
	saveEventTitle : function(){
		// prepairing dataString to post to server
		var dataString = new Array();
	  	dataString.push({
			name: "cal_id",
			value: calVal_eventId
		});
	  	// Retrieving the Event title directly from the input field
	  	var eventTitle = $('body #'+calVal_id+' #newCalendarEventInt').val();
	  	dataString.push({
			name: "cal_event_title",
			value: eventTitle
		});
		dataString = $.param(dataString);
		
	  	$.ajax({
	  		type        : 'POST', 
	        url         : '/melis/MelisCalendar/ToolCalendar/saveEvent',
	        data		: dataString,
	        dataType    : 'json',
	        encode		: true
	  	}).done(function(data) {
			if(data.success) {
				// Retrieving Calendar event id
				var eventData = $("#calendar").fullCalendar('clientEvents', calVal_eventId)[0];
				// Generating event title
				var newTitleEvent = calVal_eventId +" : "+eventTitle;
				// Updating calendar event using FullCalendar method
				eventData.title = newTitleEvent;
				$('#calendar').fullCalendar('updateEvent', eventData);
				
				//close generated modal after saving event title
				$('#'+calVal_id+'_container').modal('hide');
				
				// Notifications
				melisHelper.melisOkNotification(data.textTitle, data.textMessage);
				melisCore.flashMessenger();
			}else{
				melisCoreTool.alertDanger("#siteaddalert", '', data.textMessage);
				melisHelper.melisKoNotification(data.textTitle, data.textMessage, data.errors);
			}
		}).fail(function(){
			alert( translations.tr_meliscore_error_message );
		});
	},
	// Go to specific date
	// This is trigger when updating Calendar Event
	gotoFCdate: function(){
		if(fcal_year_client_event!=null&&fcal_month_client_event!=null){
			$('#calendar').fullCalendar( 'gotoDate', fcal_year_client_event, fcal_month_client_event);
			// ReInitialize to default value after Executed this function
			fcal_year_client_event = null;
			fcal_month_client_event = null;
		}
	},
	// Dashboard Calendar
	initDashboardCalendar: function(){
		var calendar_events = new Array;
		
		// posting to server as new calendar event
		$.ajax({
	        url         : '/melis/MelisCalendar/ToolCalendar/retrieveDashboardCalendarEvents',
	        dataType    : 'json',
	        encode		: true
		}).done(function(data) {
			// Adding Date Event to Array
			$.each(data,function(){
				calendar_events.push(String(this.dates));
			});
			
			//Datepicker inline initialization
			$('#dashbaorad-calendar-tool').datepicker({ 
				format: "yyyy-mm-dd",
				inline: true,
				showOtherMonths:true,
				todayHighlight: true,
				toggleActive: true,
				// Rendering Calendar Event from Ajax to this Inline calendar
				beforeShowDay: function(date){
			        var d = date;
			        var curr_date = ("0" + d.getDate()).slice(-2); // Formating in 2 digit number
			        var curr_month = ("0" + (d.getMonth() + 1)).slice(-2); // Months are zero based and Formating in 2 digit number
			        var curr_year = d.getFullYear(); // Fetching Year from date param
			        var formattedDate = curr_year + "-" + curr_month + "-" + curr_date;
			        	//Checking if Date exist of Calendar Tool Events
			           	if ($.inArray(formattedDate, calendar_events) != -1){
			           		return {
			           			classes: 'calendar-highlight calendar-event', // Highlight Date
			           		};
			           	}
			        return;
			    }
			}).on('changeDate', function(e){
				var formattedDate = e.format('yyyy-mm-dd');
				if ($.inArray(formattedDate, calendar_events) != -1){
					$("#id_meliscore_menu_toolstree li[data-tool-id='id_meliscalendar_tool']").trigger("click");
					if($("#id_meliscalendar_tool").length){
						// Init local variable year and month
						fcal_year_client_event = parseInt(e.format('yyyy'));
						fcal_month_client_event = (parseInt(e.format('m'))-1);
						
						if(fc_ready){
							melisHelper.zoneReload("id_melistoolcalendar_tool_calendar_content", "melistoolcalendar_tool_calendar_content");
						}
						
					}
				}
		    });
			
		}).fail(function(){
			alert( translations.tr_meliscore_error_message );
		});
	}
}
