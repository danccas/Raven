<?php

final class Dashboard {
  private $list   = array();
  private $counts = array();
  private $is_null = true;
  private static $instance;

  public static function getInstance() {
    if (null === static::$instance) {
      static::$instance = new static();
    }
    return static::$instance;
  }
  function __construct() {
  }
  public function __clone() {
    trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR);
  }
  private function _add($name, $size, $data) {
    $this->list[] = array(
      'name' => $name,
      'type' => $data['type'],
      'size' => $size,
      'data' => $data,
    );
    $this->is_null = false;
  }
  private function _sort() {
    //
  }
  static function addView($name, $size, $view) {
    $ce =& Dashboard::getInstance();
    if(!is_file($view)) {
      return false;
    }
    ob_start();
    (function() use($view) {
      require($view);
    })();
    $view = ob_get_clean();
    $ce->list[] = array(
      'name' => $name,
      'type' => 'view',
      'size' => $size,
      'data' => $view,
    );
  }
  static function addCount($name, $count) {
    $ce =& Dashboard::getInstance();
    $ce->counts[$name] = $count;
  }
  static function add($name, $size, $data) {
    return Dashboard::getInstance()->_add($name, $size, $data);
  }
  static function sort() {
    return Dashboard::getInstance()->_sort();
  }
  static function getAll() {
    return Dashboard::getInstance()->list;
  }
  static function getAllCounts() {
    return Dashboard::getInstance()->counts;
  }
  static function fill() {
    return !Dashboard::getInstance()->is_null;
  }
}
