<?php
namespace App\Config;


define('BASEPATH','/');

define('PROTOCOL', ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443 || $_SERVER['HTTP_X_FORWARDED_PORT'] == 443) ? "https://" : "http://");

define('VIEWS', $_SERVER['DOCUMENT_ROOT'] . '/../app/View');