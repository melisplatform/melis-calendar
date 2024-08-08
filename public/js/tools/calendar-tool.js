// Responsive Function
function responsiveCalendar() {
	var $this 			= $(this),
		$calendar_tool 	= $('#id_meliscalendar_tool .row .col-md-4');

		if( $(window).width() <= 991 ) {
			$calendar_tool.insertAfter( $this.parent().find('#id_meliscalendar_tool .row .col-md-8') );
		} else {
			$calendar_tool.insertBefore( $this.parent().find('#id_meliscalendar_tool .row .col-md-8') );
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
	var calVal_eventId 	= null,
		calVal_id 		= null,
		calVal_melisKey = null,
		flag_requesting = false;

		// initialize event title input as dragable element
		/* $('.melis-draggable-input').draggable({
			zIndex: 999,
			revert: true,      // will cause the event to go back to its
			revertDuration: 0,  //  original position after the drag,
			start: function() { if (typeof mainYScroller != 'undefined') mainYScroller.disable(); },
			stop: function() { if (typeof mainYScroller != 'undefined') mainYScroller.enable(); }
		}); */

		var draggableEl = document.getElementById("idformcalendar");

		var draggable = new FullCalendar.Draggable(draggableEl, {
			itemSelector: '.melis-draggable-input',
			eventData: function(eventEl) {
				console.log(`Draggable eventData eventEl: `, eventEl);
				return {
					title: $(eventEl).closest(".input-group").find("#newCalendarEventInt").val()
				};
			}
		});

		// mobile FIX to open put text in the box, 
		$('.melis-draggable-input').on('click', function() {
			var $this = $(this);
				$this.trigger("focus");
		});

		var calendarEl 			= document.getElementById('calendar-tool'),
			initialLocaleCode 	= 'en';
		
			if ( $(calendarEl).length ) {
				// calendar tool initialization
				var calendar = new FullCalendar.Calendar(calendarEl, {
					themeSystem: 'bootstrap5',
					initialView: 'dayGridMonth',
					nowIndicator: true,
					headerToolbar: {
						left: 'prev,next today',
						center: 'title',
						right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
					},
					buttonText :{
						today:    translations.tr_melistoolcalendar_fullcalendar_today,
						month:    'month',
						week:     'week',
						day:      'day'
					},
					//
					titleFormat: { 
						month: 'long', year: 'numeric'
					},
					timeZone: 'local',
					// https://codepen.io/pen?&editors=001,locale option implemenation
					locale: initialLocaleCode,
					editable: true,
					droppable: true,
					dayMaxEvents: true,
					// getting event for calendar from server
					events: '/melis/MelisCalendar/ToolCalendar/retrieveCalendarEvents',
					drop: function(date, allDay) {
						var dataString = new Array();

							//get data from input
							dataString.push({
								name: "cal_event_title",
								value: $("#newCalendarEventInt").val()
							});

							// get data from param
							dataString.push({
								name: "cal_date_start",
								// "yyyy-MM-dd", { year: 'numeric', month: '2-digit', day: '2-digit' }
								// 2024-08-08
								// date.dateStr, calendar.formatDate(date, { year: 'numeric', month: '2-digit', day: '2-digit' })
								value: date.dateStr
							});

							dataString = $.param(dataString);

							// posting to server as new calendar event
							$.ajax({
								type: 'POST',
								url: '/melis/MelisCalendar/ToolCalendar/saveEvent',
								data: dataString,
								dataType: 'json',
								encode: true
							}).done(function(data) {
								if ( data.success ) {
									if ( data.event !== null ) {
										// get event data from ajax/http request response
										var event = data.event;

										// init event Object by using data response
										var eventObject = {
											id: event.id,
											title: event.id + ' : ' + event.title, // concat id and title as calendar item event title
											start: event.start,
											end: event.end
										}

										// retrieve the dropped element's stored Event Object
										var originalEventObject = eventObject;

										// we need to copy it, so that multiple events don't have a reference to the same object
										var copiedEventObject = $.extend({}, originalEventObject);

										// render/add the event on the calendar
										// the last 'true' argument determines if the event "sticks" (https://fullcalendar.io/docs/Calendar-addEvent)
										//calendar.addEvent(copiedEventObject, true);
										$("#calendar-tool").fullCalendar("renderEvent", copiedEventObject, true);

										// init input as empty
										$("#newCalendarEventInt").val("");

										// reload recent added widget
										melisHelper.zoneReload("id_melistoolcalendar_tool_recent_added", "melistoolcalendar_tool_recent_added");

										// notifications
										melisHelper.melisOkNotification(data.textTitle, data.textMessage);
										melisCore.flashMessenger();
									}
								}
								else {
									melisCoreTool.alertDanger("#siteaddalert", "", data.textMessage);
									melisHelper.melisKoNotification(data.textTitle, data.textMessage, data.errors);
								}
							}).fail(function() {
								alert( translations.tr_meliscore_error_message );
							});
					},
					eventDidMount: function(arg) {
						// Render calendar item event, update and Delete icon render to each calendar event
						$(arg.el).append( "<span class='event-icon'><i class='fa fa-pencil-square-o update-event' data-id='"+arg.event._id+"'></i><i class='fa fa-trash-o delete-event' data-id='"+arg.event._id+"'></i></span>" );
					},
					/* eventContent: function(args) {
						console.log(`eventContent args.event.title: `, args.event.title);
					}, */
					eventResize: function(event){
						// calendar event that trigger when event resized
						calendarTool.reschedEvent(event);
					},
					eventDrop: function (event){
						// calendar event that trigger where event drag and drop to another date
						calendarTool.reschedEvent(event);
					},
					datesSet: function(event) {
						// Go to specific date
						// This is trigger when updating Calendar Event
						calendarTool.gotoFCdate();
						// Re init fc_ready to ready/ready to maniplulate
						fc_ready = true;
					},
					loading: function( isLoading ) { 
						// Re init fc_ready to ready/ready to maniplulate
						fc_ready = true;

						// if you want to add a loading animation
						if ( !isLoading ) {
							var langLocale = getLocale();
								calendar.setOption('locale', langLocale);
								
							var dayTitleTimeout = setTimeout(function() {
								var $dayTitle 	= $(".fc-col-header-cell-cushion");
									if ( $dayTitle.length ) {
										$dayTitle.each(function(i, v) {
											var $this = $(v);
												text = $this.text().trim();
												if ( text.length > 3 ) {
													var newText = text.replace(/\./g, '');
														$this.text( newText );
												}
										});

										clearTimeout( dayTitleTimeout );
									}
							}, 1000);
						}					
					}
				});

				calendar.render();
			}

			function getLocale() {
				var $langCode = $("#id_meliscore_header_language .dropdown-menu li a span.active");
					return $langCode.attr("data-lang");
			}
}

// Local varial Year and Month
var fcal_year_client_event 	= null,
	fcal_month_client_event = null;

// calendar functionalities
var calendarTool = {
	// calendar initialization
	getCalendarData: function() {
		initCalendarTool();
	},
	// dragging the calendar event date covered
	reschedEvent: function(event) {
		// set variable from param
		var id 			= event.id,
	  		start 		= $.fullCalendar.formatDate(event.start, "yyyy-MM-dd"),
	  		end 		= $.fullCalendar.formatDate(event.end, "yyyy-MM-dd"),
	  		// prepairing dataString to post to server
			dataString 	= new Array();
		  
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
	deleteEvent: function(eventId) {
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
		    		$('#calendar-tool').fullCalendar('removeEvents',eventId);
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
	editEventTitle: function(eventId) {
		// initialize of local variable calendar id
		calVal_eventId = eventId;
		calVal_id = 'id_meliscalendar_tool_edit_event_modal';
		calVal_melisKey = 'meliscalendar_tool_edit_event_modal';
		modalUrl = '/melis/MelisCalendar/Calendar/renderCalendarModal';

		// requesitng to create modal and display after
    	melisHelper.createModal(calVal_id, calVal_melisKey, false, {cal_id: eventId}, modalUrl);
	},
	// saving calendar event
	saveEventTitle : function() {
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
					var eventData = $("#calendar-tool").fullCalendar('clientEvents', calVal_eventId)[0];
					// Generating event title
					var newTitleEvent = calVal_eventId +" : "+eventTitle;
					// Updating calendar event using FullCalendar method
					eventData.title = newTitleEvent;
					$('#calendar-tool').fullCalendar('updateEvent', eventData);
					
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
	gotoFCdate: function() {
		if ( fcal_year_client_event != null && fcal_month_client_event != null ) {
			//$('#calendar-tool').fullCalendar('gotoDate', fcal_year_client_event, fcal_month_client_event);
			calendar.gotoDate( fcal_year_client_event, fcal_month_client_event );

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
				$('.dashbaorad-calendar-tool').datepicker({ 
					format: "yyyy-mm-dd",
					inline: true,
					showOtherMonths:true,
					todayHighlight: true,
					toggleActive: true,
					// Rendering Calendar Event from Ajax to this Inline calendar
					beforeShowDay: function(date){
						var d 				= date,
							curr_date 		= ("0" + d.getDate()).slice(-2), // Formating in 2 digit number
							curr_month 		= ("0" + (d.getMonth() + 1)).slice(-2), // Months are zero based and Formating in 2 digit number
							curr_year 		= d.getFullYear(), // Fetching Year from date param
							formattedDate 	= curr_year + "-" + curr_month + "-" + curr_date;

							//Checking if Date exist of Calendar Tool Events
							if ( $.inArray(formattedDate, calendar_events) != -1 ) {
								return {
									classes: 'calendar-highlight calendar-event', // Highlight Date
								};
							}
						return;
					}
				}).on('changeDate', function(e){
					var formattedDate = e.format('yyyy-mm-dd');
						if ( $.inArray(formattedDate, calendar_events) != -1 ) {
							$("#id_meliscore_menu_toolstree li[data-tool-id='id_meliscalendar_tool']").trigger("click");

							if ( $("#id_meliscalendar_tool").length ) {
								// Init local variable year and month
								fcal_year_client_event = parseInt(e.format('yyyy'));
								fcal_month_client_event = (parseInt(e.format('m'))-1);
								
								if ( fc_ready ) {
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

$(function() {
	var $body = $("body");

		// init
		initDashboardCalendar();

		// bidning tool action
		$body.on("click", ".delete-event", function(e) { 
			var $this = $(this);

				// deleting event using event item delete button
				calendarTool.deleteEvent( $this.data('id') );
		});

		$body.on("click", ".update-event", function(e) { 
			var $this = $(this);

				// editing event title
				// this action will appear the modal for updating event title
				calendarTool.editEventTitle( $this.data('id') );
		});

		$body.on("click", ".btnSaveEventTitle", function(e) {
			var $this = $(this);

				// saving event title from modal
				calendarTool.saveEventTitle( $this.data('id') );
		});

		$body.on("keyup keypress", "#idformcalendar", function(e) {
			var keyCode = e.keyCode || e.which;

				if (keyCode === 13) { 
					e.preventDefault();
					return false;
				}
		});
});

// Responsive During Resize
$(window).on('resize',function(){
	responsiveCalendar();
});
