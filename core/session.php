<?php
class Session {
  private static $instance = null;
  protected static $SESSION_AGE = 1800;

  public static function importRoute($route) {
    return static::getInstance();
  }
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
    if (static::$instance === null) {
      static::$instance = $this;
      $this->init();
      return $this;
    }
    return static::g();
  }
  public static function write($key, $value) {
    if (!is_string($key)) {
      throw new InvalidArgumentTypeException('Session key must be string value');
    }
    $_SESSION[$key] = $value;
    self::_age();
    return $value;
  }
  public static function w($key, $value) {
    return self::write($key, $value);
  }
  public static function has($key) {
    return isset($_SESSION[$key]);
  }
  public static function h($key) {
    return isset($_SESSION[$key]);
  }
  public static function read($key, $child = false) {
    if (!is_string($key)) {
      throw new InvalidArgumentTypeException('Session key must be string value');
    }
    if (isset($_SESSION[$key])) {
      self::_age();
      if (false == $child) {
        return $_SESSION[$key];
      } else {
        if (isset($_SESSION[$key][$child])) {
          return $_SESSION[$key][$child];
        }
      }
    }
    return false;
  }
    
    public static function r($key, $child = false)
    {
        return self::read($key, $child);
    }
    
    public static function delete($key)
    {
        if ( !is_string($key) )
            throw new InvalidArgumentTypeException('Session key must be string value');
        unset($_SESSION[$key]);
        self::_age();
    }
    
    public static function d($key)
    {
        self::delete($key);
    }
    
    public static function dump()
    {
        echo nl2br(print_r($_SESSION));
    }

    
    private static function _age()
    {
        $last = isset($_SESSION['LAST_ACTIVE']) ? $_SESSION['LAST_ACTIVE'] : false ;
        
        if (false !== $last && (time() - $last > self::$SESSION_AGE))
        {
            self::destroy();
            throw new ExpiredSessionException();
        }
        $_SESSION['LAST_ACTIVE'] = time();
    }
    
    public static function params()
    {
        $r = array();
        if ( '' !== session_id() )
        {
            $r = session_get_cookie_params();
        }
        return $r;
    }
    
    public static function close()
    {
        if ( '' !== session_id() )
        {
            return session_write_close();
        }
        return true;
    }
    
    public static function commit()
    {
        return self::close();
    }
    
    public static function destroy()
    {
        if ( '' !== session_id() )
        {
            $_SESSION = array();

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            session_destroy();
        }
    }
    
    public static function init()
    {
        if (function_exists('session_status'))
        {
            if (session_status() == PHP_SESSION_DISABLED)
                throw new SessionDisabledException();
        }
        
        if ( '' === session_id() )
        {
            $secure = true;
            $httponly = true;

            if (ini_set('session.use_only_cookies', 1) === false) {
                throw new SessionUseOnlyCookiesException();
            }

            if (ini_set('session.cookie_httponly', 1) === false) {
                throw new SessionHttpOnlyCookieException();
            }

            $params = session_get_cookie_params();
            session_set_cookie_params($params['lifetime'],
                $params['path'], $params['domain'],
                $secure, $httponly
            );

            return session_start();
        }
        return session_regenerate_id(true);
    }
}
