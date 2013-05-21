

      var dbMarkers = [];
      var ridesStore = [];

      var markerLatLng = [];
      var loadedTDrivers = [];

      var selectedMarker;
      var markerMode='tdriver';
      var userMarkerIcon = "http://westminster.boskalis.com/fileadmin/custom/images/marker_icon3.png";
      var selectedId;

      function MyControl(controlName, map) {
        var controlDiv = document.createElement("div");

        // Set CSS styles for the DIV containing the control
        // Setting padding to 5 px will offset the control
        // from the edge of the map.
        controlDiv.style.padding = '5px';
        controlDiv.id=controlName;

        // Set CSS for the control border.
        var controlUI = document.createElement('div');
        controlUI.style.backgroundColor = 'white';
        controlUI.style.borderStyle = 'solid';
        controlUI.style.borderWidth = '2px';
        controlUI.style.cursor = 'pointer';
        controlUI.style.textAlign = 'center';
        controlUI.title = 'Click to set the map to Home';
        controlDiv.appendChild(controlUI);

        // Set CSS for the control interior.
        var controlText = document.createElement('div');
        controlText.style.fontFamily = 'Arial,sans-serif';
        controlText.style.fontSize = '12px';
        controlText.style.paddingLeft = '4px';
        controlText.style.paddingRight = '4px';
        controlText.innerHTML = '<strong>'+controlName+'</strong>';
        controlUI.appendChild(controlText);

        controlDiv.index = 1;
        map.controls[google.maps.ControlPosition.TOP_RIGHT].push(controlDiv);
        return controlDiv;
      }

      function getPrettyDistance(distance) {
        if (distance > 1) {
          return sprintf("%.2f km", distance);
        } else {
          return sprintf("%.1f m", distance * 1000);
        }
      }

      var contextMenuOptions={};
      var menuItems=[];
      menuItems.push({className:'context_menu_item', eventName:'call_taxi', label:'Call Taxi'});
      menuItems.push({className:'context_menu_item', eventName:'zoom_out_click', label:'Zoom out'});
      contextMenuOptions.menuItems=menuItems;
      var contextMenu;

      function initialize() {
        // google.maps.visualRefresh=true;
        var mapOptions = {
          center: new google.maps.LatLng(-12.087583,-77.035103),
          zoom: 13,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById("map-canvas"),
            mapOptions);


        contextMenu = new ContextMenu(map, contextMenuOptions);

        google.maps.event.addListener(contextMenu, 'menu_item_selected', function(latLng, eventName){
        //  latLng is the position of the ContextMenu
        //  eventName is the eventName defined for the clicked ContextMenuItem in the ContextMenuOptions
        switch(eventName){
          case 'call_taxi':
              
              var marker = dbMarkers[selectedId];

              var userId = "u="+marker.title.substring(1);
              var lat = "la="+marker.position.jb;
              var lng = "ln="+marker.position.kb;
              var address = 'a=Some Address';
              var reference='r=reference';
              $.ajax({ 
                    url: "users/start_ride",
                    type:'POST',
                    data:userId+"&"+lat+"&"+lng+"&"+address+"&"+reference,
                    success: function(data){
                      console.log("users/start_ride");
                      console.log(data);
                    }, dataType: "json"});

            break;
          case 'zoom_out_click':
            map.setZoom(map.getZoom()-1);
            break;
        }
      });


        console.log(contextMenu);
        // var drawingManager = new google.maps.drawing.DrawingManager();
        var drawingManager = new google.maps.drawing.DrawingManager({
          // drawingMode: google.maps.drawing.OverlayType.MARKER,
          drawingControl: true,
          drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_LEFT,
            drawingModes: [
              google.maps.drawing.OverlayType.MARKER,
              google.maps.drawing.OverlayType.CIRCLE,
              google.maps.drawing.OverlayType.POLYGON,
              google.maps.drawing.OverlayType.POLYLINE,
              google.maps.drawing.OverlayType.RECTANGLE
            ]
          },
          circleOptions: {
            fillColor: '#ffff00',
            fillOpacity: 1,
            strokeWeight: 5,
            clickable: false,
            zIndex: 1,
            editable: true
          }
        });

        



        var clearTDriversControl = new MyControl('clear tdrivers', map);
        var clearUsersControl = new MyControl('clear users', map);
        var closestMarkersControl = new MyControl('get closest', map);
        var tdriver_usertoggle = new MyControl('tdriver', map);
        var clearRideRequestsControll = new MyControl('clear-ride-requests', map);


        google.maps.event.addDomListener(clearRideRequestsControll, 'click', function() {
            $.ajax({
              url: "users/clear_rides_requests",
              context: document.body
            }).done(function(data) {
              console.log('users/clear_rides_requests');
              console.log(data);
            });
        });

        google.maps.event.addDomListener(tdriver_usertoggle, 'click', function() {
          var ob= $("#tdriver>div>div>strong");
          var markerMode=(ob.html()=='tdriver')?'passenger':'tdriver';
          ob.html(markerMode);
            // console.log(("#"+markerMode+">div>div>strong").html());
        });
        google.maps.event.addDomListener(clearTDriversControl, 'click', function() {
            $.ajax({
              url: "tdrivers/clear_locations",
              context: document.body
            }).done(function() {
              console.log("successfully cleared");
            });
        });
        google.maps.event.addDomListener(clearUsersControl, 'click', function() {
            $.ajax({
              url: "users/clear_locations",
              context: document.body
            }).done(function(data) {
              console.log(data+" successfully cleared");
            });
        });
        

        google.maps.event.addDomListener(closestMarkersControl, 'click', function() {
            $.ajax({
              url: "tdrivers/get_closest",
              context: document.body,
              type:"POST",
              data:"selectedPoint="+JSON.stringify({
                'selected_id':selectedMarker.title,
                'lat':selectedMarker.position.jb,
                'lng':selectedMarker.position.kb
              })
            }).done(function(data) {
              console.log(data);
              console.log(data.length);
              for (var i = 0; i < data.length; i++) {
                var radius=data[i]['distance']/2;
                console.log('distance = '+data[i]['distance']);
                console.log('radius = '+data[i]['distance']/2);
                var toMarker = dbMarkers['d'+data[i]["to"]];
                // var fromMarker = dbMarkers['d'+data[i]["to"]];
                var lat = (dbMarkers[data[i]["from"]].position.jb + toMarker.position.jb)/2;
                var lng = (dbMarkers[data[i]["from"]].position.kb + toMarker.position.kb)/2;
                console.log(lat);
                console.log(lng);
                
                var flightPlanCoordinates = [
                  new google.maps.LatLng(dbMarkers[data[i]["from"]].position.jb, dbMarkers[data[i]["from"]].position.kb),
                  new google.maps.LatLng(toMarker.position.jb, toMarker.position.kb)
                ];
                var flightPath = new google.maps.Polyline({
                  path: flightPlanCoordinates,
                  strokeColor: '#FF0000',
                  strokeOpacity: 1.0,
                  strokeWeight: 2,
                  map:map
                });
                var mapLabel = new MapLabel({
                   text: getPrettyDistance(data[i]['distance']),
                   position: new google.maps.LatLng(lat,lng),
                   map: map,
                   fontSize: 16,
                   align: 'center'
                 });
              };
              console.log("successfully sent");
            });
        });

          //CREATE MARKER
          google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
            console.log(event);
            if (event.type == google.maps.drawing.OverlayType.MARKER) {
              
              
              var lat = event.overlay.position.jb;
              var lng = event.overlay.position.kb;
              var latlng = [lat, lng];
              console.log("sending "+ latlng);
              if($("#tdriver>div>div>strong").html()=='tdriver'){
                $.ajax({
                  url: "tdrivers/create_tdriver",
                  context: document.body,
                  type:"POST",
                  data:"data="+JSON.stringify(latlng)
                }).done(function(returnedId) {
                  console.log("tdriver created");
                  console.log(returnedId);
                  event.overlay.setTitle(returnedId)
                  dbMarkers['d'+returnedId] = event.overlay;
                  loadedTDrivers[returnedId]=[];
                  loadedTDrivers[returnedId]['lat']=lat;
                  loadedTDrivers[returnedId]['lng']=lng;
                });
                google.maps.event.addListener(event.overlay, 'click', markerClickEvent);
              }else{
                $.ajax({
                  url: "users/create_user",
                  context: document.body,
                  type:"POST",
                  data:"data="+JSON.stringify(latlng)
                }).done(function(data) {
                  console.log("user created");
                  console.log(data);
                  event.overlay.setTitle(data)
                  event.overlay.setIcon(userMarkerIcon);
                  dbMarkers[data] = event.overlay;
                });
	          	google.maps.event.addListener(event.overlay, 'click', markerClickEvent);
		          google.maps.event.addListener(event.overlay, 'rightclick', markerRightClickEvent);
              }
            }
          });

          // (function poll(){
          //     $.ajax({ 
          //       url: "tdrivers/locations",
          //      success: function(data){
          //           loadData(data,map);
          //     }, dataType: "json", complete: poll, timeout: 4000 });
          // })();

          

          $.ajax({
            url: "users/everyone_locations",
            context: this,
            data:"ola"

          }).done(function(data) {
            console.log(data);
            loadData(data, map);

            (function poll(){
               setTimeout(function(){
                  $.ajax({ 
                    url: "users/everyone_locations",
                   success: function(data){
                    refreshPositions(data, map);
                    poll();
                  }, dataType: "json"});
              }, 2000);
            })();

          });
        
     

        drawingManager.setMap(map);
      }

       var loadData = function(data, map){
        if(data==null) return;
        console.log(data);
        var users = data.users;
        var tdrivers = data.tdrivers;
        
        for (var i = 0;  i<users.length; i++) {
        	var uid='u'+users[i].id;
          var marker = new google.maps.Marker({
                position: new google.maps.LatLng(users[i].lat, users[i].lng),
                map:map,
                icon:userMarkerIcon,
                title:uid
            });
          google.maps.event.addListener(marker, 'click', markerClickEvent);
          google.maps.event.addListener(marker, 'rightclick', markerRightClickEvent);
          dbMarkers[uid]=marker;

          // markerLatLng[uid]=[];
          // markerLatLng[uid]['lat']=
        };
        loadedTDrivers = [];
        for (var i = 0;  i<tdrivers.length; i++) {
        	var did='d'+tdrivers[i].id;
          var marker = new google.maps.Marker({
    				position: new google.maps.LatLng(tdrivers[i].lat, tdrivers[i].lng),
    				map:map,
    				title:did
    			});
    			google.maps.event.addListener(marker, 'click', markerClickEvent);
    			google.maps.event.addListener(marker, 'rightclick', markerRightClickEvent);
    			dbMarkers[did]=marker;
          loadedTDrivers[tdrivers[i].id]=tdrivers[i];
        };


        var rides = data.rides;
        // if(rides!=null){}
        for(var i = 0 ; i<rides.length; i++){
          var tdriverLat=dbMarkers['d'+rides[i].tdriver_id].position.jb;
          var tdriverLng=dbMarkers['d'+rides[i].tdriver_id].position.kb;
          var userLat=dbMarkers['u'+rides[i].user_id].position.jb;
          var userLng=dbMarkers['u'+rides[i].user_id].position.kb;
          var flightPlanCoordinates = [
                new google.maps.LatLng(userLat, userLng),
                new google.maps.LatLng(tdriverLat, tdriverLng)
              ];
          var flightPath = new google.maps.Polyline({
            path: flightPlanCoordinates,
            strokeColor: 'indigo',
            strokeOpacity: 1.0,
            strokeWeight: 2,
            map:map
          });

          if(ridesStore['d'+rides[i].tdriver_id]==null)
          	ridesStore['d'+rides[i].tdriver_id]=[];
          ridesStore['d'+rides[i].tdriver_id]['u'+rides[i].user_id]=flightPath;
          console.log(ridesStore);
          console.log(flightPath.getPath());
        }
        // console.log(dbMarkers);
      }

      var markerRightClickEvent = function(event){
        console.log(event);
        console.log(this);
        selectedId = this.title;
        console.log(selectedId);
        contextMenu.show(event.latLng);
      }

      var markerClickEvent = function(event) {
        console.log(this);
        console.log(event);
        if(selectedMarker!=null){
          selectedMarker.setIcon(null);
          selectedMarker.setAnimation(null);

          selectedMarker=this;
        }else{
          selectedMarker=this;
        }

        this.setIcon("http://s7.postimg.org/wg6bu3jpj/pointer.png");
        this.setAnimation(google.maps.Animation.BOUNCE);
      }

      function refreshPositions(data, map){
        if(data==null) return;
        console.log(data);
        var newRides = data.rides;

        var arrayStoreTemp=[];
		    var newcreated=0;
        for(var i=0; i<newRides.length; i++){
        	var did='d'+newRides[i].tdriver_id;
        	var uid='u'+newRides[i].user_id;

        	if(ridesStore[did]!=null){
        		if(ridesStore[did][uid]!=null){
        			if(arrayStoreTemp[did]==null)
						  arrayStoreTemp[did]=[];
        			arrayStoreTemp[did][uid]=ridesStore[did][uid];
        			delete ridesStore[did][uid];
        		}else{
        			//create new
        			var tdriverLat=dbMarkers[did].position.jb;
    					var tdriverLng=dbMarkers[did].position.kb;
    					var userLat=dbMarkers[uid].position.jb;
    					var userLng=dbMarkers[uid].position.kb;
		         	var flightPlanCoordinates = [
		                new google.maps.LatLng(userLat, userLng),
		                new google.maps.LatLng(tdriverLat, tdriverLng)
		              ];
		          	var flightPath = new google.maps.Polyline({
			            path: flightPlanCoordinates,
			            strokeColor: 'indigo',
			            strokeOpacity: 1.0,
			            strokeWeight: 2,
			            map:map
			        });
			        arrayStoreTemp[did][uid]=flightPath;
			        newcreated++;
        		}
        	}else{
    				var tdriverLat=dbMarkers[did].position.jb;
    				var tdriverLng=dbMarkers[did].position.kb;
    				var userLat=dbMarkers[uid].position.jb;
    				var userLng=dbMarkers[uid].position.kb;
	         	var flightPlanCoordinates = [
	                new google.maps.LatLng(userLat, userLng),
	                new google.maps.LatLng(tdriverLat, tdriverLng)
	              ];
	          	var flightPath = new google.maps.Polyline({
		            path: flightPlanCoordinates,
		            strokeColor: 'indigo',
		            strokeOpacity: 1.0,
		            strokeWeight: 2,
		            map:map
		        });

  				if(arrayStoreTemp[did]==null)
  					arrayStoreTemp[did]=[];
  				arrayStoreTemp[did][uid]=flightPath;
  				newcreated++;
        	}
        }
        for(var did in ridesStore){
          for(var uid in ridesStore[did]){
            ridesStore[did][uid].setMap(null);
            console.log(ridesStore[did][uid]);
          }
        }
        console.log("newcreated "+newcreated);
        console.log(arrayStoreTemp);
        ridesStore=arrayStoreTemp;

        var tdrivers = data.tdrivers;
        // if(tdrivers)
        for (var i = 0;  i<tdrivers.length; i++) {
        	   var uiId='d'+tdrivers[i].id;
	          var fromLat = loadedTDrivers[tdrivers[i].id].lat;
	          var fromLng = loadedTDrivers[tdrivers[i].id].lng;
	          var toLat=tdrivers[i].lat;
	          var toLng=tdrivers[i].lng;

            if(fromLat!=toLat || fromLng!=toLng){
              console.log("changed markers");
              loadedTDrivers[tdrivers[i].id].lat=toLat;
              loadedTDrivers[tdrivers[i].id].lng=toLng;
              // console.log(parseFloat(fromLat)+" "+parseFloat(fromLng));
              // console.log((toLat)+" "+(toLng));
              transition(uiId, fromLat, fromLng, toLat, toLng);
            }
	           
        };
      }
      
      function transition(id, fromLat, fromLng, toLat, toLng){
          currentDelta[id] = 0;
          deltaLat[id] = (toLat - fromLat)/numDeltas;
          deltaLng[id] = (toLng - fromLng)/numDeltas;
          moveMarker(id);
      }
      var numDeltas=40;
      var deltaLat=[];
      var deltaLng=[];
      var currentDelta=[];
      var delay=40;

      function moveMarker(id){
        // id=50;
        // console.log(id+" "+currentDelta[id]);
        var newPosLat = dbMarkers[id].position.jb + deltaLat[id];
        var newPosLng = dbMarkers[id].position.kb + deltaLng[id];
        var latlng = new google.maps.LatLng(newPosLat, newPosLng);

        dbMarkers[id].setPosition(latlng);
        

        if(ridesStore[id]!=null){
        	// console.log(1);
	        for(var uid in ridesStore[id]){
	        	var a = [ridesStore[id][uid].getPath().getAt(0),latlng];
	        	ridesStore[id][uid].setPath(a);
	        }	
        }
        

        if(currentDelta[id]!=numDeltas){
            currentDelta[id]++;
            setTimeout(function(){
              moveMarker(id);
            }, delay);
        }
      }
      google.maps.event.addDomListener(window, 'load', initialize);