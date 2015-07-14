<?php require_once('./inc/_variables.inc'); ?>
<!DOCTYPE html>
<head>
<meta charset="utf-8" />
<meta name='google-site-verification' content='-jE-f4Gbpim9_feo74iK5zP_-tegU7xvV89-yqFy7ZI' />

<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="description" content="<?php echo isset($description) ? $description : "This Map shows all upcoming meetups and events around the United States. Discover all events near you and meet other people who want to make the country better. Discuss plans, organize events, and strategize locally and nationally on how to get Bernie Sanders to the White House." ?>">
<meta name="keywords" content="Bernie Sanders, FeelTheBern, Events, Bernie, #bernie2016, #feelthebern">
<meta property="og:image" content="<?php echo isset($og_img) ? $og_img : "http://www.bernie2016events.org/img/bernie-logo.png"; ?>" />
<meta property="og:title" content="<?php echo isset($title) ? $title : "Map of Grassroot Events for Bernie Sanders - Discover events and meetups near you | Bernie Sanders 2016 Events"; ?>"/>
<meta property="og:description" content="<?php echo isset($description) ? $description : "This Map shows all upcoming meetups and events around the United States. Discover all events near you and meet other people who want to make the country better. Discuss plans, organize events, and strategize locally and nationally on how to get Bernie Sanders to the White House." ?>"/>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700,800">
<title><?php echo isset($title) ? $title : "Map of Grassroot Events for Bernie Sanders - Discover events and meetups near you | Bernie Sanders 2016 Events"; ?></title>
<link rel="shortcut icon" href="http://www.bernie2016events.org/favicon.ico">
</head>
<body>
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

<link href='css/custom.css?v=<?php echo $APPVERSION; ?>' rel="stylesheet" type="text/css" />

<div id='header'>
  <a id='header-logo' href='./' ><img src='./img/logo.png' style='float: left; margin-left: 10px;' /></a>
  <nav>
    <ul id="nav">
      <!-- <li><a href='./july29'><strong style="color: #ea504e">July 29 Organizing Kick-off events</strong></a></li> -->
      <li><a href='./'>All Events</a></li>
      <li><a href='./by-state' >By state</a></li>
      <li><a href='https://medium.com/@BernieMeetups/' target='_blank'>News</a></li>
      <li id='social-buttons'>
        <!-- <a class='subscribe-icon' href='https://twitter.com/intent/follow?screen_name=BernieMeetups' ><img src='./img/icon/twitter.png' /></a> -->
        <a class='subscribe-icon' href='http://twitter.com/BernieMeetups' target='_blank'><img src='./img/icon/twitter.png' /></a>
        <a class='subscribe-icon' href='http://www.facebook.com/berniemeetups' target='_blank'><img src='./img/icon/facebook.png' /></a>
        <a class='subscribe-icon' href='http://bernie2016events.us11.list-manage.com/subscribe?u=7101205eff90b8bd4cc638328&id=fcdf771f70' target='_blank'><img src='./img/icon/mailchimp.png' /></a>
        <a class='subscribe-icon' href='http://feeds.feedburner.com/Bernie2016Events?format=xml' target='_blank'><img src='./img/icon/rss.png' /></a>

      </li>
      <!-- <li><a href='https://twitter.com/intent/follow?screen_name=BernieMeetups' >Follow</a></li> -->
      <li><a href='https://secure.actblue.com/contribute/page/reddit-for-bernie' class='contribute contribute-big' target='_blank'>Contribute to the Campaign</a></li>
    </ul>
  </nav>
  <div style='clear: both'></div>
</div>
<div></div>
