<?php
namespace Raven\Http;

use Raven\Framework\Application;
use Raven\Http\Request;
use Raven\Framework\Controller;
use Raven\Http\Response;

Class Route {

  private $data = array();
  public  $analyze = null;
  public  $route = null;
  public $web = array();
  private static $instance;
  public $current_route = array();
  public $merge_route   = array();
  private $middlewares   = array();
  public $middleware = null;

  public $libs;
  public $request;

  public static function instance() {
    return self::$instance; 
  }
  public function __construct(Request $request, String $uri = null) {
    if(static::$instance instanceof Route) {
      return static::$instance;
    }
    static::$instance = $this;
    $this->request = $request;
    $this->recogniseUri($uri);
  }

  public static function middleware($callback) {
    $ce = static::instance();
    if(is_callable($callback)) {
      $ce->middlewares[] = $callback;
    }
    return $ce;
  }
  private function recogniseUri($uri = null) {
    if(is_null($uri)) {
      $uri = $this->request->path();
    }
    $this->route = array(
      'main'  => '/' . $uri,
      'path'  => $uri,
      'old'   => null,
      'query' => array(),
      'back'  => null,
      'back2' => null,
      'tree'  => array(),
    ); 
    return $this;
  }
  private function route($route, $regexps = false, $started = false, $delete = true) {
    $query = $this->route['path'];
    $query = trim($query, '/');
    $e = $this->route_process($query, $route, $regexps, $started, $delete, $cantidad);
    if(!empty($e)) {
      $sub = $cantidad != 0 ? $e[0] : $route;
      $this->route['old'] = '/' . implode('/', $this->route['tree']);
      $this->route['back'] = $this->route['old'] . (!empty($this->route['query']) ? '?' . implode('&', $this->route['query']) : '');
      $this->route['tree'][] = trim($sub, '/');
      $query =  $delete ? substr($query, strlen($sub)) : $query;
      $this->route['path'] = $query;
    }
    return $e;
  }
  private function callbackController($action) {
    if(!preg_match('/^(?<controller>[\w]+)\@(?<method>[\w]+)$/i', $action, $match)) {
      exit('formato invalido');
    }
    $file = Application::instance()->path() . '/Http/Controllers/' . $match['controller'] . '.php';
    if(!file_exists($file)) {
      exit('controller file no existe:' . $file);
    }
    //require_once $file;
    $controlador = '\\App\\Http\\Controllers\\' . $match['controller'];
    if(class_exists($controlador, true)) {
      $obj = new $controlador();
      //var_dump(method_exists($obj, $match['method']));exit;
      $rp = $obj->{$match['method']}($this->request);
      echo $rp;
      exit;
      //return call_user_func_array($this->$name, $args);
    } else {
      exit('no es callable:' . $controlador);
    }
  }
  private function route_process($query, $route, $regexps = false, $started = false, $delete = true, &$cantidad = 0) {
    $route = trim($route,'/');
    $route = str_replace('/', '\/', $route);
    $expresion_regular = preg_replace_callback("/\{(?<id>[\w_]+)\}/", function($n) use($regexps) {
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
  public static function uri($path = null, $query = null) {
    $currentQuery = static::instance()->request->query->all();
    $r = '';
    if(!is_null($query)) {
      $clear = strpos($query, '?') === 0;
      if($clear || empty($currentQuery)) {
        $r .= ($clear ? '' : '?') . $query;
      } else {
        $oldGet = $currentQuery;
        parse_str($query, $out);
        unset($oldGet['p']);
        if(!empty($out)) {
          foreach($out as $k => $v) {
            unset($oldGet[$k]);
          }
        }
        $r .= '?' . http_build_query($oldGet) . '&' . $query;
      }
    }
    return $r;
  }
  private function __request($method, $params) {
    if($this->request->method() != strtoupper($method)) {
      return $this;
    }
    if(count($params) == 3) {
      list($regex, $eq, $callback) = $params;
    } elseif(count($params) == 2) {
      list($regex, $callback) = $params;
    } else {
      exit('error cantidad parametros');
    }
    $r = $this->route($regex, $eq ?? null, $method == 'path', true);
    
    if($r) {
      $this->callbackController($callback);
      var_dump(array($regex, $r, $callback));exit;
      $back = null;
      foreach($this->middlewares as $m) {
        $back = ($m)($this, $back);
      }
      $this->middleware = $back;
      if(is_array($this->current_route)) {
        $this->merge_route = array_merge($this->merge_route, $this->current_route);
      }
      $this->current_route = $r;
      \Closure::bind($callback, $this)($this->request, new Response);
      exit();
    } else {
      $this->middleware = null;
    }
    return $this;
  }
  public static function any(...$agvs) {
    return static::instance()->__request('any', $agvs);
  }
  public static function post(...$agvs) {
    return static::instance()->__request('post', $agvs);
  }
  public static function get(...$agvs) {
    return static::instance()->__request('get', $agvs);
  }
  public static function put(...$agvs) {
    return static::instance()->__request('put', $agvs);
  }
  public static function delete(...$agvs) {
    return static::instance()->__request('delete', $agvs);
  }
  public static function path(...$agvs) {
    return static::instance()->__request('path', $agvs);
  }
  static function createLink($a, $b) {
    return static::nav($a, $b, 'links');
  }
  static function view(...$agvs) {
    return static::instance();
  }
  static function match(...$agvs) {
    return static::instance();
  }
  static function resource(String $model, String $controller) {
    $ce = static::instance();
    $myStruct = array(
      'index'   => '',
      'create'  => 'create',
      'store'   => '',
      'show'    => '',
      'edit'    => 'edit',
      'update'  => '',
      'destroy' => '',
    );
    $dir = $model;
    $ce->get($dir . '/' . $myStruct['index'], $controller . '@index');
    $ce->get($dir . '/' . $myStruct['create'], $controller . '@create');
    $ce->post($dir . '/' . $myStruct['store'], $controller . '@store');
    $ce->get($dir . '/{' . $model . '}/' . $myStruct['show'], $controller . '@show');
    $ce->get($dir . '/{' . $model . '}/' . $myStruct['edit'], $controller . '@edit');
    $ce->put($dir . '/{' . $model . '}/' . $myStruct['update'], $controller . '@update');
    $ce->delete($dir . '/{' . $model . '}/' . $myStruct['destroy'], $controller . '@destroy');
    return $ce;
  }
  static function href($a) {
    $index = 'links';
    if(!isset($this->data[$index][$a])) {
      return false;
    }
    $h = "href=\"" . $this->data[$index][$a]['link'] . "\"";
    $h .= ($this->data[$index][$a]['popy'] ? ' data-popy' : '');
    return $h;
  }
  static function link($a) {
    $index = 'links';
    if(!isset($this->data[$index][$a])) {
      return false;
    }
    return $this->data[$index][$a]['link'];
  }
}
