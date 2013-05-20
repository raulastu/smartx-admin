<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <style type="text/css">
      html{
        font-family:Arial, Helvetica, sans-serif;
        font-size:12px;
        height:100%;
      }
      body { height: 100%; margin: 0; padding: 0 }

            /*  start styles for the ContextMenu  */
      .context_menu{
        background-color:white;
        border:1px solid gray;
      }
      .context_menu_item{
        background-color:black;
        color:white;
        padding:3px 6px;
      }
      .context_menu_item:hover{
        background-color:rgb(68, 65, 70);
      }
      .context_menu_separator{
        background-color:gray;
        height:1px;
        margin:0;
        padding:0;
      }
      /*  end styles for the ContextMenu  */


      #map-canvas { height: 100% }

    </style>
    
    <script type="text/javascript"
      src="https://maps.googleapis.com/maps/api/js?libraries=drawing&key=AIzaSyAK2rfeeudxNw8JVF_9u3tk9xxXkOe7-Mc&sensor=false">
    </script>
    <script type="text/javascript" src="js/third/maplabel-compiled.js"></script>
    <script type="text/javascript" src="js/third/sprintf.min.js"></script>
    <script type="text/javascript" src="js/vendor/jquery-1.9.0.min.js"></script>
    <script type="text/javascript" src="js/third/sprintf.min.js"></script>
    <script type="text/javascript" src="js/third/ContextMenu.js"></script>
    
    
    <script type="text/javascript">

      var dbMarkers = [];

      var markerLocations = [];
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
        
        var mapOptions = {
          center: new google.maps.LatLng(-12.087583,-77.035103),
          zoom: 14,
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
              if($("#call>div>div>strong").html()=='call'){
                  $.ajax({ 
                    url: "users/start_ride",
                    type:'POST',
                    data:userId+"&"+lat+"&"+lng+"&"+address+"&"+reference,
                    success: function(data){
                        console.log("ride_id="+data);
                    }, dataType: "json"});
              }else{

              }

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
        var callTaxi = new MyControl('call', map);


        google.maps.event.addDomListener(callTaxi, 'click', function() {
            var markerMode=($("#call>div>div>strong").html()=='call')?'dontcall':'call';
            $("#call>div>div>strong").html(markerMode);
            // console.log(("#"+markerMode+">div>div>strong").html());
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
                var lat = (dbMarkers[data[i]["from"]].position.jb + dbMarkers[data[i]["to"]].position.jb)/2;
                var lng = (dbMarkers[data[i]["from"]].position.kb + dbMarkers[data[i]["to"]].position.kb)/2;
                console.log(lat);
                console.log(lng);
                
                var flightPlanCoordinates = [
                  new google.maps.LatLng(dbMarkers[data[i]["from"]].position.jb, dbMarkers[data[i]["from"]].position.kb),
                  new google.maps.LatLng(dbMarkers[data[i]["to"]].position.jb, dbMarkers[data[i]["to"]].position.kb)
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


          google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
            console.log(event);
            if (event.type == google.maps.drawing.OverlayType.MARKER) {
              
              var latlng = new Array(event.overlay.position.jb+"", event.overlay.position.kb+"");
              console.log("sending "+ latlng);
              if($("#tdriver>div>div>strong").html()=='tdriver'){
                $.ajax({
                  url: "tdrivers/create_tdriver",
                  context: document.body,
                  type:"POST",
                  data:"data="+JSON.stringify(latlng)
                }).done(function(data) {
                  console.log("successfully created");
                  console.log(data);
                  event.overlay.setTitle(data)
                  dbMarkers[data] = event.overlay;
                });
                google.maps.event.addListener(event.overlay, 'click', markerClickEvent);
              }else{
                $.ajax({
                  url: "users/create_user",
                  context: document.body,
                  type:"POST",
                  data:"data="+JSON.stringify(latlng)
                }).done(function(data) {
                  console.log("successfully created");
                  console.log(data);
                  event.overlay.setTitle(data)
                  event.overlay.setIcon(userMarkerIcon);
                  dbMarkers[data] = event.overlay;
                });
                google.maps.event.addListener(event.overlay, 'click', markerClickEvent);
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
        for (var i = 0;  i<data.length; i++) {
          var marker = new google.maps.Marker({
                position: new google.maps.LatLng(data[i].lat, data[i].lng),
                map:map,
                title:""+data[i].id
            });
          if(data[i].id.indexOf('u')==0){
           marker.setIcon(userMarkerIcon);
          }else{
            
          } 
          google.maps.event.addListener(marker, 'click', markerClickEvent);
                  //  display the ContextMenu on a Map right click
          google.maps.event.addListener(marker, 'rightclick', markerRightClickEvent);

          dbMarkers[data[i].id]=marker;
        };
        console.log(dbMarkers);
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
        if(this.title.indexOf('u')==0){
          
        }else{
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
      }

      function refreshPositions(data, map){
        if(data==null) return;
        for (var i = 0;  i<data.length; i++) {
          var fromLat = dbMarkers[data[i].id].position.jb;
          var fromLng = dbMarkers[data[i].id].position.kb;
          var toLat=data[i].lat;
          var toLng=data[i].lng;
          transition(data[i].id, fromLat, fromLng, toLat, toLng)
        };
        console.log(dbMarkers);
      }
      
      function transition(id, fromLat, fromLng, toLat, toLng){
          currentDelta[id] = 0;
          deltaLat[id] = (toLat - fromLat)/numDeltas;
          deltaLng[id] = (toLng - fromLng)/numDeltas;
          moveMarker(id);
      }
      var numDeltas=120;
      var deltaLat=[];
      var deltaLng=[];
      var currentDelta=[];
      var delay=10;

      function moveMarker(id){
        // id=50;
        // console.log(id+" "+currentDelta[id]);
        var newPosLat = dbMarkers[id].position.jb + deltaLat[id];
        var newPosLng = dbMarkers[id].position.kb + deltaLng[id];
        var latlng = new google.maps.LatLng(newPosLat, newPosLng);
        dbMarkers[id].setPosition(latlng);
        if(currentDelta[id]!=numDeltas){
            currentDelta[id]++;
            setTimeout(function(){
              moveMarker(id);
            }, delay);
        }
      }
      google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <body>
    <div id="map-canvas"/>
  </body>
</html>