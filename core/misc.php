<?php
if (!function_exists('getallheaders')) {
    function getallheaders() {
    $headers = [];
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
    }
    return $headers;
    }
}
function toHashtag($txt) {
  return trim(str_replace('#','', str_replace(' ', '', ucwords(strtolower($txt)))));
}
function obtener_tipos($db, $grupo) {
  return $db->get("SELECT * FROM tipo WHERE tipo_id = :grupo", false, null, array(
    'grupo' => $grupo,
    ));
}

function obtener_persona_por_documento($db, $tipo, $numero) {
  return $db->get("
    SELECT * FROM persona 
    WHERE documento_tipo = :tipo 
    AND documento_numero = :numero", true,
    null, array(
      'tipo'   => $tipo,
      'numero' => $numero,
    ));
}

function array_contar_valores($entrada) {
  $salida = array();
  foreach($entrada as $a) {
    if(isset($salida[$a])){
       $salida[$a]++;
    }else{
       $salida[$a]=1;
    }
  }
  return  $salida;
}

function mayuscula($x) {
  return strtoupper($x);
}
function minuscula($x) {
  return strtolower($x);
}
function obtener_iniciales($n) {
  if(empty($n)) {
    return 'NaN';
  }
  $n = explode(' ', $n);
  if(count($n) > 1) {
    $n = array_map(function($n) { return substr($n, 0, 1); }, $n);
    $n = implode('', $n);
  } else {
    $n = implode('', $n);
    $n = substr($n, 0, 3);
  }
  return strtoupper($n);
}
function debug($x) {
  echo "DEBUG:";
  echo "<pre>";print_r($x);exit;
}
function codificar($t) {
  $t = is_array($t) ? '@' . json_encode($t) : $t;
  return base64_encode($t);
}
function decodificar($t) {
  $t = base64_decode($t);
  if(substr($t, 0, 1) == '@') {
    return json_decode(substr($t, 1),true);
  }
  return $t;
}
function create_link($query, $d = '', $subd = '') {
  $subdominio = $subd == '' ? '' : $subd . '.';
  $dominio = $d == '' ? DOMINIO_ACTUAL : $d;
  return '//' . $subdominio . $dominio . $query;
}
function is_empty($x, $y = null) {
  return !empty($x) ? $x : $y;
}
function unikid() {
  return time() . rand();
}
/*Funciones de Popy y Tablefy */
function popy_close($e = null) {
  echo 'popy-close';
  exit;
}
function popy_error($e = null) {
  echo 'popy-error ';
  echo is_array($e) ? json_encode($e) : $e;
  exit;
}
function popy_refresh($e = null) {
  echo 'popy-refresh ' . $e;
  exit;
}
function popy_ok($e = null) {
  echo 'popy-ok ' . $e;
  exit;
}
function popy_location($e = null) {
  echo 'popy-location ' . $e;
  exit;
}
function dinero($x) {
  setlocale(LC_MONETARY, 'es_PE');
  return money_format('%i', $x);
}
function formato_dinero($n, $decimales = 2) {
  return number_format((float)$n, $decimales, '.', '');
}
function formato_numero($n, $decimales = 2) {
  return number_format((float)$n, $decimales, '.', '');
}
function formato_moneda($x) {
  setlocale(LC_MONETARY, 'es_PE');
  return money_format('%i', $x);
}
function formato_moneda_texto($n, $moneda = 'PEN') {
  require_once(ABS_LIBRERIAS . 'dist/NumberToLetter.php');
  $n = number_format($n, 2, ',', '.');
  return (new NumberToLetter())->to_word($n, $moneda);
}
function crypto_rand_secure($min, $max) {
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}
function hexToRgb($hex, $alpha = false) {
   $hex      = str_replace('#', '', $hex);
   $length   = strlen($hex);
   $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
   $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
   $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
   if ( $alpha ) {
      $rgb['a'] = $alpha;
   }
   return $rgb;
}
function get_token($length = 5) {
  $token = "";
//  $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $codeAlphabet = "abcdefghjkmnpqrstuvwxyz";
  $codeAlphabet.= "123456789";
  $codeAlphabet.= "-";
  $max = strlen($codeAlphabet);
  for ($i=0; $i < $length; $i++) {
    $token .= $codeAlphabet[crypto_rand_secure(0, $max-1)];
  }
  return $token;
}
function add_prefix_keys($n, $p) {
  if(empty($n) || !is_array($n)) {
    return $n;
  }
  $rp = array();
  foreach($n as $k => $v) {
    $rp[$p . $k] = is_array($v) ? add_prefix_keys($v, $p) : $v;
  }
  return $rp;
}
function permission_upload_loong() {
  ini_set('post_max_size', '200M');
  ini_set('upload_max_filesize', '200M');
}
function data_to_tablefy($ls) {
  if(empty($ls)) {
    return null;
  }
  $rp = array();
  foreach($ls as $k => $n) {
    $n['popy_is_father'] = false;
    $n['popy_father_id'] = null;
    $rp[$n['id']] = $n;
  }
  foreach($rp as $k => $n) {
    $rp[$k]['popy_id'] = (int) $k;
    if(!empty($n['padre_id'])) {
      $rp[$k]['popy_father_id'] = (int) $n['padre_id'];
      if(!empty($rp[$n['padre_id']])) {
        $rp[$rp[$k]['padre_id']]['popy_is_father'] = true;
      }
    }
  }
  return array_values($rp);
}
function return_tablefy($data, $pagination = null) {
  return return_json($data);
}
function return_json($data, $pagination = null) {
  if(!is_null($pagination)) {
    $data = array(
      'data'       => $data,
      'pagination' => $pagination,
    );
  }
  echo json_encode($data);
  exit;
};
function array_group_by($a, $b) {
  $_temp = array();
  $f = array_shift($b);
  $indice = is_array($f) ? $f['key'] : $f;
  foreach($a as $n) {
    if(!is_array($f)) {
      $_temp[$n[$indice]][] = $n;
    } else {
      if(!isset($_temp[$n[$indice]])) {
        $_temp[$n[$indice]] = !empty($f['only']) ? array_only_keys($n, $f['only']) : array();
        $_temp[$n[$indice]]['children'] = array();
      }
      $_temp[$n[$indice]]['children'][] = array_delete_keys($n, $f['only']);
    }
  }
  if(!empty($b)) {
    foreach($_temp as $n) {
      if(!is_array($f)) {
        $_temp[$n[$indice]] = array_group_by($_temp[$n[$indice]], $b);
      } else {
        $_temp[$n[$indice]]['children'] = array_group_by($_temp[$n[$indice]]['children'], $b);
      }
    }
  }
  return $_temp;
}
function array_distinct($a, $b) {
  //$intersect = array_intersect_key($a, $b);
  return array_filter($a, function($v, $k) use($b) {
    return array_key_exists($k, $b) && $b[$k] != $v;
  }, ARRAY_FILTER_USE_BOTH);
}
function array_azar() {
 $r = func_get_args();
 return $r[array_rand($r)];
}
function array_delete_keys($n, $m) {
  foreach($m as $k) {
    unset($n[$k]);
  }
  return $n;
}
function array_only_keys($n, $m) {
  $rp = array();
  foreach($m as $k) {
    $rp[$k] = isset($n[$k]) ? $n[$k] : null;
  }
  unset($n);
  return $rp;
}
function array_keys_required($base, $keys, &$error = null) {
  if(!empty($base) && is_array($base)) {
    $keys = (array) $keys;
    foreach($keys as $k) {
      if(is_array($k)) {
        $rp = array_keys_required($base, $k, $error);
        if(!$rp) {
          return false;
        }
      } elseif(!isset($base[$k])) {
        $error = $k;
        return false;
      }
    }
    return true;
  }
  return false;
}
function process_join_arrays($base, $keys, &$error = '') {
  if(!array_keys_required($base, $keys, $error)) {
    return $error = null;
    $error = 'falta: ' . json_encode($error);
    return false;
  }
#  $keys = array_values($keys);
  $cantidad = count($base[reset($keys)]);
  foreach($keys as $k_r => $k) {
    if(is_array($k)) {
      $rp = process_join_arrays($base, $k, $error);
      if(!$rp) {
        return false;
      }
    } elseif($cantidad != count($base[$k])) {
      $error = 'Cantidad no uniforme' . $cantidad . '/' . count($base[$k]);
      return false;
    }
#    echo "<pre>BASE:";print_r($base);echo "</pre>";
    $base[$k] = array_values($base[$k]);
  }
  if(empty($cantidad)) {
    return null;
  }
  $rp = range(0, $cantidad - 1);
  $rp = array_map(function($n) { return []; }, $rp);
  foreach($keys as $k_r => $k) {
    for($i = 0; $i < $cantidad; $i++) {
      $rp[$i][$k_r] = $base[$k][$i];
    }
  }
  return $rp;
}

/*
function get_card($name, $tri = true) {
  $ferror = function($x) use($tri) {
    if($x) {
      throw new Exception($x);
    } else {
      return false;
    }
  };
  $name = strtolower($name);
  $file = ABS_CARDS . $name . '.php';
  if(file_exists($file)) {
    $n = null;
    require($file);
    if(!is_null($n) && is_array($n)) {
      if(isset($n['tipo'])) {
        if($n['tipo'] == 'db') {
          if(!array_keys_required($n, array('host','username','password','database'), $error)) {
            return $ferror("falta-" . $error);
          }
        } else if($n['tipo'] == 'mail') {
          if(!array_keys_required($n, array('name','username','password','from','smtp','imap'), $error)) {
            return $ferror("falta-" . $error);
          } else {
            if(!empty($n['smtp'])) {
              $ex = explode(':', $n['smtp']);
              $n['smtp_secure'] = $ex[0];
              $n['smtp_host']   = $ex[1];
              $n['smtp_port']   = $ex[2];
            }
            if(!empty($n['imap'])) {
              $ex = explode(':', $n['imap']);
              $n['imap_secure'] = $ex[0];
              $n['imap_host']   = $ex[1];
              $n['imap_port']   = $ex[2];
            }
          }
        } elseif($n['tipo'] == 'ftp') {
          if(!array_keys_required($n, array('host','username','password','path'), $error)) {
            return $ferror("falta-" . $error);
          }
        } elseif($n['tipo'] != 'all') {
          return $ferror("tipo invalid");
        }
      } else {
        return $ferror("n sin tipo");
      }
    } else {
      return $ferror("n not is array");
    }
  } else {
    $error = "No found: " . $file;
    return $ferror($error);
  }
  return $n;
}*/
function files_of_dir($dir) {
  $rp = array();
  if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
      while (($file = readdir($dh)) !== false) {
        if(!in_array($file, array('.','..'))) {
          $rp[] = $file;
        }
      }
      closedir($dh);
    }
  }
  return $rp;
}

function process_join_arrays_vold($base, $keys) {
  if(!array_keys_required($base, $keys, $error)) {
    return false;
  }
  $cantidad = count($base[$keys[0]]);
  foreach($keys as $k) {
    if($cantidad != count($base[$k])) {
      return false;
    }
    $base[$k] = array_values($base[$k]);
  }
  if(empty($cantidad)) {
    return null;
  }
  $rp = range(0, $cantidad - 1);
  $rp = array_map(function($n) { return []; }, $rp);
  foreach($keys as $k) {
    for($i = 0; $i < $cantidad; $i++) {
      $rp[$i][$k] = $base[$k][$i];
    }
  }
  return $rp;
}
function is_utf8($string) {
  return preg_match('%^(?:
        [\x09\x0A\x0D\x20-\x7E]            # ASCII
      | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
      |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
      | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
      |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
      |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
      | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
      |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
  )*$%xs', $string);
}
function _log() {
  if(func_num_args() < 2) {
    throw new Exception('Error Log');
  }
  $argx = func_get_args();
  $file = $argx[0];
  $x = array_map(function($n) { return is_array($n) ? json_encode($n) : $n; }, $argx);
  $x = implode("\n", $x);
  $out = date('d-m-Y h:i A') . ': ' . trim($x) . "\n";
  if(!is_null($file)) {
    file_put_contents($file, $out, FILE_APPEND | LOCK_EX);
  } else {
    echo $out;
  }
}
function merge_auditoria_registro($m) {
  global $USUARIO;
  if(empty($USUARIO) || empty($USUARIO['personal'])) {
    $USUARIO = array(
      'personal' => array(
        'id' => 1,
      )
    );
  }
  return array_merge($m, array(
    'created_by' => $USUARIO['personal']['id'], 
  ));
}
function strpos_array($haystack, $needles, &$key = null) {
  foreach($needles as $n) {
    if(($d = strpos($haystack, $n)) !== false) {
      $key = $n;
      return $d;
    } 
  }
  return false;
}
function parse_domain($co) {
  $subdomain = null;
  $domain    = null;
  $es_ip     = filter_var($co, FILTER_VALIDATE_IP);
  $especiales = array(
    'infobox.com.pe',
  );
  if(empty($co)) {
    return false;
  }
  if($d = strpos_array($co, $especiales, $key)) {
    $subdomain = substr($co, 0, $d - 1);
    $domain    = substr($co, $d);
  } else {
    $d = !$es_ip ? explode('.', $co) : $co;
    if(($c = count($d)) > 2) {
      $subdomain = implode('.', array_slice($d, 0, $c - 2));
      $domain    = implode('.', array_slice($d, $c - 2));
    } else {
      $domain = $co;
    }
  }
  return array(
    'completo'   => $co,
    'dominio'    => $domain,
    'subdominio' => $subdomain,
    'es_ip'      => $es_ip,
  );
}
function get_parse($desde, $hasta = NULL, $html, $all = false) {
        $parte = explode($desde, $html);
        unset($html);
        if(count($parte) > 1) {
                if(!empty($hasta)) {
                        if($all) {
                                $rt = array();
                                foreach ($parte as $p) {
                                        $p = explode($hasta, $p);
                                        $p = trim($p[0]);
                                        $rt[] = $p;
                                }
                                $retorno = $rt;
                        }
                        else {
                                $parte = explode($hasta, $parte[1]);
                                $parte = trim($parte[0]);
                                $retorno = $parte;
                        }
                } else {
                        if(!$all) {
                                $retorno = trim($parte[1]);
                        } else {
                                $retorno = NULL;
                        }
                }
        } else {
                $retorno = NULL;
        }

        return $retorno;
}
function reducir_texto($texto, $tamano) {
  if(isUTF8($texto)) {
    //$texto = utf8_decode($texto);
  }
  $texto = trim(str_replace(array("\n", "\r"), ' ', $texto));
  $len   = strlen($texto);
  if($len > $tamano) {
    $last_space = strrpos(substr($texto, 0, $tamano), ' ');
    return substr($texto, 0, $last_space) . '...';
  } else {
    return $texto;
  }
}
function corregir_codificacion_texto($texto){
  $retorno = null;
  if(!empty($texto)){
    $retorno = "[ERROR CODIFICACION]";
    $texto_old = $texto;
    $utf8 = mb_detect_encoding($texto, 'UTF-8', true);
    $latin = preg_match('/á|é|í|ó|ú|Á|É|Í|Ó|Ú/i', $texto);
    if($utf8){
      if($latin || preg_match('%(?:[\\xC2-\\xDF][\\x80-\\xBF]|\\xE0[\\xA0-\\xBF][\\x80-\\xBF]|[\\xE1-\\xEC\\xEE\\xEF][\\x80-\\xBF]{2}|\\xED[\\x80-\\x9F][\\x80-\\xBF]|\\xF0[\\x90-\\xBF][\\x80-\\xBF]{2}|[\\xF1-\\xF3][\\x80-\\xBF]{3}|\\xF4[\\x80-\\x8F][\\x80-\\xBF]{2} )+%xs', $texto)) {
        $retorno = utf8_encode($texto);
      } else {
        $retorno = $texto;
      }
    } else {
      $retorno = utf8_decode($texto);
    }
    return $retorno;
  }
  return $retorno;
}
function estado_toString($estados, $estado) {
  return isset($estados[$estado]) ? $estados[$estado] : "Desconocido (#" . $estado . ")";
}
function base64_url_encode($input) {
 return strtr(base64_encode($input), '+/=', '-_,');
}
function base64_url_decode($input) {
 return base64_decode(strtr($input, '-_,', '+/='));
}
function TimeToSec($time) {
  $sec = 0;
  foreach (array_reverse(explode(':', $time)) as $k => $v) $sec += pow(60, $k) * $v;
  return $sec;
}
function SecToTime($t,$f=':') {
  return sprintf("%02d%s%02d%s%02d", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
}
function fecha($x = null, $h = null, $relleno = 'Sin Fecha') {
  $formato = 'd/m/Y';
  return !empty($x) ? date($formato, strtotime($x)) . (is_null($h) ? '' : ' ' . hora($x)) : $relleno;
}
function hora($x = null, $formato = 'h:i A') {
  return !empty($x) ? date($formato, strtotime($x)) : 'SIN FECHA';
}
function fecha_larga($fecha = null, $hora = false) {
  global $DIAS, $MESES;
  $fecha = !empty($fecha) ? (is_numeric($fecha) ? $fecha : strtotime($fecha)) : time();
  if(empty($fecha)) {
      return NULl;
  }
  $hora = !empty($hora) ? ' a las ' . date("h:i A", $fecha) : '';
  return ucfirst($DIAS[date('w', $fecha)]) . ', ' . date('d', $fecha) . ' de ' . ucfirst($MESES[date('n', $fecha)-1]) . ' del ' . date('Y', $fecha) . $hora;
}
function tiempo_transcurrido($fecha = 'now') {
  global $DIAS, $MESES;
  if(empty($fecha)) {
    return '';
  }
  $fecha = !empty($fecha) ? (is_numeric($fecha) ? $fecha : strtotime($fecha)) : time();
  $ahora = time();
  if(empty($fecha)) {
      return NULl;
  }
  $MINUTO = 60;
  $HORA   = $MINUTO * 60;
  $DIA    = $HORA * 24;
  $MES    = $DIA * 30;
  $ANHO   = $MES * 12;

  $diferencia = $fecha - $ahora == 0 ? 1 : $fecha - $ahora;
  $signo      = $diferencia > 0;
  $prefijo    = $signo ? 'En ' : 'Hace ';
  $sufijo     = '';
  $diferencia = $signo ? $diferencia : $diferencia * -1;

  if($diferencia <= $MINUTO * 1) {
    $txt = 'instantes';
 // } elseif($diferencia <= $MINUTO * 9) {
//    $txt = 'breve momentos';
  } elseif($diferencia <= $HORA - 5 * $MINUTO) {
    $txt = round($diferencia/$MINUTO) . ' minutos';
  } elseif($diferencia <= $HORA + 5 * $MINUTO) {
    $txt = 'Una hora';
  } elseif($diferencia <= $HORA * 4) {
    $txt = round($diferencia/$HORA) . ' horas';
  } elseif($diferencia <= $HORA * 12) {
    $prefijo = '';
    $txt     = 'Hoy, ' . date('h:i a', $fecha);
  } elseif($diferencia <= $DIA + 6 * $HORA) {
    $prefijo = '';
    $sufijo  = ', ' . date('h:i a', $fecha);
    $txt     = $signo ? 'Mañana' : 'Ayer';
  } elseif($diferencia <= $DIA * 6) {
    $txt     = round($diferencia/$DIA) . ' días';
#  } elseif($diferencia <= $DIA * 6) {
#    $prefijo = $signo ? 'Este ' : '';
#    $sufijo  = $signo ? ''      : ' pasado';
#    $txt     = $DIAS[date('w', $fecha)];
  } elseif($diferencia <= $DIA * 8) {
    $txt     = 'una semana';
  } elseif($diferencia <= $MES - 5 * $DIA) {
    $prefijo = $signo ? 'El próximo ' : 'El pasado ';
    $sufijo  = '';
    $txt     = $DIAS[date('w', $fecha)] . ' ' . date('d', $fecha);
  } elseif($diferencia <= $MES + 5 * $DIA) {
    $txt = 'un mes';
  } elseif($diferencia <= $ANHO - 2 * $MES) {
    $txt = round($diferencia/$MES) . ' meses';
  } elseif($diferencia <= $ANHO + 2 * $MES) {
    $txt = 'un año';
  } else {
    $prefijo = '';
    $sufijo  = '';
    $txt     = ucfirst($DIAS[date('w', $fecha)]) . ' ' . date('d', $fecha) . ' de ' . $MESES[date('n', $fecha)-1] . ' del ' . date('Y', $fecha);
  }
  return $prefijo . $txt . $sufijo;
}
function defecto_variable(&$variable, $defecto){
  if(empty($variable)) {
    $variable = $defecto;
  }
}
function request_by_post() {
  return $_SERVER['REQUEST_METHOD'] == 'POST';
}
function marcar_imagen($archivo) {
  shell_exec("/usr/bin/composite -gravity NorthEast -geometry +10+10 " . IMAGENES . "golxgol_80x27.png " . $archivo . " " . $archivo);
}
function obtener_ids_resultados($rp){
  $rp = array_map(function($a){
    return $a['id'];
  }, $rp);
  return $rp;
}
function result_parse_to_options($rp, $key, $value) {
  $retorno = array();
  if(empty($rp)) {
    return $retorno;
  }
  foreach ($rp as $k => $v) {
    if(is_array($value)) {
      $concat = "";
      foreach ($value as $m) {
        $concat .= isset($v[$m]) ? $v[$m] : (array_key_exists($m, $v) ? '' : $m);
      }
      $val = $concat;
    } else {
      $val = isset($v[$value]) ? $v[$value] : null;
    }
    if(!empty($key) && isset($v[$key])){
      $retorno[$v[$key]] = $val;
    } else {
      $retorno[] = $v[$value];
    }
  }
  return $retorno;
}
function is_array_of($a, $c) {
  return is_array($a) && count($a) == $c;
}
function array_is_equal($a, $c, $e = false) {
  if(!is_array_of($c, count($c))) {
    return false;
  }
  if(empty($e)) {
    $a = array_values($a);
    $c = array_values($c);
  }
  foreach($c as $k => $v) {
    if(!isset($a[$k]) || $v != $a[$k]) {
      return false;
    }
  }
  return true;
}
function RequestStatus($num, $message = null) {
    $http = array(
        100 => 'HTTP/1.1 100 Continue',
        101 => 'HTTP/1.1 101 Switching Protocols',
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
    header($http[$num]);
    return array(
        'code'    => $num,
        'error'   => $http[$num],
        'message' => $message,
    );
}
function _404($ERROR = null) {
  if(defined('FILE_ERROR') && !empty(FILE_ERROR)) {
    header('HTTP/1.0 404 Not Found');
    include(FILE_ERROR);
    exit;
  } elseif(defined('DEVEL_MODE') && DEVEL_MODE) {
    echo "ERROR:";
    var_dump($ERROR);
    exit;
  } else {
    header('HTTP/1.0 404 Not Found');
    echo "ERROR";
    echo $ERROR;
    exit;
  }
}


function _403() {
  header('HTTP/1.0 403 Forbidden');
  echo "<h1>403 - Forbidden</h1>";
  exit();
}


function _401() {
  header('HTTP/1.0 401 Unauthorized');
  echo "<h1>401 - Unauthorized</h1>";
  exit();
}


function isUTF8($str) {
    $c=0; $b=0; 
    $bits=0; 
    $len=strlen($str); 
    for($i=0; $i<$len; $i++){ 
        $c=ord($str[$i]); 
        if ($c >= 128) { 
            if(($c >= 254)) return false; 
            elseif($c >= 252) $bits=6; 
            elseif($c >= 248) $bits=5; 
            elseif($c >= 240) $bits=4; 
            elseif($c >= 224) $bits=3; 
            elseif($c >= 192) $bits=2; 
            else return false; 
            if(($i+$bits) > $len) return false; 
            while($bits > 1){ 
                $i++; 
                $b=ord($str[$i]); 
                if($b < 128 || $b > 191) return false; 
                $bits--; 
            }
        }
    }
    return true;
}
function parse_slug($slug) {
  $r = array();
  if(is_numeric($slug)) {
    $r['id']   = $slug;
    $r['slug'] = '';
  } else {
    if(preg_match("/\A([0-9]+?)-(.+?)\Z/", $slug, $m)) {
      $r['id']   = $m[1];
      $r['slug'] = $m[2];
    } else {
      _404();
    }
  }
  
  return $r;
}
function exec_no_conflict($file) {
  $ocmd = "nohup php -q " . $file . " > /var/www/html/genesis/core/logs/no_conflict_" . generar_slug($file) . ".log &";
  $cmd = str_replace('"', '\"', $ocmd);
   exec("ps -ef | grep \"{$cmd}\"", $ps);
   if(!empty($ps)) {
     $ps = array_filter($ps, function($c) use($ocmd) {
       return strpos($c, "00:00:00 " . $ocmd) !== false;
     });
     if(!empty($ps)) {
       return false;
     }
   }
   exec($cmd);
   return true;
}
function quitar_tildes($str){
  return str_replace(
    array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'),
    array('a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'),
    strtolower(trim($str))
  );
}
function generar_slug($str, $mono = false) {
  $a = quitar_tildes($str);
  $stopwords = implode("|", get_stopwords());
  // Removemos los stopwords"
  // Cambiamos todo lo que no sean letras o números por guiones
  if(!$mono) {
    $a = preg_replace("/\b(${stopwords})\b/", "", $a);
    $a = preg_replace('/[^A-Za-z-\d]+/', '-', $a);
  } else {
    $a = preg_replace('/[^A-Za-z-\d]+/', '-', $a);
  }
  // Finalmente, removemos guiones de más. e.g. "hola--mundo" por "hola-mundo"
  $a = preg_replace("/(\-)+/", '-', $a);
  
  // No puede empezar (ni terminar) tampoco con un guión
  $a = preg_replace("/\A-/", '', $a);
  $a = preg_replace("/-\Z/", '', $a);
  
  return $a;
}
function standard_string($string) {
  if(is_array($string)) {
    return array_map('standard_string', $string);
  }

  //$string = (isUTF8($string) ? utf8_decode($string) : $string);
  $parsed = trim($string);
  $parsed = str_replace("\n", ' ', $parsed);
  $parsed = preg_replace("/[[:blank:]]+/", ' ', $parsed);
  $parsed = strtolower($parsed);
  return $parsed;

  $parsed = str_replace(
    array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
    array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
    $parsed
  );

  $parsed = str_replace(
    array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
    array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
    $parsed
  );

  $parsed = str_replace(
    array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
    array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
    $parsed
  );

  $parsed = str_replace(
    array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
    array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
    $parsed
  );

  $parsed = str_replace(
    array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
    array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
    $parsed
  );

  $parsed = str_replace(
    array('ñ', 'Ñ', 'ç', 'Ç'),
    array('n', 'N', 'c', 'C',),
    $parsed
  );

  $parsed = preg_replace("/[@\,]+/", '', $parsed);
  $parsed = preg_replace("/[@\,]+/", '', $parsed);
  /*$parsed = preg_replace("/[áàäâªÁÀÂÄ]+/", 'a', $parsed);
  $parsed = preg_replace("/[éèëêÉÈÊË]+/", 'e', $parsed);
  $parsed = preg_replace("/[íìïîÍÌÏÎ]+/", 'i', $parsed);
  $parsed = preg_replace("/[óòöôÓÒÖÔ]+/", 'o', $parsed);
  $parsed = preg_replace("/[úùüûÚÙÛÜ]+/", 'u', $parsed);
  $parsed = preg_replace("/[ñÑ]+/", 'n', $parsed);
  $parsed = preg_replace("/[çÇ]+/", 'c', $parsed);*/
  $parsed = preg_replace("/[[:blank:]]+/", ' ', $parsed);
  $parsed = strtolower($parsed);
  return $parsed;
}

function get_stopwords(){
  return array("a", "al", "algo", "algun", "alguna", "algunas", "algunas", "alguno", "algunos", "algunos", "ambos", "ante", "ante", "antes", "antes", "aquel", "aquellas", "aquellos", "aqui", "arriba", "atras", "bajo", "bastante", "bien", "cada", "cierta", "ciertas", "cierto", "ciertos", "como", "como", "con", "con", "conseguimos", "conseguir", "consigo", "consigue", "consiguen", "consigues", "contra", "cual", "cual", "cuando", "cuando", "de", "del", "dentro", "desde", "donde", "donde", "dos", "durante", "e", "el", "el", "el", "ella", "ellas", "ellas", "ellos", "ellos", "empleais", "empleamos", "emplean", "emplear", "empleas", "empleo", "en", "en", "encima", "entonces", "entre", "entre", "era", "era", "erais", "eramos", "eramos", "eran", "eran", "eras", "eras", "eres", "eres", "es", "es", "es", "esa", "esas", "ese", "eso", "esos", "esta", "esta", "esta", "esta", "estaba", "estaba", "estaba", "estabais", "estabamos", "estaban", "estabas", "estad", "estada", "estadas", "estado", "estado", "estado", "estados", "estados", "estais", "estais", "estamos", "estamos", "estamos", "estan", "estan", "estan", "estando", "estar", "estara", "estaran", "estaras", "estare", "estareis", "estaremos", "estaria", "estariais", "estariamos", "estarian", "estarias", "estas", "estas", "este", "este", "esteis", "estemos", "esten", "estes", "esto", "estos", "estoy", "estoy", "estuve", "estuviera", "estuvierais", "estuvieramos", "estuvieran", "estuvieras", "estuvieron", "estuviese", "estuvieseis", "estuviesemos", "estuviesen", "estuvieses", "estuvimos", "estuviste", "estuvisteis", "estuvo", "fin", "fue", "fue", "fue", "fuera", "fuerais", "fueramos", "fueran", "fueras", "fueron", "fueron", "fueron", "fuese", "fueseis", "fuesemos", "fuesen", "fueses", "fui", "fui", "fuimos", "fuimos", "fuiste", "fuisteis", "gueno", "ha", "ha", "ha", "habeis", "haber", "habia", "habia", "habiais", "habiamos", "habian", "habias", "habida", "habidas", "habido", "habidos", "habiendo", "habra", "habran", "habras", "habre", "habreis", "habremos", "habria", "habriais", "habriamos", "habrian", "habrias", "hace", "haceis", "hacemos", "hacen", "hacer", "haces", "hago", "han", "han", "has", "hasta", "hay", "haya", "hayais", "hayamos", "hayan", "hayas", "he", "hemos", "hube", "hubiera", "hubierais", "hubieramos", "hubieran", "hubieras", "hubieron", "hubiese", "hubieseis", "hubiesemos", "hubiesen", "hubieses", "hubimos", "hubiste", "hubisteis", "hubo", "incluso", "intenta", "intentais", "intentamos", "intentan", "intentar", "intentas", "intento", "ir", "la", "la", "largo", "las", "las", "le", "les", "lo", "lo", "los", "los", "mas", "me", "mi", "mi", "mia", "mias", "mientras", "mio", "mio", "mios", "mis", "modo", "mucho", "muchos", "muchos", "muy", "muy", "nada", "ni", "no", "nos", "nos", "nosotras", "nosotros", "nosotros", "nuestra", "nuestras", "nuestro", "nuestros", "o", "os", "otra", "otras", "otro", "otro", "otros", "para", "para", "pero", "pero", "poco", "podeis", "podemos", "poder", "podria", "podriais", "podriamos", "podrian", "podrias", "por", "por", "porque", "porque", "por que", "puede", "pueden", "puedo", "que", "que", "quien", "quien", "quienes", "sabe", "sabeis", "sabemos", "saben", "saber", "sabes", "se", "sea", "sea", "seais", "seamos", "sean", "seas", "ser", "ser", "sera", "seran", "seras", "sere", "sereis", "seremos", "seria", "seriais", "seriamos", "serian", "serias", "si", "si", "sido", "siendo", "siendo", "sin", "sin", "sobre", "sobre", "sois", "sois", "solamente", "solo", "somos", "somos", "son", "son", "soy", "soy", "su", "su", "sus", "sus", "suya", "suyas", "suyo", "suyos", "tambien", "tambien", "tanto", "te", "tendra", "tendran", "tendras", "tendre", "tendreis", "tendremos", "tendria", "tendriais", "tendriamos", "tendrian", "tendrias", "tened", "teneis", "teneis", "tenemos", "tenemos", "tener", "tenga", "tengais", "tengamos", "tengan", "tengas", "tengo", "tengo", "tenia", "teniais", "teniamos", "tenian", "tenias", "tenida", "tenidas", "tenido", "tenidos", "teniendo", "ti", "tiempo", "tiene", "tiene", "tiene", "tienen", "tienen", "tienes", "todo", "todo", "todos", "trabaja", "trabajais", "trabajamos", "trabajan", "trabajar", "trabajas", "trabajo", "tras", "tu", "tu", "tus", "tuve", "tuviera", "tuvierais", "tuvieramos", "tuvieran", "tuvieras", "tuvieron", "tuviese", "tuvieseis", "tuviesemos", "tuviesen", "tuvieses", "tuvimos", "tuviste", "tuvisteis", "tuvo", "tuya", "tuyas", "tuyo", "tuyo", "tuyos", "ultimo", "un", "un", "una", "una", "unas", "uno", "uno", "unos", "unos", "usa", "usais", "usamos", "usan", "usar", "usas", "uso", "va", "vais", "valor", "vamos", "van", "vaya", "verdad", "verdadera", "verdadero", "vosotras", "vosotras", "vosotros", "vosotros", "voy", "vuestra", "vuestras", "vuestro", "vuestros", "y", "ya", "yo");
}
