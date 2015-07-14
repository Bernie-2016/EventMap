<pre>
<?php
DEFINE('MEETUP_KEY', "f135658543aa2b7f6195b2bab26");
DEFINE('MEETUP_URL_SIGNATURE', 'https://api.meetup.com/2/open_events?and_text=False&offset=0&format=json&limited_events=False&text=Bernie+AND+Sanders&photo-host=public&page=500&radius=5000&desc=False&status=upcoming&sig_id=13127811&sig=f9257c28770d9efc63cf1639e4d0678d66dd475d');

$contents = file_get_contents(MEETUP_URL_SIGNATURE);

// echo $contents;

$events = json_decode($contents);

// print_r($events);
foreach ( $events->results as $item ):
  $id = $item->id;
  $name = $item->name;

  $timezone = new DateTimeZone("UTC");
  $start_time = new DateTime();
  $start_time->setTimezone($timezone);
  $start_time->setTimestamp(((double) $item->time/1000)+(double) $item->utc_offset/1000);


  $end_time = NULL;

  if (isset($item->duration)) {
    $end_time = new DateTime();
    $end_time->setTimezone($timezone);
    $end_time->setTimestamp(((double) $item->time/1000) +(double) $item->utc_offset/1000 + ((double) $item->duration/1000));
  }

  // echo intval($item->time) . "{$start_time->format('h:i A')} -- " . (intval($item->time) + intval($item->duration)) . "\n";

  // if ( isset($item->timezone) ) {
  //   $start_time = new DateTime($item->start_time, new DateTimeZone($item->timezone));
  //   $end_time = isset($item->end_time) ? new DateTime($item->end_time, new DateTimeZone($item->timezone)) : NULL;
  // } else {
  //   $start_time = new DateTime($item->start_time);
  //   $end_time = isset($item->end_time) ? new DateTime($item->end_time) : NULL;
  // }

  echo @join("&lt;TAB&gt;", array(
            $item->event_url,
            $name, $start_time->format('m/d/Y'),
            $start_time->format('h:i A'),
            $end_time ? $end_time->format('h:i A') : "",
            isset($item->venue) ? join("", array(
                isset($item->venue->name) ? ($item->venue->name ." "):"",
                isset($item->venue->address_1) ? ($item->venue->address_1 . " ") : "",
                isset($item->venue->city) ? ($item->venue->city . " ") : "" ,
                isset($item->venue->state) ? ($item->venue->state . " ") : "",
                isset($item->venue->zip) ? ($item->venue->zip . " ") : "", )) : "",
            isset($item->venue) && isset($item->venue->state) ? $item->venue->state : "NONE",
            isset($item->venue) && isset($item->venue->zip) ? $item->venue->zip : "NONE",
            isset($item->group) ?
              $item->group->name : "",
            isset($item->group) ?
              ("http://www.meetup.com/" . $item->group->urlname) : "",
            $item->event_url)) . "\n";
endforeach;
?>
</pre>
