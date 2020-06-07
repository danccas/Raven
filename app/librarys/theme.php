<?php
final Class Theme {
  private static $passVarNam  = '_tym';
  private $data = array();
  private static $instance;
  public $title = null;
  public $description;

  public static function addBreadcrumb($t, $l) {
    return Theme::group_options('breadcrumbs', null, array('title' => $t, 'link' => $l));
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
  public static function controller($file, $params = null) {
    if(strpos($file, '/') === false) {
      if(!empty(Route::getInstance()->web['controlador'])) {
        $file = dirname(Route::getInstance()->web['controlador']) . '/' . $file;
      } else {
        $file = ABS_CONTROLADORES . $file;
      }
    }
    if(!file_exists($file))  {
      _404('controlador-no-existe: ' . $file);
    }
#    Route::getInstance()->web['raiz_controlador'] = '/' . implode('/', Route::getInstance()->route['tree']) . '/';
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
  public static function load($file, $params = null) {
    static::part($file, $params);
    exit;
  }
  public static function part($file, $params = null) {
    if(!is_null($params)) {
      extract($params);
    }
    if(strpos($file, '/') === false) {
      $file = dirname(Route::getInstance()->web['controlador']) . '/themes/' . $file;
    }
    if(!file_exists($file)) {
      _404('no-existe-theme:' . $file);
    }
    require($file);
  }
  public static function header() {
    if(!ES_POPY) {
      require_once(ABS_PLANTILLAS . 'cabecera_cpanel.php');
    }
  }
  public static function footer() {
    if(!ES_POPY) {
    require_once(ABS_PLANTILLAS . 'pie_cpanel.php');
    }
  }
  public static function blank($file, $params = null) {
    return Theme::load($file, $params);
  }
  static function getData() {
    return Theme::getInstance()->data;
  }
  static function data($n, $v = -1) {
    $ce = Theme::getInstance();
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
  static function JS($a, $name = null) {
    $ls = Theme::data('extra-js');
    foreach($ls as $k => $v) {
      if($v == $a && is_numeric($k)) {
        return false;
      }
    }
    if(!is_null($name) && !empty($ls[$name])) {
      if(!is_array($ls[$name])) {
        $ls[$name] = array($ls[$name]);
      }
      $ls[$name][] = $a;
      $a = $ls[$name];
    }
    return Theme::group_options('extra-js', $name, $a);
  }
  static function CSS($a, $name = null) {
    $ls = Theme::data('extra-css');
    foreach($ls as $k => $v) {
      if($v == $a && is_numeric($k)) {
        return false;
      }
    }
    return Theme::group_options('extra-css', $name, $a);
  }
  static function Manual($a, $b = -1) {
    return Theme::group_options('manuales', $a, $b);
  }
  static function meta($a, $b = -1) {
    return Theme::group_options('meta', $a, $b);
  }
  static function hash($st) {
    return substr(md5($st), 0, 4);
  }
  static function renderAssets() {
    include(ABS_HELPERS . 'styles.php');
  }
  static function render($x) {
    if(!ES_POPY) {
      if(!is_null($x->title)) {
        static::setTitle($x->title);
        $x->title = null;
      }
      $x->renderInPage();
      exit;
    } else {
      static::renderAssets();
      echo $x->render();
      exit;
    }
  }
  static function submenu($a = null, $b = -1) {
    $index = 'submenu';
    if($a === null) {
      $ce = Theme::getInstance();
      $rp = $ce->data[$index];
      $ce->data[$index] = array();
      return $rp;
    }
    if($b === -1) {
      return Theme::group_options($index, null, $b);
    }
    $slug = generar_slug($a);
    //Route::link(null, DOMINIO_ACTUAL, SUBDOMINIO_ACTUAL, static::$passVarNam . '=' . $slug);
    $key = static::$passVarNam . static::hash($a);
    $b = array(
      'nombre'   => trim($a, '&'),
      'link'     => is_callable($b) ? Route::link(Route::web('raiz_metodo'), DOMINIO_ACTUAL, SUBDOMINIO_ACTUAL, $key . '=' . $slug) : $b,
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
      Theme::data($index, array());
      $e['call'] = $b['callback']($error, $route);
      if($e['call'] === false && is_null($error)) {
        $error = 'No se ha podido realizar la Acci&oacute;n';
      }
      exit;
      return true;
    }
    return Theme::group_options($index, null, $b);
  }
  static function renderMenu() {
  }
  static function group_options($g, $a, $b = -1) {
    $ls = Theme::data($g);
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
    Theme::data($g, $ls);
    return true;
  }
  public static function __callStatic($method, $params) {
    if(count($params) > 1) {
      throw new Exception("method invalid params");
    }
    $val = !empty($params) ? $params[0] : -1;
    return Theme::data('method', $val);
  }
}
