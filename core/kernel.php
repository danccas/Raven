<?php
final Class Kernel {
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

  private $configure = [];
  public $libs;

  private $request;

  public function handle(Request $request)
  {
    try {
        return $this->request = $request;
    } finally {
    }
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
  public function routing() {
    
  }
}
