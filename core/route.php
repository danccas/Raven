<?php
Class Route {
  private static $passVarNam  = '_tym'; #Viene de Route
  private $permissions = null;
  private $http = '';
  private $data = array();
  public  $analyze = null;
  public  $route = null;
  private $errores = array();
  public $web = array();
  private $rdebug = false;
  public $title = null;
  public $description;
  private static $instance;
  public $current_route = array();
  public $merge_route   = array();
  private $middlewares   = array();
  public $middleware = null;

  private $configure = array();
  public $libs;


  private $request;

  /* Middleware */
  public static function add($callback) {
    $ce = static::getInstance();
    if(is_callable($callback)) {
      $ce->middlewares[] = $callback;
    }
    return $ce;
  }
  static function init() {
    $ce = static::g();
    $url = PHP_SAPI == 'cli' ? (isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '') : (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null);
    $url = $ce->clear($url);
    $ce->route = array(
      'main'  => '/' . $url,
      'path'  => $url,
      'old'   => null,
      'query' => array(),
      'back'  => null,
      'back2' => null,
      'tree'  => array(),
    ); 
    $ce->analyze_route();
    return $ce;
  }
  static function addQuery($q) {
    $ce = static::getInstance();
    $ce->route['back2'] = $ce->route['back'];
    $ce->route['old'] = $ce->route['main'];
    $ce->route['query'][] = $q;
    $ce->route['back'] = $ce->route['old'] . (!empty($ce->route['query']) ? '?' . implode('&', $ce->route['query']) : '');
  }
  private function clear($query) {
    if(empty($query)) {
      return '';
    }
    $query = parse_url($query); // Solo queremos la URL sin parámetros GET
    $query = !empty($query['path']) ? $query['path'] : '';
    $query = preg_replace("/\/+/", '/', $query); // Quitamos todos los slashes repetidos (e.g. "politica/:codigo")
    $query = trim($query, '/') . '/';
    return $query;
  }
  private function route($route, $regexps = false, $started = false, $delete = true) {
    $query = $this->route['path'];
    if($this->rdebug) {
      echo "=========================================<br />\n";
      $e = $this->route_process($query, $route, $regexps, $started, $delete, $cantidad);
      $deb = debug_backtrace();
      echo "File:  " . $deb[1]['file'] . ":" . $deb[1]['line'] . "<br />\n";
      echo "Query: " . json_encode($query) . "<br />\n";
      echo "Route: " . json_encode($route) . "<br />\n";
      echo "Regex: " . json_encode($regexps) . "<br />\n";
      echo "Start: " . json_encode($started) . "<br />\n";
      echo "Delet: " . json_encode($delete) . "<br />\n";
      echo "Resul: " . (!empty($e) ? '<span style="color:green">TRUE</span>' : '<span style="color:red">FALSE</span>') . "<br />\n";
      echo "Data:  " . json_encode($e) . "<br />\n";
      echo "Canti: " . json_encode($cantidad) . "<br />\n";
      echo "========================================<br /><br />\n";
    } else {
      $e = $this->route_process($query, $route, $regexps, $started, $delete, $cantidad);
    }
#    var_dump(array($route, $query));
#    echo $route;
#    var_dump($e);
    if(!empty($e)) {
#      var_dump(array($route, $query));
      $sub = $cantidad != 0 ? $e[0] : $route;
      $this->route['old'] = '/' . implode('/', $this->route['tree']);
      $this->route['back'] = $this->route['old'] . (!empty($this->route['query']) ? '?' . implode('&', $this->route['query']) : '');
      $this->route['tree'][] = trim($sub, '/');
      $query =  $delete ? substr($query, strlen($sub)) : $query;
      $query = $this->clear($query);
      $this->route['path'] = $query;
    }
    return $e;
  }
  private function route_process($query, $route, $regexps = false, $started = false, $delete = true, &$cantidad = 0) {
    if(is_null($query)) { /* TODO: le cambie a NOT NOT */
      $query = $this->route['main'];
      $query = $this->clear($query);
    }
    $route = str_replace('/', '\/', $route);
    $expresion_regular = preg_replace_callback("/\:(?<id>[\w_]+)\;?/", function($n) use($regexps) {
      $regexp = !empty($regexps[$n['id']]) ? $regexps[$n['id']] : '[^\/]+';
      $regexp = "(?P<" . $n['id'] . ">" . $regexp . ")";
      return $regexp;
    }, $route, -1, $cantidad);
    if($cantidad != 0) {
      $expresion_regular = '/^' . $expresion_regular . ($started ? '\//' : '$/');
      $e = preg_match($expresion_regular, ($started ? $query : trim($query, '/')), $r) ? array_merge(array('route' => $query), $r) : FALSE;
    } else {
      $route = str_replace('\/', '/', $route);
      //$route = trim($route, '/') . '/';
      $e = $started ? strpos($query, $route) === 0 : $route == trim($query, '/');
    }
    return $e;
  }
  private static function __request($method, $a1, $a2, $a3 = null) {
    $ce = static::getInstance();
    $regex    = $a1;
    $eq       = null;
    $callback = null;
    if($a3 === null) {
      $callback = $a2;
    } else {
      $eq = $a2;
      $callback = $a3;
    }
    if(PHP_SAPI !== 'cli') {
      if(in_array($method, ['post','get','put','delete'])) {
        if(strtolower($_SERVER['REQUEST_METHOD']) != $method) {
          return $ce;
        }
      }
    }
    $r = $ce->route($regex, $eq, $method == 'path', true);
    if($r) {
      $back = null;
      foreach($ce->middlewares as $m) {
        $back = ($m)($ce, $back);
      }
      $ce->middleware = $back;
      if(is_array($ce->current_route)) {
        $ce->merge_route = array_merge($ce->merge_route, $ce->current_route);
      }
      $ce->current_route = $r;
      Closure::bind($callback, $ce)($ce);
      exit;
    } else {
      $ce->middleware = null;
    }
    return $ce;
  }
  public static function any($a1, $a2, $a3 = null) {
    return static::__request('any', $a1, $a2, $a3);
  }
  public static function post($a1, $a2, $a3 = null) {
    return static::__request('post', $a1, $a2, $a3);
  }
  public static function get($a1, $a2, $a3 = null) {
    return static::__request('get', $a1, $a2, $a3);
  }
  public static function put($a1, $a2, $a3 = null) {
    return static::__request('put', $a1, $a2, $a3);
  }
  public static function delete($a1, $a2, $a3 = null) {
    return static::__request('delete', $a1, $a2, $a3);
  }
  public static function path($a1, $a2, $a3 = null) {
    return static::__request('path', $a1, $a2, $a3);
  }
  static function createLink($a, $b) {
    return static::nav($a, $b, 'links');
  }
  static function href($a) {
    $ce = static::getInstance();
    $index = 'links';
    if(!isset($ce->data[$index][$a])) {
      return false;
    }
    $h = "href=\"" . $ce->data[$index][$a]['link'] . "\"";
    $h .= ($ce->data[$index][$a]['popy'] ? ' data-popy' : '');
    return $h;
  }
  static function link($a) {
    $ce = static::getInstance();
    $index = 'links';
    if(!isset($ce->data[$index][$a])) {
      return false;
    }
    return $ce->data[$index][$a]['link'];
  }
}
