<?php
final Class Route {
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
  private $current_route = array();
  private $merge_route   = array();
  private $middlewares   = array();
  public $middleware = null;

  private $configure = array();
  public $libs;

  public static function g() {
    return static::getInstance();
  }
  public static function getInstance() {
    if (null === static::$instance) {
      static::$instance = new static();
    }
    return static::$instance;
  }
  function __construct() {
    if (null === static::$instance) {
      $this->defaultConfig();
      static::$instance = $this;
      $this->libs = new stdClass();
      return $this;
    }
    return false;
#    return static::g();
  }
  public function __clone() {
    trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR);
  }
  static function hash($st) {
    return substr(md5($st), 0, 4);
  }
  public function attr($key, $val = -1) {
    if($val === -1) {
      if(isset($this->configure[$key])) {
        return $this->configure[$key];
      } else {
        return false;
      }
    } else {
      $this->configure[$key] = $val;
    }
    return $this;
  }
  public function import($file) {
    require_once($file);
  }
  private function defaultConfig() {
    $this->attr('core', dirname(__FILE__) . '/');
    $this->attr('root', dirname(__FILE__) . '/../');
    $this->attr('librarys', $this->attr('root') . 'app/librarys/');
    $this->attr('controllers', $this->attr('root') . 'app/controllers/');
    $this->attr('views', $this->attr('root') . 'app/views/');
  }
  public function getConfig() {
    return $this->configure;
  }
  public static function config($cb) {
    if(is_callable($cb)) {
      $cb(static::g());
    }
  }
  private function analyze_route() {
    $regx = '((sy-:empresa/?)?(:controlador(/:metodo)?)?)';
    $r = Route::getInstance()->route_process(null, $regx, array(
      'empresa'     => '[\w\-]{3,25}',
      'controlador' => '[\w\_\-]{3,15}',
      'metodo'      => '[^\/]+',
    ), true, false);
    $this->analyze = array(
      'empresa_slug'     => !empty($r['empresa']) ? $r['empresa'] : null,
      'controlador_link' => !empty($r['controlador']) ?  $r['controlador'] : null,
      'metodo_link'      => !empty($r['controlador']) ? (!empty($r['metodo']) ? $r['metodo'] : 'index') : null,
    );
    $this->web = array(
      'raiz_web'         => '/',
      'raiz_area'        => null,
      'raiz_controlador' => null,
      'raiz_metodo'      => $this->route['main'],
    );
  }
  static function group_options($g, $a, $b = -1) {
    $ls = static::data($g);
    if(empty($ls)) {
      $ls = array();
    }
    if($b === -1) {
      return array_key_exists($a, $ls) ? $ls[$a] : false;
    }
    if($a === null) {
      $ls[] = $b;
    } else {
      $ls[$a] = $b;
    }
    Route::data($g, $ls);
    return true;
  }
  public static function addBreadcrumb($t, $l) {
    return static::group_options('breadcrumbs', null, array('title' => $t, 'link' => $l));
  }
  public static function setTitle($t) {
    static::getInstance()->title = $t;
  }
  public static function getTitle() {
    return static::getInstance()->title;
  }
  public static function hasTitle() {
    return !empty(static::getInstance()->title);
  }
  public static function setDescription($t) {
    static::getInstance()->description = $t;
  }
  public static function getDescription() {
    return static::getInstance()->description;
  }
  public static function hasDescription() {
    return !empty(static::getInstance()->description);
  }
  static function setArea($a) {
    return Route::getInstance()->web['raiz_area'] = '/sy-' . $a . '/';
  }
  static function web($x) {
    return Route::getInstance()->web[$x];
  }
  static function setUser($x) {
    return Route::getInstance()->permissions = $x;
  }
  static function setURL($x) {
    return Route::getInstance()->http = $x;
  }
  public static function library($file, $name = null) {
    $name = is_null($name) ? $file : $name;
    $slug = strtolower($name);
    $file = strtolower($file);
    $file = strpos($file, '.php') === false ? static::g()->attr('core') . $file . '.php' : $file;
    static::import($file);
    if(class_exists($name)) {
      if(method_exists($name, 'importRoute')) {
        static::g()->libs->$slug = $name::importRoute(static::g());
      }
    }
  }
  public static function libraryOwn($file, $name = null) {
    $name = is_null($name) ? $file : $name;
    $slug = strtolower($name);
    $file = strtolower($file);
    $file = strpos($file, '.php') === false ? static::g()->attr('librarys') . $file . '.php' : $file;
    static::import($file);
    if(class_exists($name)) {
      if(method_exists($name, 'importRoute')) {
        static::g()->libs->$slug = $name::importRoute(static::g());
      }
    }
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
  static function current() {
    return Route::getInstance()->route['main'];
  }
  static function back() {
    return Route::getInstance()->route['back2'];
  }
  static function addQuery($q) {
    $ce = Route::getInstance();
    $ce->route['back2'] = $ce->route['back'];
    $ce->route['old'] = $ce->route['main'];
    $ce->route['query'][] = $q;
    $ce->route['back'] = $ce->route['old'] . (!empty($ce->route['query']) ? '?' . implode('&', $ce->route['query']) : '');
  }
  static function getData() {
    return Route::getInstance()->data;
  }
  static function data($n, $v = -1) {
    $ce = Route::getInstance();
    if($v !== -1) {
      return $ce->data[$n] = $v;
    }
    if(isset($ce->data[$n]) && is_callable($ce->data[$n])) {
      return $ce->data[$n]($ce);
    }
    if(!array_key_exists($n, $ce->data)) {
      return false;
    }
    return $ce->data[$n];
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
  public function who() {
    print_r($this->route);
    exit;
  }
  static function debug($x) {
    return static::getInstance()->debug = $x;;
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
  /* Methods */
  public static function else($cb) {
    if(is_callable($cb)) {
      $cb('Route:else');
      exit;
      return true;
    } elseif(function_exists($cb)) {
      ($cb)('Route:else');
      exit;
    }
  }
  private static function __request($method, $a1, $a2, $a3 = null) {
    $ce = Route::getInstance();
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
##      $callback($ce);
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
  public static function setError($x) {
    Route::getInstance()->errores[] = array(
      'type'    => 'danger',
      'message' => $x,
    );
  }
  /* Middleware */
  public static function add($callback) {
    $ce = Route::getInstance();
    if(is_callable($callback)) {
      $ce->middlewares[] = $callback;
    }
    return $ce;
  }
  public static function setAlert($x) {
    Route::getInstance()->errores[] = array(
      'type'    => 'warning',
      'message' => $x,
    );
  }
  public static function renderErrors() {
    $ls = Route::getInstance()->errores;
    Route::getInstance()->errores = null;
    $se = array();
    $html = '';
    if(!empty($ls)) {
      foreach($ls as $e) {
        if(!isset($se[$e['type']])) {
          $se[$e['type']] = array();
        }
        if(!empty($e['message'])) {
          if(!is_array($e['message'])) {
            $se[$e['type']][] = $e['message'];
          } else {
            foreach($e['message'] as $x) {
              $se[$e['type']][] = $x;
            }
          }
        }
      }
      foreach($se as $type => $messages) {
        $html .= "<div class=\"message is-" . $type . "\">";
        $html .= "<div class=\"message-header\">Debes seguir estas indicaciones:</div><div class=\"message-body\"><div class=\"content\">";
        
        $html .= "<ul style=\"margin-top:0\">";
        foreach($messages as $m) {
          $html .= "<li>" . $m . "</li>";
        }
        $html .= "</ul>";
        $html .= "</div></div>";
        $html .= "</div>";
      }
    }
    return $html;
  }
  public static function uri($path = null, $domain = null, $sub = null, $get = null) {
    $r = '';
    if(is_null($sub)) {
      if(is_null($domain)) {
        $r .= '';
      } else {
        if($sub != SUBDOMINIO_ACTUAL || $domain != DOMINIO_ACTUAL) {
          $r .= '//' . $domain;
        }
      }
    } else {
      if($sub != SUBDOMINIO_ACTUAL || $domain != DOMINIO_ACTUAL) {
        $r .= '//' . $sub . '.' . $domain;
      }
    }
    $r .= is_null($path) ? static::web('raiz_metodo') : $path;
    if(!is_null($get)) {
      $clear = strpos($get, '?') === 0;
      if($clear || empty($_GET)) {
        $r .= ($clear ? '' : '?') . $get;
      } else {
        $oldGet = $_GET;
        parse_str($get, $out);
        if(!empty($out)) {
          foreach($out as $k => $v) {
            unset($oldGet[$k]);
          }
        }
        $r .= '?' . http_build_query($oldGet) . '&' . $get;
      }
    }
    return $r;
  }
  public static function controller($file, $params = null) {
    if(strpos($file, '/') === false) {
      if(!empty(Route::getInstance()->web['controlador'])) {
        $file = dirname(Route::getInstance()->web['controlador']) . '/' . $file . '.php';
      } else {
        $file = defined('CONTROLLERS') ? CONTROLLERS . $file . '.php' : $file;
      }
    }
    if(!file_exists($file))  {
      _404('controlador-no-existe: ' . $file);
    }
    Route::getInstance()->web['raiz_controlador'] = '/' . implode('/', Route::getInstance()->route['tree']) . '/';
    if(!is_null($params)) {
      if(is_array($params)) {
        extract($params);
      } else {
        echo "no-es-array:" . $file;
        var_dump($params);
        exit;
      }
    }
    require($file);
    exit;
  }
  public static function theme($file, $params = null) {
    $params['VISTA'] = $file;
    $file = 'internal';
    echo static::part($file, $params);
    exit;
  }
  public static function view($file, $params = null) {
    return static::part($file, $params);
  }
  public static function part($file, $params = null) {
    if(!is_null($params)) {
      extract($params);
    }
    $file = strpos($file, '.php') === false ? static::g()->attr('views') . $file . '.php' : $file;
    if(!file_exists($file)) {
      _404('no-existe-theme:' . $file);
    }
    require($file);
  }
  static function render($x) {
    if(!ES_POPY) {
      $x->renderInPage();
      exit;
    } else {
      static::renderAssets();
      echo $x->render();
      exit;
    }
  }
  static function nav($a = null, $b = -1, $index = 'submenu') {
    if($a === null) {
      $ce = Route::getInstance();
      $rp = $ce->data[$index];
      $ce->data[$index] = array();
      return $rp;
    }
    if($b === -1) {
      return static::group_options($index, null, $b);
    }
    $slug = generar_slug($a);
    $key = static::$passVarNam . static::hash($a);
    $b = array(
      'nombre'   => trim($a, '&'),
      'link'     => is_callable($b) ? static::uri(Route::web('raiz_metodo'), DOMINIO_ACTUAL, SUBDOMINIO_ACTUAL, $key . '=' . $slug) : $b,
      'popy'     => (substr($a, -1) === '&'),
      'callback' => is_callable($b) ? $b : null,
    );
    if(is_callable($b['callback']) && !empty($_GET[$key]) && $slug == $_GET[$key]) {
      $error = null;
      $route = Route::getInstance()->route;
      if(class_exists('Popy')) {
        Popy::g()->currentRoute = $route;
      }
      Route::addQuery($key . '=' . $slug);
      Route::data($index, array());
      $e['call'] = $b['callback']($error, $route);
      if($e['call'] === false && is_null($error)) {
        $error = 'No se ha podido realizar la Acci&oacute;n';
      }
      exit;
      return true;
    }
    return static::group_options($index, $index == 'submenu' ? null : trim($a, '&'), $b);
  }
  static function createLink($a, $b) {
    return static::nav($a, $b, 'links');
  }
  static function href($a) {
    $ce = Route::getInstance();
    $index = 'links';
    if(!isset($ce->data[$index][$a])) {
      return false;
    }
    $h = "href=\"" . $ce->data[$index][$a]['link'] . "\"";
    $h .= ($ce->data[$index][$a]['popy'] ? ' data-popy' : '');
    return $h;
  }
  static function link($a) {
    $ce = Route::getInstance();
    $index = 'links';
    if(!isset($ce->data[$index][$a])) {
      return false;
    }
    return $ce->data[$index][$a]['link'];
  }
  static function byRequest($type = null) {
    if($type === null) {
      return $_SERVER['REQUEST_METHOD'];
    }
    return $_SERVER['REQUEST_METHOD'] == strtoupper(trim($type));
  }
  static function header($key, $val = -1) {
    if($val === -1) {
      $ls = getallheaders();
      return isset($ls[$key]) ? $ls[$key] : false;
    } else {
      header($key . ': ' . $val);
    }
  }
  static function input($name) {
    return isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
  }
  static function byAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
  }
  static function requestByPopy($cb = null, $ncb = null) {
    $r = static::byAjax() && !empty($_SERVER['HTTP_X_POPY']) && strtolower($_SERVER['HTTP_X_POPY']) == '9435';
    if($cb === null) {
      return $r;
    } elseif($r) {
      $cb();
    } elseif($ncb !== null) {
      $ncb();
    }
  }
  /* CSS & JS */
  static function addJS($a, $name = null) {
    $ls = Route::data('extra-js');
    if(!empty($ls)) {
      foreach($ls as $k => $v) {
        if($v == $a && is_numeric($k)) {
          return false;
        }
      }
    }
    if(!is_null($name) && !empty($ls[$name])) {
      if(!is_array($ls[$name])) {
        $ls[$name] = array($ls[$name]);
      }
      $ls[$name][] = $a;
      $a = $ls[$name];
    }
    return static::group_options('extra-js', $name, $a);
  }
  static function addCSS($a, $name = null) {
    $ls = Route::data('extra-css');
    if(!empty($ls)) {
      foreach($ls as $k => $v) {
        if($v == $a && is_numeric($k)) {
          return false;
        }
      }
    }
    return static::group_options('extra-css', $name, $a);
  }
  static function addMeta($a, $b) {
    return static::group_options('extra-meta', $a, $b);
  }
  static function renderAssets() {
    $r = '';
    if (!empty(static::data('extra-css'))) {
      foreach (static::data('extra-css') as $css) {
        $archivo = defined('RAIZ_WEB') && strpos($css, RAIZ_WEB) === 0 ? $css : RAIZ_WEB . "css/" . $css;
        $r .= "<link href=\"" . $archivo . "\" media=\"screen\" rel=\"Stylesheet\" type=\"text/css\" />\n";
      }
    }
    if (!empty(static::data('extra-js'))) {
      foreach (static::data('extra-js') as $ijs => $js) {
        if(is_array($js)) {
          $r .= "<script type=\"text/javascript\"> requireJS(['" . implode("','", $js) . "']" .  (!is_numeric($ijs) ? ",'" . $ijs . "'" : '') . "); </script>\n";
        } else {
          $r .= "<script type=\"text/javascript\"> requireJS('" . $js . "'" . (!is_numeric($ijs) ? ",'" . $ijs . "'" : '') . "); </script>\n";
        }
      }
    }
    return $r;
  }
  static function renderMeta() {
    $r = '';
    if(!empty(static::data('extra-meta'))) {
      foreach (static::data('extra-meta') as $key => $meta) {
        $r .= '<meta name="' . $key . '" content="' . $meta . '">';
      }
    }
    return $r;
  }
  static function renderNav() {
    $h = '<div style="text-align: right;">';
    if(!empty(static::data('submenu'))) {
      $h .= '<div class="columns">';
      foreach(static::nav() as $nav) {
        $h .= '<div class="column">';
        $h .= '<a href="' . $nav['link'] . '" ' . (!empty($nav['popy']) ? 'data-popy' : '') . ' class="button naranja">' . $nav['nombre'] . '</a>';
        $h .= '</div>';
      }
      $h .= '</div>';
    }
    $h .= '</div>';
    return $h;
  }

  /* Popy */
  static function go2Back() {
    if(static::requestByPopy()) {
      #static::Close();
      static::Refresh();
    } else {
      header('location: ' . Route::getInstance()->route['back2']);
      exit;
    }
  }
  static function Close($e = null) {
    if(static::requestByPopy()) {
      echo 'popy-close';
      exit;
    } else {
      static::go2Back();
    }
  }
  static function Refresh($e = null) {
    if(static::requestByPopy()) {
      echo 'popy-refresh ' . $e;
      exit;
    } else {
      header('location: .');
      exit;
    }
  }
  static function Error($e = null) {
    if(static::requestByPopy()) {
      echo 'popy-error ';
      echo is_array($e) ? json_encode($e) : $e;
      exit;
    } else {
      static::setError($e);
    }
  }
  static function Success($e = null) {
    if(static::requestByPopy()) {
      echo 'popy-ok ';
      echo is_array($e) ? json_encode($e) : $e;
      exit;
    } else {
      static::setSuccess($e);
    }
  }
  static function Action($e = null) {
    if(static::requestByPopy()) {
      echo 'popy-js ';
      echo is_array($e) ? json_encode($e) : $e;
      exit;
    }
  }
  static function Redirect($e = null) {
    if(static::requestByPopy()) {
      echo 'popy-location ' . $e;
      exit;
    } else {
      header('location: ' . $e);
      exit;
    }
  }
  static function response($code = 200, $message = '') {
    $http = array(
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        202 => 'HTTP/1.1 202 Accepted',
        203 => 'HTTP/1.1 203 Non-Authoritative Information',
        204 => 'HTTP/1.1 204 No Content',
        205 => 'HTTP/1.1 205 Reset Content',
        206 => 'HTTP/1.1 206 Partial Content',
        300 => 'HTTP/1.1 300 Multiple Choices',
        301 => 'HTTP/1.1 301 Moved Permanently',
        302 => 'HTTP/1.1 302 Found',
        303 => 'HTTP/1.1 303 See Other',
        304 => 'HTTP/1.1 304 Not Modified',
        305 => 'HTTP/1.1 305 Use Proxy',
        307 => 'HTTP/1.1 307 Temporary Redirect',
        400 => 'HTTP/1.1 400 Bad Request',
        401 => 'HTTP/1.1 401 Unauthorized',
        402 => 'HTTP/1.1 402 Payment Required',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        405 => 'HTTP/1.1 405 Method Not Allowed',
        406 => 'HTTP/1.1 406 Not Acceptable',
        407 => 'HTTP/1.1 407 Proxy Authentication Required',
        408 => 'HTTP/1.1 408 Request Time-out',
        409 => 'HTTP/1.1 409 Conflict',
        410 => 'HTTP/1.1 410 Gone',
        411 => 'HTTP/1.1 411 Length Required',
        412 => 'HTTP/1.1 412 Precondition Failed',
        413 => 'HTTP/1.1 413 Request Entity Too Large',
        414 => 'HTTP/1.1 414 Request-URI Too Large',
        415 => 'HTTP/1.1 415 Unsupported Media Type',
        416 => 'HTTP/1.1 416 Requested Range Not Satisfiable',
        417 => 'HTTP/1.1 417 Expectation Failed',
        500 => 'HTTP/1.1 500 Internal Server Error',
        501 => 'HTTP/1.1 501 Not Implemented',
        502 => 'HTTP/1.1 502 Bad Gateway',
        503 => 'HTTP/1.1 503 Service Unavailable',
        504 => 'HTTP/1.1 504 Gateway Time-out',
        505 => 'HTTP/1.1 505 HTTP Version Not Supported',
    );
    header($http[$code]);
    echo $message; 
    exit;
  }
  static function responseJSON($code = 200, $message = '') {
    static::header('Content-Type', 'application/json');
    return static::response($code, json_encode($message));
  }
}
