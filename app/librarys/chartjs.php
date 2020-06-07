<?php
class Chartjs {
  static function line($listado, $labels) {
    $estadisticas = array();
    $_est = array();
    array_walk($labels, function(&$v, $k) { $v['tipo'] = $k; });
    $default = array_map(function($n) { return 0; }, $labels);
    if(!empty($listado) && is_array($listado)) {
      foreach($listado as $m) {
        if(!isset($estadisticas[$m['fecha']])) {
          $estadisticas[$m['fecha']] = $default;
        }
        $estadisticas[$m['fecha']][$m['tipo']] = $m['cantidad'];
      }
    }
#debug($estadisticas);
    foreach($estadisticas as $f => $j) {
      foreach($j as $t => $m) {
        $_est[$t][] = $m;
      }
    }
#debug($_est);
#debug($labels);
    $tiempos = array_keys($estadisticas);
    $labels = array_map(function($n) use ($_est) {
      return array(
        'label'           => $n['nombre'],
        'fill'            => false,
        'backgroundColor' => $n['color'],
        'borderColor'     => $n['color'],
        'data'            => !empty($_est[$n['tipo']]) ? $_est[$n['tipo']] : array(),
      );
    }, $labels);
#debug($labels);
    $labels = array_values($labels);
    return array(
      'type'     => 'line',
      'labels'   => $tiempos,
      'datasets' => $labels,
    );
  }
  static function pie($listado, $labels) {
    $estadisticas = array();
#    array_walk($labels, function(&$v, $k) { $v['tipo'] = $k; });
#debug($labels);
    foreach($labels as $l) {
      foreach($listado as $m) {
        if($l['tipo'] == $m['tipo']) {
          $estadisticas[$l['tipo']] = $m['cantidad'];
        }
      }
      if(!isset($estadisticas[$l['tipo']])) {
        $estadisticas[$l['tipo']] = 0;
      }
    }
    $datasets = array(
      'data'            => array_values($estadisticas),
      'backgroundColor' => array_values(array_map(function($n) { return $n['color']; }, $labels)),
      'label'           => 'PIE',
    );
    return array(
      'type'     => 'pie',
      'labels'   => array_values(array_map(function($n) { return !empty($n['nombre']) ? $n['nombre'] : $n['tipo']; }, $labels)),
      'datasets' => array($datasets),
    );
  }
}
