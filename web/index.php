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
    <link rel="shortcut icon" href="/favicon.ico">

    <title>Let's Get to Work! Find Volunteer Work, Grassroots events, and official Rallies for Bernie Sanders</title>
  </head>
  <body>




<?php require_once('inc/_header.php'); ?>
<div></div>
<section id='map-section' />
  <div id='map'></div>
  <div id='map-event-list'>
    <div id='map-instructions'>
      <span style="vertical-align: middle;font-weight: 600;"></span>  <img src="//d2bq2yf31lju3q.cloudfront.net/img/logo.png" style="
    height: 20px;

    vertical-align: middle;
"> <span style="

vertical-align"> Let's get to work!</span>
    </div>
      <form id='zip-and-distance' action="#">
        <div id="error-box"></div>
        <div id='zipcode-container'>
          <input type='text' name='zipcode' id='input-text-zipcode' value='' placeholder='Zipcode' maxlength='5'/>
          <input type='submit' id='hidden-submit' style='visibility: hidden; position: absolute; right:0;top:0; z-index: -1;'/>
        </div>
        <div id='distance-container'>
          <ul id='distance-list'>
              <li>
                        <input type='radio' id='mile-5' name='distance' value='5' /> <label for='mile-5'>5mi</label></li><li>
                        <input type='radio' id='mile-10' name='distance' value='10' /> <label for='mile-10'>10mi</label></li><li>
                        <input type='radio' id='mile-20' name='distance' value='20' /> <label for='mile-20'>20mi</label></li><li>
                        <input type='radio' id='mile-50' name='distance' value='50'  checked='checked' /> <label for='mile-50'>50mi</label></li><li>
                        <input type='radio' id='mile-100' name='distance' value='100' /> <label for='mile-100'>100mi</label></li><li>
                        <input type='radio' id='mile-250' name='distance' value='250' /> <label for='mile-250'>250mi</label></li>          </ul>

        </div>
        <div class='clear'></div>
        <div id='event-type-container'>
          <ul id='event-type-list'>
            <li class='event-type-item'>
              <input type='checkbox' checked='checked' name='eventtype' id='event-R' value='R' />
              <label for='event-R' data-tooltip="Show/Hide Official Bernie Sanders Events">
                <span class='etype-bullet etype-selected'>&#9673;</span>
                <span class='etype-name'>Official Event</span>
              </label>
            </li>
            <li class='event-type-item'>
              <input type='checkbox' checked='checked' name='eventtype' id='event-CW' value='CW' />
              <label for='event-CW' data-tooltip="Show/Hide Volunteer activity (flyering, calling, walking, etc)">
                <span class='etype-bullet etype-selected'>&#9673;</span>
                <span class='etype-name'>Volunteer Work</span>
              </label>
            </li>
            <li class='event-type-item'>
              <input type='checkbox' checked='checked' name='eventtype' id='event-E' value='E' />
              <label for='event-E' data-tooltip="Show/Hide Volunteer meetings to get organized or learn more.">
                <span class='etype-bullet etype-selected'>&#9673;</span>
                <span class='etype-name'>Meetings</span>
              </label>
            </li>
          </ul>
        </div>
        <div id='daterange-opt'>
          <span id='daterange-value'>All Events</span>
          <ul id='daterange-options'>
            <li data-daterange='today' class='daterange-options-item'>
              <label for='daterange-today'>
                <input name='daterange' type='radio' id='daterange-today' value='today' />Today
              </label>
            </li>
            <li data-daterange='this-week' class='daterange-options-item'>
              <label for='daterange-this-week'>
                <input name='daterange' type='radio' id='daterange-this-week' value='this-week' />This Week
              </label>
            </li>
            <li data-daterange='in-2-weeks' class='daterange-options-item'>
              <label for='daterange-in-2-weeks'>
                <input name='daterange' type='radio' id='daterange-in-2-weeks' value='in-2-weeks'/>
                In 2 Weeks
              </label>
            </li>
            <li data-daterange='this-month' class='daterange-options-item'>
              <label for='daterange-this-month'>
                <input name='daterange' type='radio' id='daterange-this-month' value='this-month'/>
                This Month
              </label>
            </li>
            <li data-daterange='all-events' class='daterange-options-item'>
              <label for='daterange-all-events'>
                <input name='daterange' type='radio' id='daterange-all-events' value='all-events' checked="checked"/>
                All Events
              </label>
            </li>
          </ul>
        </div>
        <div class='clear'></div>
        </form>


      <div id='event-results-area'>
        <h2 id='event-results-count' style='display: none'> <span>within</span> <span id='event-distance'></span> <span>of</span>
          <span id="event-city"></span>
          <ul id='event-or-office'>
            <li><input type='radio' name='entity-type' value='events' checked='checked' id='entity-type-events'/><label for='entity-type-events'><span id='event-counter'></span></label></li>
            <li><input type='radio' name='entity-type' value='offices' id='entity-type-offices' /><label for='entity-type-offices'><span id='office-counter'>3 offices</span></label></li>
          </ul>
        </h2>
        <div id='event-list-area'>
          <ul id='office-list'>
          </ul>
          <ul id='event-list'>
          </ul>
          <div id="footer-area" style="text-align: center; margin-top: 20px; border: solid 3px #EEEEEE; border-width: 3px 0 0 0; padding-top: 10px;">
            <sub>&copy; <a href='http://www.reddit.com/r/SandersForPresident'>SandersForPresident</a>. This site is not affiliated with the <a href='http://www.berniesanders.com'>Bernie Sanders Campaign.</a>&nbsp;|&nbsp;<a href='mailto:rapi@bernie2016events.org'>Contact&nbsp;Us</a>.</sub>
          </div>
        </div>
  </div>
</section>
<footer>
  <sub>&copy; <a href='http://www.reddit.com/r/SandersForPresident' target="_blank">SandersForPresident</a>. This site is not affiliated with the <a href='http://www.berniesanders.com' target="_blank">Bernie Sanders Campaign.</a>&nbsp;|&nbsp;<a href='mailto:rapi@bernie2016events.org'>Contact&nbsp;Us</a>.</sub>

</footer>

  <script src='//d2bq2yf31lju3q.cloudfront.net/js/d3.gz' type='text/javascript'></script>
  <script id='zipcodes-datadump' type='text/plain'></script>
  <script src="//d2bq2yf31lju3q.cloudfront.net/js/jquery.gz"></script>
  <script src='//d2bq2yf31lju3q.cloudfront.net/js/mapbox.gz'></script>
  <script src='/js/leaflet-bouncer.js'></script>
  <script type='text/javascript' src="/js/bern-map-async.js"></script>
  <script>


    $.ajax({
      // url: '/js/bern-july-29-data.gz',
      url: '//d2bq2yf31lju3q.cloudfront.net/js/event-data.gz',
      // data: {
      //   u: "https://docs.google.com/spreadsheets/d/1Ze8IkpTq2nBWKGxtd4Zxs8QH1oUoiqXbFwdqC5CzcpI/export?gid=0&format=csv"
      // },
      dataType: 'script',
      // dataType: 'text',
      cache: true, // otherwise will get fresh copy every page load
      success: function(data) {
        // window.WORKDATA = data;
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
