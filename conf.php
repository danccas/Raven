<?php
Route::config(function($app) {
  $app->library('Misc');
  $app->library('doris.pdo', 'Doris');
  $app->library('Pagination');
  $app->library('Identify');
  $app->library('Tablefy');
  $app->library('Session');

  $app->attr('root', dirname(__FILE__) . '/');
  $app->attr('librarys', $app->attr('root') . 'app/librarys/');
  $app->attr('controllers', $app->attr('root') . 'app/controllers/');
  $app->attr('views', $app->attr('root') . 'app/views/');
 
  Doris::registerDSN('moviles', 'mysql://root@localhost:3306/moviles');
});
