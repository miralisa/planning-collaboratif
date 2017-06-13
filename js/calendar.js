
	$(document).ready(function() {

		var zone = "05:30"; 
                var mode=1;
                var idPS = getUrlVars()['idPS'];
                console.log("idPS = "+idPS);

	$.ajax({
		url: '../php/process.php',
                type: 'POST',
                data: 'type=fetch&idPS='+idPS,
                async: false,
                success: function(s){
                        json_events = s;
                       // console.log(s);
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
			height: 500,
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
                        if(mode==1){
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
				  url: "../php/process.php",
				  data: 'type=newe&title='+title+'&startdate='+start.format()+'&end='+end.format()+'&idPS='+idPS,
				  dataType: 'json',
                                  error: function(s){
                                          alert("Veuillez créer un évènement à l'intérieur d'un créneau autorisé.");
                                  }
				 
				})
				  .done(function( msg ) {
				    eventData.id = msg.eventid;
		    	   console.log( "Id: " + msg +" "+ msg.eventid+ " evid "+ eventData.id+"bonjour = "+msg.bonjour);
                                
					$('#calendar').fullCalendar('updateEvent',eventData);
					console.log(eventData);
                                        

					//window.location.reload(true);
				  });

				//$('#calendar').fullCalendar('unselect');
				//$('#calendar').fullCalendar('updateEvent',eventData);
                                $('#calendar').fullCalendar('removeEvents');
            			getFreshEvents();
			
				//window.location.reload();
                                } else if(mode == 0) {
                                        var creneau = {
							start: start,
							end: end,
							rendering: 'background',
							color: 'rgba(137, 255, 184, 0.8)'
						};

						$('#calendar').fullCalendar('renderEvent', creneau, true);
				   
						$.ajax({
						  method: "POST",
						  url: "../php/process.php",
						  data: 'type=newc&startdate='+start.format()+'&end='+end.format()+'&idPS='+idPS,
						  dataType: 'json',
						 
						})
						  .done(function( msg ) {
						    creneau.id = msg.creneauid;
                                                    console.log( "Id: " + msg +" "+ msg.creneauid+ " evid "+ creneau.id+", bonjour : "+msg.bonjour);
						  	
							$('#calendar').fullCalendar('updateEvent',creneau);
							console.log(creneau);
					

							//window.location.reload(true);
						  });

						$('#calendar').fullCalendar('unselect');
						$('#calendar').fullCalendar('updateEvent',creneau);
                                
                                }
                                else if(mode == 2){
                                        console.log("Chuis dans le mode 2, debut :"+start.format()+", fin :"+end.format());
                                     var creneau = {
							start: start,
							end: end
						};
                                         $.ajax({
						  method: "POST",
						  url: "../php/process.php",
						  data: 'type=suppc&startdate='+start.format()+'&end='+end.format()+'&idPS='+idPS,
						  dataType: 'json',
						 
						});
									  
                                                $('#calendar').fullCalendar('removeEvents');
                                                getFreshEvents();
                         }       
			}, /*
			eventReceive: function(event){
					var title = event.title;
				var start = event.start.format("YYYY-MM-DD[T]HH:mm:SS");
			
				$.ajax({
		    		url: '../php/process.php',
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
					url: '../php/process.php',
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
				    		url: '../php/process.php',
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
				    		url: '../php/process.php',
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
                                    {
                                            text: 'Discussion',
                                            click: function(){
                                                    $("#envoi").off("click");
                                                    $("#envoi").on("click", function (){
                                                            var message = encodeURIComponent( $('#message').val() );
                                                            var d = moment().format();
                                                            console.log(d);
                                                            if(message != ""){
                                                                 $.ajax({
                                                                        url: '../php/process.php',
                                                                        data: 'type=envoi&eventid='+event.id+'&contenu='+message+'&date='+d,
                                                                        type: 'POST',
                                                                        dataType: 'json'
                                                                }).done(function(){
                                                                         console.log('message sent');
                                                                         var lastID = $('#messages p:last').attr('id');
                                                                         if(lastID == null)
                                                                                 lastID = 0;
                                                                         console.log("lastID = "+lastID+" et eventid = "+event.id );
                                                                         $.ajax({
                                                                                url: '../php/process.php',
                                                                                data: 'type=charger&eventid='+event.id+'&lastmessage='+lastID,
                                                                                type: 'POST',
                                                                                dataType: 'json',
                                                                                success: function(response){
                                                                                        console.log("Reçu requête charger : "+response);
                                                                                        if(response.status == 'success')
                                                                                                $("#messages").append(response.messages);
                                                                                },
                                                                                error: function(e){
                                                                                        console.log("Error during refreshment :"+e.responseText);
                                                                                }
                                                                                 
                                                                              });
                                                                      });
                                                                }
                                                           });
                                                            $(this).dialog('close');
                                                            $("#messages").empty();
                                                            $.ajax({
                                                                        url: '../php/process.php',
                                                                        data: 'type=fetchMessage&eventid='+event.id,
                                                                        type: 'POST',
                                                                        dataType: 'json'
                                                                }).done(function(msg){
                                                                        console.log("retrieving last messages with event id :"+event.id);
                                                                        console.log(msg.messages);
                                                                        if(msg.messages != "")
                                                                            $("#messages").append(msg.messages);
                                                                      });
                                                            
                                                            $("#Discussion").dialog(
                                                                { 
                                                                  modal: true,
                                                                  
                                                                  });
                                                            
                                                    }
                                    },
				  ],
            	
            	width:380});
       // });
    },
   
			eventResize: function(event, delta, revertFunc) {
				console.log(event);
				var title = event.title;
				var end = event.end.format();
				var start = event.start.format();
		        $.ajax({
					url: '../php/process.php',
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
			url: '../php/process.php',
	        type: 'POST', // Send post data
	        data: 'type=fetch&idPS='+idPS,
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

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}

$("#cmode").on("click",function (){
        console.log('on cmode');
        mode=0;

});

$("#emode").on("click",function (){
        console.log('on emode');
        mode=1;

});

$("#smode").on("click",function (){
        console.log('on smode');
        mode=2;

});

$("#refresh").on("click",function (){
        console.log('refreshing');
        $('#calendar').fullCalendar('removeEvents');
        getFreshEvents();

});




});
