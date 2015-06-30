<?php
  /* This wuold try to solve the IE problem, by first accessing a website from the outsite and then printing it out here..

Rapi Castillo
*/
  $url = $_GET['u'];
  $content = file_get_contents($url);
//  $data = json_decode($content);

  echo $content;
?>
