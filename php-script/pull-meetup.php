<pre>
<?php
DEFINE('MEETUP_KEY', "f135658543aa2b7f6195b2bab26");
DEFINE('MEETUP_URL_SIGNATURE', 'https://api.meetup.com/2/open_events?and_text=False&offset=0&format=json&limited_events=False&text=Bernie+AND+Sanders&photo-host=public&page=500&radius=1000&desc=False&status=upcoming&sig_id=13127811&sig=f9257c28770d9efc63cf1639e4d0678d66dd475d');

$contents = file_get_contents(MEETUP_URL_SIGNATURE);

echo $contents;

$events = json_decode($contents);

foreach ( $events->results as $item ):

  if ( isset($item->timezone) ) {
    $start_time = new DateTime($item->start_time, new DateTimeZone($item->timezone));
    $end_time = isset($item->end_time) ? new DateTime($item->end_time, new DateTimeZone($item->timezone)) : NULL;
  } else {
    $start_time = new DateTime($item->start_time);
    $end_time = isset($item->end_time) ? new DateTime($item->end_time) : NULL;
  }

  echo @join("&lt;TAB&gt;", array(
            $item->id, $item->name, $start_time->format('m/d/Y'),
            $item->is_date_only ? '' : $start_time->format('h:i A'),
            $item->is_date_only || !$end_time ? '' : $end_time->format('h:i A'),
            isset($item->place->location) ? join(" ", array($item->place->name, $item->place->location->street,
                      $item->place->location->city, $item->place->location->state, $item->place->location->zip)) : $item->place->name,
            isset($item->place->location) ? $item->place->location->state : "NONE",
            isset($item->place->location) ? $item->place->location->zip : "",
            isset($item->owner->category) && $item->owner->category == "Community" ?
              $item->owner->name : "",
            isset($item->owner->category) && $item->owner->category == "Community" ?
              ("http://www.facebook.com/" . $item->owner->id) : "",
            $item->event_url)) . "\n";
endforeach;
?>
</pre>
