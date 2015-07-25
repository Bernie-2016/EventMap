<?php

  require_once('./inc/_memcached.inc');
  header("Content-Type: text/plain");

  define('BERNIE2016_URL', "https://go.berniesanders.com/page/event/search_results?format=json&wrap=no&orderby[0]=date&orderby[1]=desc&event_type=13&mime=text/json&limit=4000&country=*");
  define('GOOGLE_SPREADSHEET', "https://docs.google.com/spreadsheets/d/1IaJQtbrsb8_bxpoayN-DhgAb3o_RMUDZyI4TwADmM1g/export?gid=0&format=csv");
  define('POSTALCODES', "./d/us_postal_codes.csv");
  define('LOCAL_JULY29', "./d/july29.json");


  /* process BernieEvents */
  function processBernieEvents($url_set) {
    $content = file_get_contents($url_set);

    $decoded = json_decode($content);


    $rsvp_count = 0;
    foreach($decoded->results as $ind => $result) {
        unset($result->description);
        unset($result->id_obfuscated);
        unset($result->closed_msg);
        unset($result->distance);

        $result->location = $result->venue_name . " " . $result->venue_addr1 . " " . $result->venue_city . " " .
                    $result->venue_state_cd . " " . $result->venue_zip;

        unset($result->venue_name);
        unset($result->venue_addr1);
        unset($result->venue_city);
        unset($result->venue_state_cd);
        unset($result->venue_zip);

      $rsvp_count += $result->attendee_count;
    }

    $decoded->settings->rsvp = $rsvp_count;
    return json_encode($decoded);
    // print_r($decoded);
    // return $url_set;
  }

  $url = $_GET['u'];
  $refresh = isset($_GET['refresh']) ? (int) $_GET['refresh'] : 0 ;

  // if ($mc) {

  //   $mc->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
  //   $mc->addServers(array_map(function($server) { return explode(':', $server, 2); }, explode(',', $_ENV['MEMCACHEDCLOUD_SERVERS'])));
  //   $mc->setSaslAuthData($_ENV['MEMCACHEDCLOUD_USERNAME'], $_ENV['MEMCACHEDCLOUD_PASSWORD']);

    if ( $url == BERNIE2016_URL || $url == LOCAL_JULY29 )
    {
      if ($refresh) {
        $content = processBernieEvents($url);
        $mc->set($url, $content);
        echo $content;
        // echo "-- Refresh {$url} --";
      }
      else {
        if ($mc->get($url)) {
          $content = $mc->get($url);
        } else {
          $content = processBernieEvents($url);
          $mc->set($url, $content);
        }
        echo $content;
      }
    } else if (
            $url == GOOGLE_SPREADSHEET ||
            $url == POSTALCODES )
    {

      if ($refresh) {
        $content = file_get_contents($url);
        $mc->set($url, $content);
        echo "-- Refresh {$url} --";
      } else {
        if ($mc->get($url)) {
          $content = $mc->get($url);
        } else {
          $content = file_get_contents($url);
          $mc->set($url, $content);
        }
        echo $content;
      }
    }

  // } else {
  //   return;
  // }



//  $data = json_decode($content);


?>
