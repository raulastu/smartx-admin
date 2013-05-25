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
    <script type="text/javascript" src="js/admin_dashboard.js"></script>
  </head>
  <body>
    <div id="map-canvas"/>
  </body>
</html>