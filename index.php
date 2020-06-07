<?php
require_once(__DIR__ . '/app/librarys/route.php');

Route::import('conf.php');

Route::init()->debug(true);

Route::any('demo', function() {
  Route::view('theme_recover');
});
Route::else(function() {
  Route::response(404);
});
