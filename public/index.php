<?php
error_reporting(1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/config/app.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


require __DIR__ . '/../app/Bootstrap.php';


