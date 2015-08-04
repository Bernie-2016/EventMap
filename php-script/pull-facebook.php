<pre>
<?php
session_start();

define('FACEBOOK_SDK_V4_SRC_DIR', '../lib/facebook-php-sdk-v4-4/src/Facebook/');
require_once('../lib/facebook-php-sdk-v4-4/autoload.php');

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSDKException;

$accessToken = "CAAU0htjC8CgBAKBTtYQe4hhiAYdku4o5RyTUL33PNZCiuOj0SZAIas36Y2v5lTjK422AJZCdstZAxnXYTGXv7eAemPLzIvk6Vgtse1GiAd8CH4ZBUTxRnuwVVhb47BlzBJAwqFOxqlfmo3bZAlZBTrWGDodevuEuRm1vBmfXj5QoZAXbZAuT8IQaW";
$appId = '1465128650469416';
$appSecret = 'a2bd57b2e122f8b8d24392e3003c9aff';
$callbackURL = "http://www.bernie2016events-local.org:8082/php-script/pull-facebook.php";

FacebookSession::setDefaultApplication($appId, $appSecret);

$today = new DateTime();
$helper = new FacebookRedirectLoginHelper($callbackURL, $appId, $appSecret);
$fromRedirect = false;
try {
  $session = new FacebookSession($accessToken);
} catch(FacebookSDKException $e) {

    try {
      $session = $helper->getSessionFromRedirect();
      $fromRedirect = true;

    } catch (FacebookSDKException $e) {
      $session = null;
    }
}


// print_r($session);

if ($session && $fromRedirect) {
  // User logged in, get the AccessToken entity.
  $accessToken = $session->getAccessToken();
  // Exchange the short-lived token for a long-lived token.
  $longLivedAccessToken = $accessToken->extend();
  print_r($longLivedAccessToken);
  // Now store the long-lived token in the database
  // . . . $db->store($longLivedAccessToken);
  // Make calls to Graph with the long-lived token.
  // . . .
} elseif ($session && !$fromRedirect) {
  // echo '/search?q=Bernie+Sanders&type=event&limit=5000&since=' . $today->getTimestamp();
  $request = new FacebookRequest($session, 'GET', '/search?q=Bernie+Sanders&type=event&limit=5000&since=' . $today->getTimestamp());
  $response = $request->execute();

  $searchResults = $response->getGraphObject();
  // echo "RESPONSE : ";
  $searchResultsArr = $searchResults->asArray();
  // print_r($searchResultsArr);

  $events = array();
  foreach($searchResultsArr['data'] as $item) {
    $events[] = array("method" => "GET", "relative_url" => "/{$item->id}?fields=place,start_time,end_time,name,owner,parent_group,timezone,is_date_only");

    if (count($events) >= 50) {
      printEvents($session, $events);
      $events = array();
    }
  }

  if (count($events) >= 0) {
    printEvents($session, $events);
    $events = array();
  }



} else {
  echo '<a href="' . $helper->getLoginUrl() . '">Login with Facebook</a>';
}

function printEvents($session, $events) {
  // print_r($events);
  $responseObj = (new FacebookRequest($session, 'POST', '?batch='.json_encode($events) ))->execute();

  $objects = $responseObj->getGraphObject();

  // print_r($objects->asArray());

  foreach ($objects->asArray() as $res) {
    $item = json_decode($res->body);
    // print_r($item);

    if ( isset($item->timezone) ) {
      $start_time = new DateTime($item->start_time, new DateTimeZone($item->timezone));
      $end_time = isset($item->end_time) ? new DateTime($item->end_time, new DateTimeZone($item->timezone)) : NULL;
     } else {
      $start_time = new DateTime($item->start_time);
      $end_time = isset($item->end_time) ? new DateTime($item->end_time) : NULL;
     }

    // print_r($item);

    echo @join("&lt;TAB&gt;", array(
            $item->id, $item->name, $start_time->format('m/d/Y'),
            $item->is_date_only ? '' : $start_time->format('h:i A'),
            isset($item->place->location) ? join(" ", array($item->place->name, $item->place->location->street,
                      $item->place->location->city, $item->place->location->state, $item->place->location->zip)) : $item->place->name,
            isset($item->place->location) ? $item->place->location->state : "NONE",
            isset($item->place->location) ? $item->place->location->zip : "",
            isset($item->place->location) ? $item->place->location->latitude : "",
            isset($item->place->location) ? $item->place->location->longitude : "",
            isset($item->owner->category) && $item->owner->category == "Community" ?
              $item->owner->name : "",
            isset($item->owner->category) && $item->owner->category == "Community" ?
              ("http://www.facebook.com/" . $item->owner->id) : "",
            "http://www.facebook.com/{$item->id}")) . "\n";

  }
}



// $longLived = $session->validate('1465128650469416', 'a2bd57b2e122f8b8d24392e3003c9aff');

// print_r($longLived);
// print_r($session);
// print_r($session->getToken());
// echo "<br/>";

// try {
//   $request = new FacebookRequest($session, 'GET', '/search?q=Bernie+Sanders&type=event');
//   print_r($request);

//   $response = $request->execute();



//   // $object = $response->getGraphObject();
//   // echo $object->getProperty('name');
//   print_r($response);
//   echo "<br/>";
// } catch (FacebookRequestException $ex) {
//   echo "<br/>xx";
//   echo $ex->getMessage();
// } catch (\Exception $ex) {
//   echo "<br/>yy";
//   echo $ex->getMessage();
// }

// try {
//   $respose = (new Facebook
// }

?>
</pre>
