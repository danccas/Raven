<?php
define('CURLY_POST', 'post');
define('CURLY_GET', 'get');
function curly($tipo, &$url, $header = null, $data = null, $cookie = null, &$info = null, $curl = null, $use_tor = false) {
  $data = is_array($data) ? http_build_query($data) : $data;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
#  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
#  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
  curl_setopt($ch, CURLOPT_ENCODING, '');
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36');
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($tipo));

  if($tipo === CURLY_POST) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  } else {
    $url .= !empty($data) ? '?' . $data : '';
  }
  curl_setopt($ch, CURLOPT_URL, $url);
  if(!is_null($header)) {
    //$header['Accept'] = 'TODO';
    array_walk($header, function(&$n, $k) { $n = $k . ": " . $n;  });
    $header = array_values($header);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  }
  if(!is_null($curl) && is_array($curl)) {
    curl_setopt_array($ch, $curl);
  }
  if(!is_null($cookie)) {
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
  }
  // Proxy Tor
  if(!empty($use_tor)) {
    curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1:9050");
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
  }
  $response = curl_exec($ch);
  $info = curl_getinfo($ch);
  $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
  $header   = substr($response, 0, $header_size);
  $response = substr($response, $header_size);
  //$info['header_response'] = http_parse_headers($header);
  //$info['header'] = curl_getinfo($ch, CURLINFO_HEADER_OUT);
  //$info['header'] .= "";;
  $url  = $info['url'];
  //curl_close($ch);
  return $response;
}
function ajax($p) {
  if(!array_keys_required($p, array('type','url'))) {
    throw new Exception('required params');
  }
  $header  = isset($p['header'])  ? $p['header']  : null;
  $data    = isset($p['data'])    ? $p['data']    : null;
  $cookie  = isset($p['cookie'])  ? $p['cookie']  : null;
  $curl    = isset($p['curl'])    ? $p['curl']    : null;
  $tor     = isset($p['tor'])     ? $p['tor']     : false;
  $type    = isset($p['type'])    ? strtolower($p['type']) : 'get';
  return curly($type, $p['url'], $header, $data, $cookie, $info, $curl, $tor);
}
function soap_request($url, $data) {
  $xml = '';
  $xml .= '<?xml version="1.0" encoding="ISO-8859-1" standalone="no" ?>';
  $xml .= '<soapenv:Envelope ';
    $xml .= 'xmlns:ser="http://service.sunat.gob.pe" ';
    $xml .= 'xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" ';
    $xml .= 'xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"';
    $xml .= '>';
    if(!empty($data)) {
      $xml .= array_to_xml($data);
    }
  $xml .= '</soapenv:Envelope>';

  $header = array();
  $header['Content-Type'] = "text/xml; charset=utf-8";
  $header['Accept'] = "text/xml";
  $header['Cache-Control'] = "no-cache";
  $header['Pragma'] = "no-cache";
  $header['SOAPAction'] = "sendBill";
  return curly(CURLY_POST, $url, $header, $xml, null, $info);
}
