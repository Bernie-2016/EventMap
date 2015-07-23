<?php
  $mc = new Memcached();
  $mc->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
  $mc->addServers(array_map(function($server) { return explode(':', $server, 2); }, explode(',', $_ENV['MEMCACHEDCLOUD_SERVERS'])));
  $mc->setSaslAuthData($_ENV['MEMCACHEDCLOUD_USERNAME'], $_ENV['MEMCACHEDCLOUD_PASSWORD']);

?>
