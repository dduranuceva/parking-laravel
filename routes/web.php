<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});
Route::get('/test-path', function () {
   // return public_path();
   return asset('logos');  
});

require __DIR__.'/auth.php';
