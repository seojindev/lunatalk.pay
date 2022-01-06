<?php
use App\util\Route;

Route::add('/test', function() {
    echo BASEPATH;
}, 'get');

Route::run(BASEPATH);