<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #map-canvas { height: 100% }
    </style>
    
    <script type="text/javascript"
      src="https://maps.googleapis.com/maps/api/js?libraries=drawing&key=AIzaSyAK2rfeeudxNw8JVF_9u3tk9xxXkOe7-Mc&sensor=true">
    </script>
    <script type="text/javascript" src="js/third/maplabel-compiled.js"></script>
    <script type="text/javascript" src="js/third/sprintf.min.js"></script>
    <script type="text/javascript" src="js/vendor/jquery-1.9.0.min.js"></script>
    
    
    <script type="text/javascript">
      var dbMarkers = [];
      var markerLocations = [];
      var selectedMarker;

      function MyControl(controlName, map) {
        var controlDiv = document.createElement("div");

        // Set CSS styles for the DIV containing the control
        // Setting padding to 5 px will offset the control
        // from the edge of the map.
        controlDiv.style.padding = '5px';

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

      function initialize() {
        var mapOptions = {
          center: new google.maps.LatLng(-12.087583,-77.035103),
          zoom: 14,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map(document.getElementById("map-canvas"),
            mapOptions);
        var drawingManager = new google.maps.drawing.DrawingManager();
        // var drawingManager = new google.maps.drawing.DrawingManager({
        //   drawingMode: google.maps.drawing.OverlayType.MARKER,
        //   drawingControl: true,
        //   drawingControlOptions: {
        //     position: google.maps.ControlPosition.TOP_CENTER,
        //     drawingModes: [
        //       google.maps.drawing.OverlayType.MARKER,
        //       google.maps.drawing.OverlayType.CIRCLE,
        //       google.maps.drawing.OverlayType.POLYGON,
        //       google.maps.drawing.OverlayType.POLYLINE,
        //       google.maps.drawing.OverlayType.RECTANGLE
        //     ]
        //   },
        //   markerOptions: {
        //     icon: 'http://www.example.com/icon.png'
        //   },
        //   circleOptions: {
        //     fillColor: '#ffff00',
        //     fillOpacity: 1,
        //     strokeWeight: 5,
        //     clickable: false,
        //     zIndex: 1,
        //     editable: true
        //   }
        // });
        // drawingManager.setOptions({
        //   drawingControlOptions: {
        //     position: google.maps.ControlPosition.BOTTOM_LEFT,
        //     drawingModes: [google.maps.drawing.OverlayType.MARKER]
        //   }
        // });
          var clearMarkersControl = new MyControl('clear', map);
          var closestMarkersControl = new MyControl('get closest', map);

          google.maps.event.addDomListener(clearMarkersControl, 'click', function() {
              $.ajax({
                url: "tdrivers/clear_locations",
                context: document.body
              }).done(function() {
                console.log("successfully cleared");
              });
          });

          google.maps.event.addDomListener(closestMarkersControl, 'click', function() {
              $.ajax({
                url: "tdrivers/get_closest",
                context: document.body,
                type:"POST",
                data:"selectedPoint="+JSON.stringify({
                  'tdriver_id':selectedMarker.title,
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
                  console.log('angle='+data[i]['angle']);
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
                  // flightPath.setMap(map);

                  // var marker = new google.maps.Marker({
                  //     position: new google.maps.LatLng(lat, lng),
                  //     map:map,
                  //     title:""+data[i]['distance']
                  // });
                };
                console.log("successfully sent");
              });
          });


          google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
            console.log(event);
            if (event.type == google.maps.drawing.OverlayType.MARKER) {

              $.ajax({
                url: "tdrivers/create_tdriver",
                context: document.body,
                type:"POST",
                data:"data="+JSON.stringify(new Array(event.overlay.position.jb, event.overlay.position.kb))
              }).done(function(data) {
                console.log("successfully created");
                console.log(data);
                event.overlay.setTitle(data)
                dbMarkers[data] = event.overlay;
              });
              google.maps.event.addListener(event.overlay, 'click', markerClickEvent);
            }
          });

          $.ajax({
            url: "tdrivers/locations",
            context: document.body,
            data:"ola" 
          }).done(function(data) {
            
            if(data==null) return;
            for (var i = 0;  i<data.length; i++) {
              var marker = new google.maps.Marker({
                  position: new google.maps.LatLng(data[i].latitude, data[i].longitude),
                  map:map,
                  title:data[i].tdriver_id
              });
              google.maps.event.addListener(marker, 'click', markerClickEvent);
              dbMarkers[data[i].tdriver_id]=marker;
            };
            console.log(dbMarkers);
          });
        
        drawingManager.setMap(map);

      }
      var markerClickEvent = function(event) {
            console.log(this);
            
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
      google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <body>
    <div id="map-canvas"/>
  </body>
</html>