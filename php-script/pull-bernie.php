<pre>
<?php
DEFINE('MEETUP_KEY', "f135658543aa2b7f6195b2bab26");
DEFINE('MEETUP_URL_SIGNATURE', 'https://go.berniesanders.com/page/event/search_results?format=json&wrap=no&orderby[0]=date&orderby[1]=desc&event_type=0&mime=text/json&limit=2000&country=*');

$contents = file_get_contents(MEETUP_URL_SIGNATURE);

// echo $contents;

$events = json_decode($contents);

// print_r($events);
foreach ( $events->results as $item ):
  $id_obfuscated = $item->id_obfuscated;
  $id = $item->url;
  $name = $item->name;

  $timezone = new DateTimeZone("UTC");
  @$start_time = new DateTime($item->start_dt);
  // $start_time->setTimezone($timezone);
  // $start_time->setTimestamp(((double) $item->time/1000)+(double) $item->utc_offset/1000);


  $end_time = NULL;

  // if (isset($item->duration)) {
    // $end_time = new DateTime($item->start_dt, new DateTimeZone($item->timezone));
    // $end_time->setTimezone($timezone);
    // $end_time->setTimestamp(((double) $item->time/1000) +(double) $item->utc_offset/1000 + ((double) $item->duration/1000));
  // }

  // echo intval($item->time) . "{$start_time->format('h:i A')} -- " . (intval($item->time) + intval($item->duration)) . "\n";

  // if ( isset($item->timezone) ) {
  //   $start_time = new DateTime($item->start_time, new DateTimeZone($item->timezone));
  //   $end_time = isset($item->end_time) ? new DateTime($item->end_time, new DateTimeZone($item->timezone)) : NULL;
  // } else {
  //   $start_time = new DateTime($item->start_time);
  //   $end_time = isset($item->end_time) ? new DateTime($item->end_time) : NULL;
  // }

  echo @join("&lt;TAB&gt;", array(
            "/".$id_obfuscated,
            $name, $start_time->format('m/d/Y'),
            $start_time->format('h:i A'),
            // $end_time ? $end_time->format('h:i A') : "",
            join("", array(
                isset($item->venue_name) ? ($item->venue_name ." "):"",
                isset($item->venue_address_1) ? ($item->venue_address_1 . " ") : "",
                isset($item->venue_city) ? ($item->venue_city . " ") : "" ,
                isset($item->venue_state_cd) ? ($item->venue_state_cd . " ") : "",
                isset($item->venue_zip) ? ($item->venue_zip . " ") : "", )),
            isset($item->venue_state_cd) ? $item->venue_state_cd : "NONE",
            isset($item->venue_zip) && isset($item->venue_zip) ? $item->venue_zip : "NONE",
            isset($item->latitude) && isset($item->latitude) ? $item->latitude : "NONE",
            isset($item->longitude) && isset($item->longitude) ? $item->longitude : "NONE",
            "Bernie Sanders Campaign Volunteers",
            "http://www.berniesanders.com",
            $item->url)) . "\n";
endforeach;
?>
</pre>
