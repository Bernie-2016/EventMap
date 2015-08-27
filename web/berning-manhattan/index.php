<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name='google-site-verification' content='-jE-f4Gbpim9_feo74iK5zP_-tegU7xvV89-yqFy7ZI' />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="description" content="We want Bernie Sanders to be president. Now it's time to get up and make that happen. Volunteer work and grassroots events are being organized everyday in the US. It is time to do everything we can to bring him to the White House. Let's do this!">
    <meta name="keywords" content="Bernie Sanders, FeelTheBern, Events, Bernie, #bernie2016, #feelthebern, #westandtogether">
    <meta property="og:image" content="http://d2bq2yf31lju3q.cloudfront.net/img/July29_FBMapImage_600px.png" />
    <meta property="og:url" content="http://www.bernie2016events.org" />
    <meta property="og:title" content="Let's Get to Work! Find Volunteer Work, Grassroots events, and official Rallies for Bernie Sanders"/>
    <meta property="og:description" content="We want Bernie Sanders to be president. Now it's time to get up and make that happen. Volunteer work and grassroots events are being organized everyday in the US. It is time to do everything we can to bring him to the White House. Let's do this!"/>

    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700,800">
    <link href='//api.tiles.mapbox.com/mapbox.js/v2.1.9/mapbox.css' rel='stylesheet' />
    <link href='/css/map.css?version=1.11' rel='stylesheet' />
    <link href='/css/custom.css?v=1.11' rel="stylesheet" type="text/css" />
    <link href='/css/work-map.css?v=1.11' rel="stylesheet" type="text/css" />
    <link href='/css/berning-manhattan.css?v=1.11' rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="/favicon.ico">

    <title>Let's Get to Work! Find Volunteer Work, Grassroots events, and official Rallies for Bernie Sanders</title>
  </head>
  <body>




<?php require_once('../inc/_header.php'); ?>
<div></div>
<section id='map-section' />
  <div id='map'></div>
  <div id='map-event-list'>
    <div id='map-instructions'>
      <span style="vertical-align: middle;font-weight: 600;"></span>  <img src="/img/logo.png" style="
    height: 20px;
    display: block;
    vertical-align: middle;
"> <span style="vertical-align">B16E Special Edition: Berning Manhattan</span>
    </div>
    <form id='zip-and-distance' action="#">
        <h2 id='event-results-count'>B16E Special Edition</h2>
        <h1>
          Berning Manhattan
        </h1>
        <div id='zipcode-container'>
          <input type='text' name='zipcode' id='input-text-zipcode' value='' placeholder='Zipcode' maxlength='5'/>
          <input type='submit' id='hidden-submit' style='visibility: hidden; position: absolute; right:0;top:0; z-index: -1;'/>
        </div>
        <div id='distance-container' style='display: none;'>
          <ul id='distance-list'>
              <li>
                        <input type='radio' id='mile-5' name='distance' value='5' /> <label for='mile-5'>5mi</label></li><li>
                        <input type='radio' id='mile-10' name='distance' value='10' checked='checked' /> <label for='mile-10'>10mi</label></li><li>
                        <input type='radio' id='mile-20' name='distance' value='20' /> <label for='mile-20'>20mi</label></li><li>
                        <input type='radio' id='mile-50' name='distance' value='50' /> <label for='mile-50'>50mi</label></li><li>
                        <input type='radio' id='mile-100' name='distance' value='100' /> <label for='mile-100'>100mi</label></li><li>
                        <input type='radio' id='mile-250' name='distance' value='250' /> <label for='mile-250'>250mi</label></li>          </ul>

        </div>
        </form>
        <div class='clear'></div>
    <div id='event-results-area'>
      <div id='event-list-area'>
        <ul id='event-list'>
          <!-- li>
            <h5 id='upcoming-event'>Upcoming nationwide event</h5>
            <a href='https://www.facebook.com/events/835545563198517/'>
              <img src='./img/ad.png' style='width: 100%; height: auto;'/>
            </a>
          </li -->
        </ul>
      </div>
  </div>
</section>
<footer>
  <sub>&copy; <a href='http://www.reddit.com/r/SandersForPresident'>SandersForPresident</a>. This site is not affiliated with the <a href='http://www.berniesanders.com'>Bernie Sanders Campaign.</a>&nbsp;|&nbsp;<a href='mailto:rapi@bernie2016events.org'>Contact&nbsp;Us</a>.</sub>

</footer>

  <script src='//d2bq2yf31lju3q.cloudfront.net/js/d3.gz' type='text/javascript'></script>
  <script id='zipcodes-datadump' type='text/plain'></script>
  <script src="//d2bq2yf31lju3q.cloudfront.net/js/jquery.gz"></script>
  <script src='//d2bq2yf31lju3q.cloudfront.net/js/mapbox.gz'></script>
  <script type='text/javascript' src="/js/berning-manhattan.js"></script>
  <script>

    $.ajax({
      // url: '//d2bq2yf31lju3q.cloudfront.net/js/bern-july-29-data.gz',
      url: '../csv-grab.php',
      data: {
        //https://docs.google.com/spreadsheets/d/1WCZdHAPmdAEUsDl-ipJGCeejDahaCiekbrauhl5llDA/export?gid=0&format=csv
        u: "https://docs.google.com/spreadsheets/d/16ZVTKJrPGYp1NVouJh1tIYUetqiQU6xVi7Hmybg13rk/export?gid=0&format=csv"
      },
      // dataType: 'script',
      dataType: 'text',
      cache: true, // otherwise will get fresh copy every page load
      success: function(data) {
        window.WORKDATA = data;
        window.dataCallback();
      }, error: function(a,b,c) {
        console.log("ERROR", b,c);
      }
    });

  </script>
  <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-64649524-1', 'auto');
  ga('send', 'pageview');
  </script>
  <script>
    window.fbAsyncInit = function() {
      FB.init({
        appId      : '1465128650469416',
        xfbml      : true,
        version    : 'v2.3'
      });
    };

    (function(d, s, id){
       var js, fjs = d.getElementsByTagName(s)[0];
       if (d.getElementById(id)) {return;}
       js = d.createElement(s); js.id = id;
       js.src = "//connect.facebook.net/en_US/sdk.js";
       fjs.parentNode.insertBefore(js, fjs);
     }(document, 'script', 'facebook-jssdk'));
  </script>
  <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
</body>
</html>
