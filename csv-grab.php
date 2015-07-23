<?php

  header("Content-Type: text/plain");

  $mc = new Memcached();
  $url = $_GET['u'];
  $refresh = isset($_GET['refresh']) ? (int) $_GET['refresh'] : 0 ;

  if ($mc) {

    $mc->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
    $mc->addServers(array_map(function($server) { return explode(':', $server, 2); }, explode(',', $_ENV['MEMCACHEDCLOUD_SERVERS'])));
    $mc->setSaslAuthData($_ENV['MEMCACHEDCLOUD_USERNAME'], $_ENV['MEMCACHEDCLOUD_PASSWORD']);

    if (  ( $url == "https://go.berniesanders.com/page/event/search_results?format=json&wrap=no&orderby[0]=date&orderby[1]=desc&event_type=13&mime=text/json&limit=4000&country=*" ||
            $url == "https://docs.google.com/spreadsheets/d/1IaJQtbrsb8_bxpoayN-DhgAb3o_RMUDZyI4TwADmM1g/export?gid=0&format=csv") ||
            $url == "./d/us_postal_codes.csv"
      ) {

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

  } else {
    return;
  }



//  $data = json_decode($content);


?>
