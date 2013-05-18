<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
        <link rel="stylesheet" href="css/normalize.css">
        <script src="js/vendor/modernizr-2.6.2.min.js"></script>


        <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
    </head>
    <body style="background: url(https://abs.twimg.com/a/1367955655/t1/img/front_page/cricket@2x.jpg) no-repeat center center fixed; 
      -webkit-background-size: cover;
      -moz-background-size: cover;
      -o-background-size: cover;
      background-size: cover;
    ">

    <!-- START FB-Connect -->
    <div id="fb-root"></div>
    <script>
      function fblogin(){
        // var nextPage = window.location;
        FB.login(function(response) {
            if (response.authResponse) {
                window.location = "/fblogin";
             // window.location.reload();
            }
        });
      }

      window.fbAsyncInit = function() {
        FB.init({
          appId      : '462894637088601',
          channelUrl : '/fb_plugin/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true  // parse XFBML
        });
        // Additional initialization code here
      };

      // Load the SDK Asynchronously
      (function(d){
         var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
         if (d.getElementById(id)) {return;}
         js = d.createElement('script'); js.id = id; js.async = true;
         js.src = "//connect.facebook.net/en_US/all.js";
         ref.parentNode.insertBefore(js, ref);
       }(document));
    </script>
    <!-- END FB-Connect -->


        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->


        <!-- Add your site or application content here -->
        <div class="navbar navbar-inverse navbar-fixed-top">
          <div class="navbar-inner">
            <div class="container">
              <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </a>
              <a class="brand" href="#">WhatToGetHer.com</a>
            </div>
          </div>
        </div>

    <div class="container">
      <div class="row" style="padding-top:20%;">

            </div>
            <div class="row" style="height:100px; color: #eee; font-size: 1.4em;">
              <div class="span4" >Don't know what to get her/him? Borrow ideas from friends!</div>

              <div class="span3 offset2 ">
                <div class="btn-group">

                  <button class="btn btn-large btn-custom-darken" onclick="fblogin()"><img src="img/fb-f.png"/></button>
                  <button class="btn btn-large btn-custom-lighten" onclick="fblogin()">Sign up with Facebook</button>
                </div>
                

              </div>
            </div>

    </div>
      

        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.9.0.min.js"><\/script>')</script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>


    </body>
</html>
