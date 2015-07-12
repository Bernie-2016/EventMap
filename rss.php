<?php

define("PAGE_LIMIT", 500);
define("RESOURCE_URL", "https://docs.google.com/spreadsheets/d/1IaJQtbrsb8_bxpoayN-DhgAb3o_RMUDZyI4TwADmM1g/export?gid=0&format=csv");

$contents = file_get_contents(RESOURCE_URL);

$lines = explode("\n", $contents);

$lines = array_splice($lines, 1);

$lines  = array_reverse($lines);

// print_r($lines);


header('Content-Type: text/xml; charset=utf-8', true); //set document header content type to be XML

$rss = new SimpleXMLElement('<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom"></rss>');
$rss->addAttribute('version', '2.0');

$channel = $rss->addChild('channel'); //add channel node

$atom = $rss->addChild('atom:atom:link'); //add atom node
$atom->addAttribute('href', 'http://www.bernie2016events.org'); //add atom node attribute
$atom->addAttribute('rel', 'self');
$atom->addAttribute('type', 'application/rss+xml');

$title = $channel->addChild('title','Bernie2016events.org RSS Feed'); //title of the feed
$description = $channel->addChild('description','Discover meetups and events about Bernie Sanders near you'); //feed description
$link = $channel->addChild('link','www.bernie2016events.org'); //feed site
$language = $channel->addChild('language','en-us'); //language

//Create RFC822 Date format to comply with RFC822
$date_f = date("D, d M Y H:i:s T", time());
$build_date = gmdate(DATE_RFC2822, strtotime($date_f));
$lastBuildDate = $rss->addChild('lastBuildDate',$date_f); //feed last build date

$generator = $rss->addChild('generator','Bernie2016Events generator'); //add generator node


//connect to MySQL - mysqli(HOST, USERNAME, PASSWORD, DATABASE);

  // $csv = str_getcsv($lines[0]);

  // print_r($csv);

  // $title = $csv[5];
  // $link = explode(",", $csv[12], 2)[1];
  // $date_rfc = gmdate(DATE_RFC2822, strtotime($csv[1] . ' ' . $csv[2]));
  // $address = $csv[6];
  // $state = $csv[4];
  // $zipcode = $csv[7];
  // $source =$csv[11];

  // $json = json_encode(
  //             array("title" => $title,
  //                   "link" => $link,
  //                   "date" => $date_rfc,
  //                   "location" => $address,
  //                   "state" => $state,
  //                   "zipcode" => $zipcode,
  //                   "source"=> $source,
  //                   "local_time" => $csv[2],
  //                   "local_date"=>$csv[1] )
  //             );


  // // print_r($json);
  $cnt = 0;
  foreach($lines as $line) //loop through each row
  {



      $csv = str_getcsv($line);


      if ( !isset($csv[4]) || in_array($csv[4], array("CANCELLED", "DUPLICATE")) || $csv[4] == "" ) continue;
      if ( $cnt++ > PAGE_LIMIT ) break;

      // print_r($csv);echo "\n";

      $title = htmlentities($csv[5]);
      @$link = isset($csv[12]) ? explode(",", $csv[12], 2)[1] : "";
      $date_rfc = isset($csv[1]) ? gmdate(DATE_RFC2822, strtotime($csv[1] . ' ' . $csv[2])) : "";
      $address = isset($csv[6]) ? htmlentities($csv[6]) : "";
      $state = isset($csv[4]) ? htmlentities($csv[4]) : "";
      $zipcode = isset($csv[7]) ? htmlentities($csv[7]) : "";
      $source = isset($csv[11]) ? htmlentities($csv[11]) : "";

      $json = json_encode(
                  array("title" => $title,
                        "link" => $link,
                        "date" => $date_rfc,
                        "location" => $address,
                        "state" => $state,
                        "zipcode" => $zipcode,
                        "source"=> $source,
                        "local_time" => isset($csv[2]) ? $csv[2] : "",
                        "local_date"=> isset($csv[1]) ? $csv[1] : "" )
                  );

      $item = $channel->addChild('item'); //add item node
      $xml_title = $item->addChild('title', htmlentities($title)); //add title node under item
      $xml_link = $item->addChild('link', $link); //add link node under item
      $xml_guid = $item->addChild('guid', $link ); //add guid node under item
      $xml_loc = $item->addChild('description', (isset($csv[1]) ? $csv[1] : "") . " " . (isset($csv[2]) ? $csv[2] : "") . " - " . $address);
      $xml_loc = $item->addChild('location');
        $xml_loc->addChild('address', $address);
        $xml_loc->addChild('state', $state);
        $xml_loc->addChild('zipcode', $zipcode);
      $xml_sched = $item->addChild('local_time');
        $xml_sched->addChild('date', isset($csv[1]) ? $csv[1] : "");
        $xml_sched->addChild('time', isset($csv[2]) ? $csv[2] : "");
      $xml_guid->addAttribute('isPermaLink', 'true'); //add guid node attribute

      // $xml_attr = $item->addChild("

      $description = $item->addChild('description', '<![CDATA['. htmlentities($json) . ']]>'); //add description

      // $date_rfc = gmdate(DATE_RFC2822, strtotime($row->published));
      $item = $item->addChild('pubDate', $date_rfc); //add pubDate node
  }

echo $rss->asXML(); //output XML

?>
