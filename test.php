<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8' />
<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" />
<link href='./assets/css/fullcalendar.css' rel='stylesheet' />
<link href='./assets/css/fullcalendar.print.css' rel='stylesheet' media='print' />
<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>

<script src="http://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>


<script src='./assets/js/moment.min.js'></script>
<!--
<script src='assets/js/jquery.min.js'></script>
<script src='assets/js/jquery-ui.min.js'></script>-->
<script src='./assets/js/fullcalendar.min.js'></script>
<script>

	$(document).ready(function() {

		var zone = "05:30"; 

	$.ajax({
		url: './php/process.php',
        type: 'POST',
        data: 'type=fetch',
        async: false,
        success: function(s){
        	json_events = s;
        }
	});


	var currentMousePos = {
	    x: -1,
	    y: -1
	};
		jQuery(document).on("mousemove", function (event) {
        currentMousePos.x = event.pageX;
        currentMousePos.y = event.pageY;
    });

		/* initialize the external events
		-----------------------------------------------------------------*/

		$('#external-events .fc-event').each(function() {

			// store data so the calendar knows to render an event upon drop
			$(this).data('event', {
				title: $.trim($(this).text()), // use the element's text as the event title
				stick: true // maintain when user navigates (see docs on the renderEvent method)
			});

			// make the event draggable using jQuery UI
			$(this).draggable({
				zIndex: 999,
				revert: true,      // will cause the event to go back to its
				revertDuration: 0  //  original position after the drag
			});

		});


		/* initialize the calendar
		-----------------------------------------------------------------*/

		$('#calendar').fullCalendar({
			events: JSON.parse(json_events),
			utc: true,
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'agendaWeek,month,agendaDay'
			},
			editable: true,
			droppable: true, 
			selectable: true,
			height: 700,
			slotLabelFormat : 'H:mm',
			slotDuration : '00:15:00',
			minTime:'08:00:00',
			maxTime:'19:00:00',
			timeFormat: 'h:mm', 
			weekends: false,
			defaultView:'agendaWeek',
			navLinks: true, // can click day/week names to navigate views
			allDaySlot:false,
			selectConstraint:{
				start: '00:00', 
                end: '24:00', 
				
			},
			/*
			events: [{
			start: '13:00', 
            end: '14:00', 
			overlap: false,
			rendering: 'background',
			   color: '#ff9f89',
            dow: [1, 2,3, 4,5]
			}], */
				
			
			select: function(start, end) {
				var title = '';
				var eventData;

					eventData = {
						title: title,
						start: start,
						end: end,
						color: colors()

					};
					$('#calendar').fullCalendar('renderEvent', eventData, true);
				   
				$.ajax({
				  method: "POST",
				  url: "./php/process.php",
				  data: 'type=new&title='+title+'&startdate='+start.format("YYYY-MM-DD[T]HH:mm:SS")+'&end='+end.format()+'&color='+eventData.color+'&zone='+zone,
				  dataType: 'json',
				 
				})
				  .done(function( msg ) {
				    eventData.id = msg.eventid;
		    	   console.log( "Id: " + msg +" "+ msg.eventid+ " evid "+ eventData.id);
				  	
					$('#calendar').fullCalendar('updateEvent',eventData);
					console.log(eventData);
			

					//window.location.reload(true);
				  });

				$('#calendar').fullCalendar('unselect');
				$('#calendar').fullCalendar('updateEvent',eventData);
				//window.location.reload();
			}, /*
			eventReceive: function(event){
					var title = event.title;
				var start = event.start.format("YYYY-MM-DD[T]HH:mm:SS");
			
				$.ajax({
		    		url: './php/process.php',
		    		data: 'type=new&title='+title+'&startdate='+start+'&zone='+zone,
		    		type: 'POST',
		    		dataType: 'json',
		    		success: function(response){
		    			event.id = response.eventid;
		    			$('#calendar').fullCalendar('updateEvent',event);
		    		},
		    		error: function(e){
		    			console.log(e.responseText);

		    		}
		    	});
		    		$('#calendar').fullCalendar('updateEvent',event);
				console.log(event);
		
			}, */ 
			eventDrop: function(event, delta, revertFunc) {
		        var title = event.title;
		        var start = event.start.format();
		       // var end = (event.end == null) ? start : event.end.format();
		        var end = event.end.format();
		        $.ajax({
					url: './php/process.php',
					data: 'type=resetdate&title='+title+'&start='+start+'&end='+end+'&eventid='+event.id,
					type: 'POST',
					dataType: 'json',
					success: function(response){
						if(response.status != 'success')		    				
						revertFunc();
					},
					error: function(e){		    			
						revertFunc();
						alert('Error processing your request: '+e.responseText);
					}
				});
		    }, 
			/*
			select: function(start, end){
			var creneau;
				creneau = {
					id: 'creneau',
				    start: start,
					end: end,
					rendering: 'inverse-background',
					color: 'rgba(255, 159, 137, 0.76)'
				};
				$('#calendar').fullCalendar('renderEvent', creneau, true);
				$('#calendar').fullCalendar('unselect');
			},
			//*/
			eventClick: function (event, jsEvent) {
			console.log(event.id);
            $("#startTime").html(moment(event.start).format('h:mm, Do MMMM'));
            $("#endTime").html(moment(event.end).format('h:mm, Do MMMM'));
            //$("#eventInfo").html(event.description);//auteur
            $("#eventContent").dialog(
            	{ 
            	  modal: true,
            	  title: event.title,
                  buttons: [
				    { 
				        text: 'Editer',
				        click: function() {
				        	console.log(event.id);
		      
		  	        	 $('#edit').show();	
		  	        	 	$("#btn").click(function (){
           					event.title = document.getElementById("title").value;
           					document.getElementById("title").value="";
              		        console.log('type=changetitle&title='+event.title+'&eventid='+event.id);
		              		
		              	$.ajax({
				    		url: './php/process.php',
				    		data: 'type=changetitle&title='+event.title+'&eventid='+event.id,
				    		type: 'POST',
				    		dataType: 'json',
				    		success: function(response){	
				    			if(response.status == 'success')			    			
		              			$('#calendar').fullCalendar('updateEvent',event);
		              			//$('#calendar').fullCalendar('renderEvent', event, true);
		              		},
				    		error: function(e){
				    			alert('Error processing your request: '+e.responseText);
				    		}
				    	});
          					$('#edit').hide();	
		  	        		$("#eventContent").dialog('close');
							$("#btn").off("click");
							});
		  	        	 //}
				        },
				    },
				    {   
				        text: 'Supprimer',
				        click: function() { 
				        	$('#calendar').fullCalendar('removeEvents', event.id);
				        	$.ajax({
				    		url: './php/process.php',
				    		data: 'type=remove&eventid='+event.id,
				    		type: 'POST',
				    		dataType: 'json',
				    		success: function(response){
				    			console.log(response);
				    			if(response.status == 'success'){
				    				$('#calendar').fullCalendar('removeEvents');
            						getFreshEvents();
            					}
				    		},
				    		error: function(e){	
				    			alert('Error processing your request: '+e.responseText);
				    		}
			    		});
				        	$(this).dialog('close');
				        	},
				    },
				  ],
            	
            	width:270});
       // });
    },
   
			eventResize: function(event, delta, revertFunc) {
				console.log(event);
				var title = event.title;
				var end = event.end.format();
				var start = event.start.format();
		        $.ajax({
					url: './php/process.php',
					data: 'type=resetdate&title='+title+'&start='+start+'&end='+end+'&eventid='+event.id,
					type: 'POST',
					dataType: 'json',
					success: function(response){
						if(response.status != 'success')		    				
						revertFunc();
					},
					error: function(e){		    			
						revertFunc();
						alert('Error processing your request: '+e.responseText);
					}
				});
		    },
			
		});

	function getFreshEvents(){
		$.ajax({
			url: './php/process.php',
	        type: 'POST', // Send post data
	        data: 'type=fetch',
	        async: false,
	        success: function(s){
	        	freshevents = s;
	        }
		});
		$('#calendar').fullCalendar('addEventSource', JSON.parse(freshevents));
	}



function colors(){
var colors =['aquamarine', 'moccasin', 'yellowGreen', 'coral', 'mediumOrchid', 'lightSeaGreen','navy'];
var color = colors[Math.floor(Math.random() * colors.length)];
	return color;
};       		

$("#cmode").on("click",function (){
 console.log('on smode');

var calendar = $('#calendar').fullCalendar('getCalendar');
  calendar.off('select');
  
 var creneau;
	
calendar.on('select', function (start, end) {
	//console.log('st ' + start.format());
     			creneau = {
					id: 'creneau',
				    start: start,
					end: end,
					overlap: true,
					rendering: 'inverse-background',
					color: 'rgba(255, 159, 137, 0.76)'
				};

				$('#calendar').fullCalendar('renderEvent', creneau, true);
				$('#calendar').fullCalendar('unselect');
	
		    });
  
});


$("#emode").on("click",function (){
 
$('#calendar').fullCalendar('select', function(start, end) {
				var title = 'Coucou';
				var eventData;
					if (title) {
					eventData = {
						title: title,
						start: start,
						end: end,
						color: colors()
					};
					$('#calendar').fullCalendar('renderEvent', eventData, true); // stick? = true
					//$('#calendar').fullCalendar('updateEvent',eventData);		  			
				}
				$.ajax({
				  method: "POST",
				  url: "./php/process.php",
				  data: 'type=new&title='+title+'&startdate='+start.format("YYYY-MM-DD[T]HH:mm:SS")+'&enddate='+end.format("YYYY-MM-DD[T]HH:mm:SS")+'&color='+'#449016'+'&zone='+zone,
				  dataType: 'json',				 
				})
				  .done(function( msg ) {
				    console.log( "Data Saved: " + msg +" "+ msg.eventid+ "ev "+ eventData.id);
				    eventData.id = msg.eventid;
		    	   console.log( "Id: " + msg +" "+ msg.eventid+ "ev "+ eventData.id);
					$('#calendar').fullCalendar('updateEvent',eventData);
					//window.location.reload(true);
				  });
				$('#calendar').fullCalendar('unselect');
				$('#calendar').fullCalendar('updateEvent',eventData);
				//window.location.reload();
			});


});


});

</script>
<style>

	body {
		margin-top: 40px;
		text-align: center;
		font-size: 14px;
		font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
	}

	#trash{
		width:32px;
		height:32px;
		float:left;
		padding-bottom: 15px;
		position: relative;
	}
		
	#wrap {
		width: 1100px;
		margin: 0 auto;
	}
		
	#external-events {
		float: left;
		width: 150px;
		padding: 0 10px;
		border: 1px solid #ccc;
		background: #eee;
		text-align: left;
	}
		
	#external-events h4 {
		font-size: 16px;
		margin-top: 0;
		padding-top: 1em;
	}
		
	#external-events .fc-event {
		margin: 10px 0;
		cursor: pointer;
	}
		
	#external-events p {
		margin: 1.5em 0;
		font-size: 11px;
		color: #666;
	}
		
	#external-events p input {
		margin: 0;
		vertical-align: middle;
	}

	#calendar {
		float: right;
		width: 900px;
	}

</style>
</head>
<body>
	<div id='wrap'>
<!--
		<div id='external-events'>
			<h4>Draggable Events</h4>
			<div class='fc-event'>New Event</div>
			<p>
				<img src="assets/img/trashcan.png" id="trash" alt="">
			</p>
		</div> -->
		<button id='smode'>Selection zone mode</button>
		<button id='emode'>Selection event mode</button>
		
		<div id='calendar'></div>
		<div id="eventContent" title="Detaills d'evenement" style="display:none;">
	    Commence à <span id="startTime"></span><br>
	    Fini à <span id="endTime"></span><br><br>
	    <p id="eventInfo"></p>
	    <form id="edit"  style="display: none;">
		Title : <input type="text" id="title"><br>
		<!--: <input type="text" name="" value=""><br>-->
		<input type="button" id='btn' value="OK">
		</form>
	 	</div>

		<div style='clear:both'></div>

	</div>
</body>
</html>
