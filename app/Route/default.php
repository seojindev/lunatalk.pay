<?php
use App\Util\Route;
use App\Controller\OrderController;

Route::add('/test', function() {
    echo BASEPATH;
}, 'get');


Route::add('/order', OrderController::order(), 'get');

Route::run(BASEPATH);