<?php

/**
 * @license MIT License
 * @copyright maartendekeizer
 */

$memcached = new Memcached();
$memcached->addServer('127.0.0.1', 20159);

$name = 'testkey';
$ttl = 10;
$data = sha1(time());

$memcached->set($name, $data, $ttl);
echo date('His') . ': key "' . $name . '" set to "' . $data . '" with ttl ' . $ttl . PHP_EOL;

for ($i = 0; $i < ($ttl + 5); $i ++) {
  $res = $memcached->get($name);
  echo date('His') . ': key "' . $name . '" data is "' . $res . '" and that is ' . ($res == $data ? 'a match' : 'not a match') . PHP_EOL;
  sleep(1);
}
