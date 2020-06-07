<?php
function mover_archivo($temp, $destino){
  return move_uploaded_file($temp, $destino);
}
function sftp_move_to_remote($card, $local, $remote, $delete_local = false, &$error = null) {
  $n = get_card($card);
  $conn = ssh2_connect($n['host'], $n['port']);
  ssh2_auth_password($conn, $n['username'], $n['password']);
  $sftp = ssh2_sftp($conn);
  $upload = copy($local, "ssh2.sftp://$sftp" . $n['path'] . $remote);
  if(!empty($upload)) {
    if($delete_local) {
      @unlink($local);
    }
  }
  return $upload;
}
function ftp_move_to_remote($card, $local, $remote, $delete_local = false, &$error = null) {
  $n = get_card($card);
  //$conn_id = ftp_ssl_connect($n['host']);
  $conn_id = ftp_connect($n['host']);
  $login_result = @ftp_login($conn_id, $n['username'], $n['password']);
  if(empty($login_result)) {
    $error = 'login';
    return false;
  }
  ftp_pasv($conn_id, true);
  $remote = $n['path'] . $remote;
  //ftp_chdir($conn_id, $path);
  //if (ftp_put($conn_id, basename($remote), $local, FTP_BINARY)) {
  if (ftp_put($conn_id, $remote, $local, FTP_BINARY)) {
    $rp = true;
    if($delete_local) {
      @unlink($local);
    }
  } else {
    $error = $local . ' => ' . $remote;
    $rp = false;
  }
  ftp_close($conn_id);
  return $rp;
}
function get_file_name_rand($url = null) {
  $ext = !empty($file) ? strtolower(pathinfo(parse_url($file, PHP_URL_PATH), PATHINFO_EXTENSION)) : 'jpg';
  return md5($url). '.' . $ext;
  return time() . rand() . '.' . $ext;
}
function descargar_imagen_externa_al_servidor($url, $dir, $name = '') {
  $imagen = file_get_contents($url);
  $name = !empty($name) ? $name : get_file_name_rand($url);
  file_put_contents($dir . '/' . $name, $imagen);
  return $name;
}
function subir_imagen_externa_al_servidor($url, $dir, $name = '') {
  if(empty($url)) {
    return null;
  }
  $data = array(
    'url'  => $url,
    'dir'  => $dir,
    'name' => $name,
  );
  $url = HOSTIMG_PATH . 'subir-fichero';
  $rp = curly(CURLY_POST, $url, null, $data, null, $info);
  if(!empty($rp)) {
    $rp = json_decode($rp, true);
    if(!empty($rp['estado'])) {
      return $rp['archivo'];
    }
  }
  return false;
}
function subir_imagen_interna_al_servidor($imagen, $dir, $name = '', $is_temp = false) {
  if(!is_array($imagen)) {
    $imagen = array(
      'name'     => basename($imagen),
      'tmp_name' => $imagen,
    );
  }
  $move_temp = ABS_TEMPORALES . 'storage/' . $imagen['name'];
  if(!$is_temp) {
    return move_uploaded_file($imagen['tmp_name'], $move_temp);

  } elseif(!file_exists($move_temp)) {
    return false;
  }
  /*
  $name = empty($name) ? get_file_name_rand($imagen['name']) : $name;
  $cmd = "/usr/bin/scp " . $move_temp . " desarrollo@35.184.77.22:/var/www/html/genesis/public/" . $dir . "/" . $name;
  $rp = exec($cmd, $out);
  echo $cmd;
  var_dump($rp);
  var_dump($out);
  exit;
  return $name;
  */
  $url = "http://anccas.org/temp_storage/" . $imagen['name'];
  $r = subir_imagen_externa_al_servidor($url, $dir, $name);
  if(!empty($r)) {
    unlink($move_temp);
  }
  return $r;
}

function subir_multimedia($f, $dir) {
  $name    = get_file_name_rand($f['name']);
  $move_to = $dir . '/' . $name;
  if(move_uploaded_file($f['tmp_name'], $move_to)) {
    return $name;
  }
  return false;
}
