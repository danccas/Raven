<?php
require_once(__DIR__ . '/../core/route.php');

Route::import(__DIR__ . '/../conf.php');

Route::init()->debug(true);

Route::any('demo', function() {
  Route::view('theme_recover');
});
Route::else(function() {
  Route::response(404);
});
