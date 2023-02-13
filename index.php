<?php

require_once("vendor/autoload.php");

$f3 = Base::instance();

$f3->config('app/config/main_config.ini');

$f3->LANGUAGE = $f3->get('sitelang');
$f3->session = new Session();

$f3->logger = new Log(date("Ymd").'.log');
//$f3->map('/api/test/@id','HoursController');
Falsum\Run::handler();
$f3->run();