<?php

  $mc = new Memcached();

  if ($mc) {

    $mc->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
    $mc->addServers(array_map(function($server) { return explode(':', $server, 2); }, explode(',', $_ENV['MEMCACHEDCLOUD_SERVERS'])));
    $mc->setSaslAuthData($_ENV['MEMCACHEDCLOUD_USERNAME'], $_ENV['MEMCACHEDCLOUD_PASSWORD']);

    $mc->set('foo', 'bar');
    echo $mc->get('foo');

  } else {
    echo "X";
  }
?>
