<pre><?php
session_start();

define('FACEBOOK_SDK_V4_SRC_DIR', '../lib/facebook-php-sdk-v4-4/src/Facebook/');
require_once('../lib/facebook-php-sdk-v4-4/autoload.php');
// require_once('./groups.inc');
// require_once('./pages.inc');
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSDKException;

$accessToken = "CAAXA8FRxv7ABAJB75Ay4rixO4ZBGnU4c4ZAvQGVmbxD7XljvDka5KkMhZAyQ4zwk4gbowmWSrp9ZB4L8FBpOrMVUVNHzvWHwHOsT060b9aj3eOzDvZBEmtzT0zmhcX7Idp3Nw18JHuMB0UXBga0YAY9kMyvI1kGqLFgZCu6X6yFzyajvgh8FHZCyqzJrKfjRBkZD";
$appId = '1619513324978096';
$appSecret = 'a5eaf276069431fd9ba7c66bf7eb9eea';
$callbackURL = "http://www.bernie2016events-local.org:8082/php-script/pull-groups.php";

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
  // $request = new FacebookRequest($session, 'GET', '/search?q=Bernie+Sanders&type=event&limit=5000&since=' . $today->getTimestamp());


  // $file = file_get_contents("./pages.json");
  $file = file_get_contents("./groups.json");

  $pages = json_decode(trim($file));
  // $pages = $file;
//   $json_errors = array(
//     JSON_ERROR_NONE => 'No error has occurred',
//     JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
//     JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
//     JSON_ERROR_SYNTAX => 'Syntax error',
// );
//  echo 'Last error : ', $json_errors[json_last_error()], PHP_EOL, PHP_EOL;

  // var_dump($pages);

//   print_r($pages);

  // echo "XXX";

  foreach($pages->data as $page) {
    try {
      // echo "[LOG] " . '/' . $page->id . '/events?limit=5000&since=' . $today->getTimestamp() . "\n";
      $request = new FacebookRequest($session, 'GET', '/' . $page->id . '/events?limit=5000&since=' . $today->getTimestamp());
      $response = $request->execute();

      $searchResults = $response->getGraphObject();
      // echo "RESPONSE : ";
      $searchResultsArr = $searchResults->asArray();
      // print_r($searchResultsArr);
      $events = array();
      if (!isset($searchResultsArr['data']) ) continue;
      foreach($searchResultsArr['data'] as $item) {
        // echo "[LOG] /{$item->id}?fields=place,start_time,end_time,name,owner,parent_group,timezone" . "\n";
        $events[] = array("method" => "GET", "relative_url" => "/{$item->id}?fields=place,start_time,end_time,name,owner,parent_group,timezone");

        if (count($events) >= 50) {
          printEvents($session, $events);
          $events = array();
        }
      }

      if (count($events) > 0) {
        printEvents($session, $events);
        $events = array();
      }
    }
    catch (exception $e) { echo $e->getMessage() . "\n"; continue; }
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
      if (isset($item->start_time)) {
        $start_time = new DateTime($item->start_time);
        $end_time = isset($item->end_time) ? new DateTime($item->end_time) : NULL;
      } else {}
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
?>
</pre>
