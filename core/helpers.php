<?php
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