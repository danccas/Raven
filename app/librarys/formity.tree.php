<?php
function Formitree($db, $mtree, $table, $key, $id = null, $context_id = null, $max_nivel = null, $params = null) {
  $EMPRESA_ID = Identify::g()->empresa_id;
  $params['field'] = empty($params['field']) ? 'nombre' : $params['field'];
  $where = array();
  $where['empresa'] = 'E.empresa_id = ' . (int) $EMPRESA_ID;
  if(isset($params['empresa']) && empty($params['empresa'])) {
    unset($where['empresa']);
  }
  $params['where'] = !empty($where) ? ' AND ' . implode(' AND ', $where) : '';

  $context_id = (array) $context_id;

    $a = array();
    if(!empty($a['ids'])) {
      $ids = explode('_', $a['ids']);
      $ids = array_filter($ids, function($n) { return !empty($n); });
      $context_id = array_merge($context_id, $ids);
    }
    $vc = function($db, $context_id, $id) use(&$vc, $table, $key) {
      if(is_null($id)) {
        return false;
      }
      $rp = $db->get("SELECT id, " . $key . " FROM " . $table . " WHERE id = '" . $id . "'", true);
      if(!empty($rp)) {
        if(empty($rp[$key])) {
          return false;

        } elseif(in_array($rp[$key], $context_id)) {
          return true;

        } else {
          return $vc($db, $context_id, $rp[$key]);
        }
      }
      return false;
    };
    if($mtree == 'ls') {
      $ls = function($db, $empresa_id, $id = null) use(&$ls, $table, $key, $context_id, $params) {
        $where = is_null($id) ? (!empty($context_id) ? 'E.id IN (\'' . implode("','", $context_id) . '\')' : 'E.' . $key . ' IS NULL') : 'E.' . $key . '= \'' . $id . "'";
        return $db->get("
        SELECT
          E.id,
          E." . $params['field'] . " as nombre,
          (SELECT COUNT(id) FROM " . $table . " WHERE " . $key . " = E.id AND deleted IS NULl) as hijos_cantidad,
          CONCAT(
            IF(E3." . $params['field'] . " IS NULL, '', CONCAT(E3." . $params['field'] . ", ' > ')),
            IF(E2." . $params['field'] . " IS NULL, '', CONCAT(E2." . $params['field'] . ", ' > ')),
            IF(E1." . $params['field'] . " IS NULL, '', CONCAT(E1." . $params['field'] . ", ' > ')),
            E." . $params['field'] . "
          ) as descripcion
        FROM " . $table . " E
          LEFT JOIN " . $table . " E1 ON E1.id = E." . $key . "
          LEFT JOIN " . $table . " E2 ON E2.id = E1." . $key . "
          LEFT JOIN " . $table . " E3 ON E3.id = E2." . $key . "
        WHERE E.deleted IS NULL AND " . $where . " AND E.empresa_id = " . (int) $empresa_id);
      };
      $ls = $ls($db, $EMPRESA_ID, $id);

    } elseif($mtree == 'top') {
      $ls = function ($db, $empresa_id, $id, $tree = null) use(&$ls, $table, $key, $context_id, $vc, $params) {
        $rp = $db->get("
        SELECT
          E.id,
          E." . $params['field'] . " as nombre,
          E." . $key . " as padre_id,
          (SELECT COUNT(id) FROM " . $table . " WHERE " . $key . " = E.id AND deleted IS NULL) as hijos_cantidad,
          CONCAT(
            IF(E3." . $params['field'] . " IS NULL, '', CONCAT(E3." . $params['field'] . ", ' > ')),
            IF(E2." . $params['field'] . " IS NULL, '', CONCAT(E2." . $params['field'] . ", ' > ')),
            IF(E1." . $params['field'] . " IS NULL, '', CONCAT(E1." . $params['field'] . ", ' > ')),
            E." . $params['field'] . "
          ) as descripcion
        FROM " . $table . " E
          LEFT JOIN " . $table . " E1 ON E1.id = E." . $key . "
          LEFT JOIN " . $table . " E2 ON E2.id = E1." . $key . "
          LEFT JOIN " . $table . " E3 ON E3.id = E2." . $key . "
        WHERE E.deleted IS NULL AND (E.id = '" . $id . "' || E." . $key . " = (SELECT " . $key . " FROM " . $table . " WHERE id = '" . $id . "')) AND E.empresa_id = " . (int) $empresa_id);
        $keeey = null;
        if(!empty($rp)) {
          array_walk($rp, function($v, $k) use($id, &$keeey) {
            if($v['id'] == $id) {
              $keeey = $k;
            }
          });
          $stree = $rp[$keeey];
          //echo "[" . $stree['id'] . " => " . $stree['padre_id'] . "]";
          $stree['hijos'] = !empty($stree['hijos_cantidad']) ? $tree : null;
          if(!empty($stree['padre_id'])) {
            $rp[$keeey] = $stree;
            if(!empty($context_id)) {
              if($vc($context_id, $stree['id'])) {
                $stree = $ls($db, $empresa_id, $stree['padre_id'], $rp);
              } else {
                $stree = $rp;
              }
            } else {
              $stree = $ls($db, $empresa_id, $stree['padre_id'], $rp);
            }
          } else {
            $stree = [$stree];
          }
        } else {
          $stree = $tree;
        }
        if(is_null($tree) && !empty($context_id)) {
          $stree = array_filter($stree, function($n) use($context_id) {
            return in_array($n['id'], $context_id);
          });
        }
        return $stree;
      };
      $ls = $ls($db, $EMPRESA_ID, $id);

    } else {
      _404('sin-method');
    }
    /*$ls = !empty($ls) ? array_map(function($n) {
      $n['nombre'] = (!empty($n['grado']) ? $n['grado'] . ' ' : '') . $n['nombre'];
      return $n;
    }, $ls) : null;*/
    return_json($ls);
}
