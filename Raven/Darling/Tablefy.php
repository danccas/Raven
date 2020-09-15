<?php
namespace Raven\Darling;
use Raven\Framework\Application;
use Raven\Framework\Controller;
use Raven\Http\Route;
use Raven\Framework\Orm\Pagination;

class Tablefy {
  const TABLEFY_NORMAL = 0;
  const TABLEFY_MINI   = 1;
  const TABLEFY_LIGHT   = 2;

  private $style = 0;

  private $passRequest = '_GET';
  private $passVarNam  = '_tyn';
  private $passVarVal  = '_tyv';
  private $passVarAjax = '_ajx';
  

  private $index   = null;
  private $headers = array();

  private $data    = array();
  private $row_cb  = null;

  private $options = array();

  public $pagination = array();
  private $fn_process = null;

  public $title = null;
  public $hash = null;
  
  public $ajax = false;
  
  private $is_process = false;
  private static $instances = null;
  private $name = null;

  public $export_in = array();#'pdf', 'excel');
  private $internal_keys = array('tb_id','tb_tr','tb_options','tb_orden');

  
  public static function instance($name = null) {
    if(property_exists(static::$instances, $name)) {
      return static::$instances->$name;
    } else {
      exit('no existe instance');
    }
  }
  public static function create($name = null) {
    return new self($name);
  }
  public static function delete($name) {
    static::$instances->$name = null;
    unset(static::$instances->$name);
  }
  public function __construct($name = null) {
    if(static::$instances === null) {
      static::$instances = new \stdClass();
    }
    $index = count((array) static::$instances) + 1;
    $name = $name ?? 'form' . $index;
    static::$instances->$name = $this;
    $this->name = $name;
    $this->uniqueId = static::hash($name);
    $this->index = $index;
    $this->passVarNam  = $this->passVarNam . $this->index;
    $this->passVarVal  = $this->passVarVal . $this->index;
    $this->passVarAjax = $this->passVarAjax . $this->index;
    $this->pagination = new Pagination($name);
  }
  public function getName() {
    return $this->name;
  }
  public function setName(String $name) {
    $old = $this->name;
    unset(static::$instances->$old);
    static::$instances->$name = $this;
    $this->name = $name;
    return $this;
  }
  public function export() {
    $in = func_get_args();
    if(empty($in) || !is_array($in)) {
      return;
    }
    if(count($in) == 1 && $in[0] == false) {
      $this->export_in = null;
      return;
    }
    $in = array_map(function($n) { return strtolower($n); }, $in);
    $this->export_in = $in;
  }
  public static function hash($st) {
    return substr(md5($st), 0, 4);
  }
  public function getLink() {
    return Route::uri(null, 'tfweb' . $this->index . '=' . $this->uniqueId);
  }
  public function setTitle($t) {
    $this->title = $t;
  }
  public function setStyle($t) {
    $this->style = (int) $t;
  }
  public static function link($ruta) {
    return array(
      'type' => 'link',
      'data' => $ruta,
    );
  }
  private static function match_link($row, $ruta = null, $get = null) {
    if(!is_null($get)) {
      $get = preg_replace_callback("/\:(?<id>[\w\_]+)\;?/", function($n) use($row) {
        return $row[$n['id']];
      }, $get, -1, $cantidad);
    }
    if(!is_null($ruta)) {
      $ruta = preg_replace_callback("/\:(?<id>[\w\_]+)\;?/", function($n) use($row) {
        return $row[$n['id']];
      }, $ruta, -1, $cantidad);
    }
    return Route::uri($ruta, $get);
  }
  public function setFilter($f) {
    if($f instanceof Formity) {
      return $this->pagination->setFilter($f);
    } elseif(is_string($f)) {
      if(Formity::exists($f)) {
        return $this->pagination->setFilter(Formity::instance($f));
      }
    }
    return false;
  }
  public function request($x) {
    return isset($_GET[$x]) || $_GET[$x] === null ? $_GET[$x] : false;
  }
  public function setHeader() {
    $n = func_get_args();
    if(empty($n)) return false;
    if(count($n) == 1 && is_array($n)) {
      $n = array_shift($n);
    }
    $n = array_map(function($n) { return is_array($n) ? $n : array('text' => $n); }, $n);
    $this->headers = $n;
  }
  public function setRow($cb) {
    if(is_callable($cb)) {
      $this->row_cb = $cb;
    }
  }
  public function setData($n, $map = null) {
    $this->data = $n;
    if(!is_null($map)) {
      $this->fn_process = $map;
    }
  }
  public function __processDataInternal() {
    if($this->pagination->has_filter) {
      $this->pagination->analyzeFilter();
    }
    $dat = $this->data;
    if(is_callable($dat)) {
      $dat = $dat($this->pagination);
    }
    if(empty($dat)) {
      $this->hash = 'clean';
      return null;
    }
    $this->hash = md5(json_encode($dat));
    return $dat;
  }
  public function __processDataExternal($dat) {
    $header   = $this->headers;
    $options  = $this->options;
    $internal = $this->internal_keys;
    $map      = $this->fn_process;
    $call_row = $this->row_cb;
    if(empty($dat)) {
      return null;
    }
    array_walk($dat, function(&$n, $k) use($map, &$i) {
      $m['tb_id']      = isset($n['tb_id']) ? $n['tb_id'] : (isset($n['id']) ? $n['id'] : uniqid());
      $m['tb_data']    = $n;
      $m['tb_map']     = !is_null($map) && is_callable($map) ? $map($n) : false;
      $m['tb_orden']   = $k;
      $n['tb_options'] = isset($m['tb_map']['tb_options']) ? $m['tb_map']['tb_options'] : (isset($n['tb_options']) ? $n['tb_options'] : null);
      $m['tb_options'] = $n['tb_options'] !== null ? array_map(function($n) { return static::hash($n); }, is_array($n['tb_options']) ? $n['tb_options'] : explode(',', $n['tb_options'])) : null;
      #$m['tb_options'] = isset($n['tb_options']) ? array_map(function($n) { return static::hash($n); }, $n['tb_options']) : null;
      $n = $m;
      unset($m);
    });
    return array_map(function($n) use($header, $options, $internal, $call_row) {
      $m = array();
      $n['tb_map'] = $n['tb_map'] !== false ? $n['tb_map'] : $n['tb_data'];
      foreach($header as $h) {
        $lab = array_shift($n['tb_map']);
        if(!is_array($lab)) {
          $lab = array('text' => $lab);
        }
        $lab['label'] = $h;
        $m[] = $lab;
      }
      $n['tb_map'] = $m;
      unset($m);
      if(!empty($options)) {
        $op = array();
        foreach($options as $k => $o) {
          if(is_null($n['tb_options']) || in_array($k, $n['tb_options'])) {
            $o['rotulo'] = static::replace_text_by_icon($o['name']);
            if($o['event']['type'] == 'link') {
              $o['href'] = static::match_link($n['tb_data'], $o['event']['data']);
            } else {
              if($call_row) {
                $o['href'] = static::match_link(array('xid' => $n['tb_id']), null, $o['link']);
              } else {
                $o['href'] = static::match_link(array('xid' => $n['tb_orden']), null, $o['link']);
              }
            }
            $op[] = $o;
          }
        }
        $n['tb_options'] = $op;
      }
      return $n;
    }, $dat);
  }
  public function prepare() {
    if(!is_null($this->request($this->passVarAjax))) {
      echo $this->render();
      exit;
    }
    $var_option = 'tfweb' . $this->index;
    if(isset($_GET['tfweb' . $this->index]) && $_GET['tfweb' . $this->index] == $this->uniqueId) {
      Theme::render($this);
    }
    $var_option = 'export_' . $this->uniqueId;
    if(!empty($this->export_in) && isset($_GET[$var_option])) {
      $en = $_GET[$var_option];
      if($en == 'pdf') {
        /* Exportar data en PDF */
        ob_start();
          echo $this->renderOnlyTable(null, true);
        $html = ob_get_clean();
        require_once(ABS_LIBRERIAS . 'pdfily.php');
        $pdf = new PDFily(Identify::g()->empresa);
        $pdf->addPage($html, 'L');
        $pdf->forceDownload('reporte_' . time());

      } elseif($en == 'excel') {
        $data = $this->__processDataInternal();
        $data = $this->__processDataExternal($data);
        $quitt = $this->internal_keys;
        $data = array_map(function($n) use($quitt) { return array_filter($n, function ($key) use($quitt) { return $key === 0 || !in_array($key, $quitt); }, ARRAY_FILTER_USE_KEY); }, $data);
        require_once(ABS_LIBRERIAS . 'excelity.php');
        $excel = new Excelity();
        $excel->createHeader('REPORTE', 'REPORTE GENERADO AUTOMATICAMENTE');
        #$excel->movingCells(false);
        $excel->setTitle('REPORTE');
        $excel->setHeader($this->headers);
        $excel->setData($data);
        $excel->forceDownload('reporte_' . date("Y-m-d"));
      } else {
        _404('Error:' . $en);
      }
    }
  }
  public function setPagination($pag) {
    $this->pagination = $pag;
  }
  public function setMap($cb) {
    $this->fn_process = $cb;
  }
  public function setAjax($t) {
    return $this->ajax = !!$t;
  }
  public function resource(String $resource, $controller) {
    if($controller instanceof Controller) {

    } elseif(is_string($controller)) {
      $controller = new $controller;
    }
    $this->setOption('show', $controller, 'show');
    $this->setOption('edit', $controller, 'edit');
    $this->setOption('delete', $controller, 'delete');
    return $this;
  }
  public function setOption($key, $call, $method = null) {
    $div = ':';
    if(strpos($key, $div) === false) {
      $key .= $div;
    }
    list($key, $name) = explode($div, $key);
    $is_popy = substr($key, -1) == '&';
    $key = trim($key,'&');
    $name = !empty($name) ? $name : ucfirst(str_replace('_',' ', strtolower($key)));
    $key = static::hash($key);
    if(is_callable($call)) {
      $call = array(
        'type' => 'Closure',
        'data' => $call,
      );
    } elseif($call instanceof Controller) {
      $call = array(
        'type'       => 'Controller',
        'controller' => $call,
        'method'     => $method,
      );
    } elseif(is_string($call) && strpos($call, '@') !== false) {
      list($controller, $method) = explode('@', $call);
      $call = array(
        'type'       => 'Controller',
        'controller' => $controller,
        'method'     => $method,
      );
    } elseif(!is_array($call)) {
      $call = array(
        'type' => 'link',
        'data' => $call,
      );
    }
    $this->options[$key] = array(
      'name'  => $name,
      'event' => $call,
      'popy'  => $is_popy,
      'link'  => $this->passVarNam . '=' . $key . '&' . $this->passVarVal . '=:xid');
      //debug(Route::instance()->request->query($this->passVarNam));

    if(Route::instance()->request->input($this->passVarNam) == $key) {
       /* Es solicitud a nuestra opcion, y nuestro Tablefy */
      $rowId = Route::instance()->request->input($this->passVarVal);
      if($this->row_cb !== null) {
        $rp = ($this->row_cb)($rowId);
        if(empty($rp)) {
          exit('respuesta de Closure false');
        } else {
          $error = null;
          if($this->options[$key]['event']['type'] == 'Controller') {
            if(!is_string($this->options[$key]['event']['controller'])) {
              $controller = $this->options[$key]['event']['controller'];
              $method     = $this->options[$key]['event']['method'];
              $controller->{$method}(Route::instance()->request, $rp);
              
            } else {
              $controller = $this->options[$key]['event']['controller'];
              $method     = $this->options[$key]['event']['method'];
              $controller = new $controller;
              $controller->{$method}(Route::instance()->request, $rp);
            }
            exit; //TODO
          } elseif($this->options[$key]['event']['type'] == 'Closure') {
            $e['call'] = $this->options[$key]['event']['data'](Route::instance()->request, $rp);
            exit; //TODO
          }
        }
      } elseif(is_numeric($rowId)) {
        $dat = $this->__processDataInternal();
        if(!isset($dat[$rowId])) {
          return;
        }
        $error = null;
        if($this->options[$key]['event']['type'] == 'Controller') {
          if(!is_string($this->options[$key]['event']['controller'])) {
            $controller = $this->options[$key]['event']['controller'];
            $method     = $this->options[$key]['event']['method'];
            $controller->{$method}(Route::instance()->request, $dat[$rowId]);
            
          } else {
            $controller = $this->options[$key]['event']['controller'];
            $method     = $this->options[$key]['event']['method'];
            $controller = new $controller;
            $controller->{$method}(Route::instance()->request, $dat[$rowId]);
          }
          exit; //TODO
        } elseif($this->options[$key]['event']['type'] == 'Closure') {
          $e['call'] = $this->options[$key]['event']['data'](Route::instance()->request, $dat[$rowId]);
          exit; //TODO
        }
        return true;
      }
    }
  }
  public function onOption(&$ls = null) {
    return;
    if(!empty($this->ls)) {
      $ls = $this->ls;
      return true;
    }
    return false;
    if(!is_null($this->request($this->passVarNam)) && !is_null($this->request($this->passVarVal))) {
      if(isset($this->options[$this->request($this->passVarNam)])) {
        if(is_numeric($this->request($this->passVarVal)) && isset($this->data[$this->request($this->passVarVal)])) {
          $error = null;
          $e['option'] = $this->request($this->passVarNam);
          if($this->options[$this->request($this->passVarNam)]['event']['type'] == 'Closure') {
            $e['call']   = $this->options[$this->request($this->passVarNam)]['event']['data']($this->data[$this->request($this->passVarVal)], $error);;
          }
          if($e['call'] === false && is_null($error)) {
            $error = 'No se ha podido realizar la Acci&oacute;n';
          }
          $e['error']  = $error; 
          $objeto = $this->data[$this->request($this->passVarVal)];
          return true;
        }
      }
    }
    return false;
  }
  private function process_data() {
    if($this->is_process) {
      return;
    }
    $this->is_process = true;
    if(!empty($this->data) && is_array($this->data)) {
      $this->data = array_map(function($n) {
        $n['_id'] = isset($n['_id']) ? $n['_id'] : uniqid();
        return $n;
      }, $this->data);
      if(!is_null($this->fn_process)) {
        $cb = $this->fn_process;
        $this->data = array_map(function($n) use($cb) {
          $m = $cb($n);
          $m['_options'] = isset($m['_options']) ? array_map(function($n) { return static::hash($n); }, $m['_options']) : null;
          $m['_id']      = isset($m['_id'])      ? $m['_id']      : $n['id'];
          $m['id'] = $n['id'];//TODO
          return $m;
        }, $this->data);
      }
      $this->hash = md5(json_encode($this->data));
    } else {
      $this->hash = 'clean';
    }
  }
  private static function replace_text_by_icon($t) {
    return strtoupper($t);
    $tx = strtolower($t);
    $icons = array(
      'editar'   => '<i class="fa fa-edit" style="color:#5b87e5;margin-right:3px;"></i>',
      'eliminar' => '<i class="fa fa-remove" style="color:#ff0000;margin-right:3px;"></i>',
      'detalles' => '<i class="fa fa-table" style="color:#827f7f;margin-right:3px;"></i>',
      'ver'      => '<i class="fa fa-eye" style="color:#4699ff;margin-right:3px;"></i>',
    );
    if(isset($icons[$tx])) {
      $r = $icons[$tx];
    } else {
      $r = '<i class="fa fa-briefcase" style="color:#4699ff;margin-right:3px;"></i>';
    }
    $r .= ' <span style="font-size:10px;">' . strtoupper($t) . '</span>';
    return $r;
  }
  public function renderOnlyTable($attr = null, $basicHTML = false) {
    $data = $this->__processDataInternal();
    $data = $this->__processDataExternal($data);
    $attrs = array(
      'class' => 'table is-fullwidth is-striped',
    );
    if(!empty($attr) && is_array($attr)) {
      $attrs = array_merge($attrs, $attr);
    }
    $_attrs = array();
    foreach($attrs as $k => $v) {
      $_attrs[] = $k . '="' . $v . '"';
    }
    $attrs = implode(' ', $_attrs);
    unset($_attrs);

    $rp = '';
    if(!$this->ajax || ($this->ajax && $this->request($this->passVarAjax) === NULL)) {
      if($this->pagination->has_filter && !$basicHTML) {
        $rp .= $this->pagination->renderFilter();
      }
      if(!empty($this->export_in) && !$basicHTML) {
        $rp .= "<div class=\"buttons has-addons is-right\" style=\"margin: 0;padding-top: 5px;padding-right: 5px;\">";
        $rp .= "<span class=\"button is-small\">Exportar:</span>";
        foreach($this->export_in as $e) {
          $link = Route::uri(null, null, null, 'export_' . $this->uniqueId . '=' . $e);
          $rp .= "<a class=\"button is-small is-danger\" href=\"" . $link . "\">" . strtoupper($e) . "</a>";
        }
        $rp .= "</div>";
      }
    }
    $rp .= "<div data-content-tablefy>";
    if($basicHTML) {
      $rp .= "<table border=\"1\" cellpadding=\"5\" data-id=\"" . $this->index . "\" data-hash=\"" . $this->hash . "\">\n";
    } else {
      $rp .= "<table data-id=\"" . $this->index . "\" " . $attrs . " data-hash=\"" . $this->hash . "\">\n";
    }
      $rp .= "<thead>\n";
        $rp .= "<tr>\n";
          foreach ($this->headers as $h) {
            $th_attrs = ' ';
            if(!empty($h['style']) && is_array($h['style'])) {
              foreach($h['style'] as $trck => $trcv) {
                $th_attrs .= $trck .'="' . $trcv . '" ';
              }
            }
            $rp .= "<th " . $th_attrs . ">" . (is_array($h) ? $h['text'] : $h) . "</th>\n";
          }
          if($this->options !== false && !$basicHTML) {
            $rp .= "<th></th>\n";
          }
        $rp .= "</tr>\n";
      $rp .= "</thead>\n";
      $rp .= "<tbody>\n";
        if(empty($data)) {
          $rp .= "<tr><td class=\"has-text-centered\" colspan=\"" . count($this->headers) . "\">No se ha encontrado coincidencias.</td></tr>\n";
        } else {
        foreach($data as $tr) {
          $tr_attrs = ' ';
          if(!empty($tr['tb_tr']) && is_array($tr['tb_tr'])) {
            foreach($tr['tb_tr'] as $trck => $trcv) {
              $tr_attrs .= $trck .'="' . $trcv . '" ';
            }
          }
          $rp .= "<tr data-id=\"" . $tr['tb_id'] . "\"" . $tr_attrs . ">\n";
          foreach ($tr['tb_map'] as $ktd => $td) {
            $params = array();
            if(!empty($td['style'])) {
              foreach($td['style'] as $a => $b) {
                if(in_array($a, ['color','text-align'])) {
                  $params[] = 'style="' . $a . ':' . $b . ';"';
                } else {
                  $params[] = $a . '="' . $b . '"';
                }
              }
            }
            $rp .= "<td data-label=\"" . (is_array($td['label']) ? $td['label']['text'] : $td['label']) . "\" data-tr=\"" . $tr['tb_id'] . "\" data-id=\"" . $tr['tb_id'] . "\" " . implode(' ', $params) . ">" . $td['text'] . "</td>\n";
          }
          if($this->options !== false && !$basicHTML) {
            $rp .= "<td data-label=\"Opciones\">\n";
              $rp .= "<div class=\"opciones columns\" style=\"justify-content: flex-end;padding-top: 10px;\">\n";
              foreach($tr['tb_options'] as $o) {
                $rp .= "<a class=\"button is-small\" " . (!empty($o['popy']) ? 'data-popy' : '') . " href=\"" . $o['href'] . "\" title=\"" . $o['name'] . "\" style=\"margin-left:5px;\">" . $o['rotulo'] . "</a>\n";
              }
              $rp .= "</div>\n";
            $rp .= "</td>\n";
          }
          $rp .= "</tr>\n";
        }
        }
      $rp .= "</tbody>\n";
      $rp .= "</table>\n";
    if(!is_null($this->pagination) && !$basicHTML) {
      if($this->ajax) {
        $this->pagination->ajax = $this->ajax;
        $this->pagination->link = Route::uri(null, DOMINIO_ACTUAL, SUBDOMINIO_ACTUAL, $this->pagination->vkey . '=$p1&' . $this->passVarAjax);
      }
      $rp .= $this->pagination->render();
    }
    $rp .= "</div>";
    return $rp;
  }
  public function renderInPage($attr = null) {
    $rp = "<div class=\"card\">";
    if(!is_null($this->title)) {
      $rp .= "<div class=\"card-header\">";
      $rp .= "<p class=\"card-header-title\">";
      $rp .= $this->title;
      $rp .= "</p>";
      $rp .= "</div>";
    }
    $rp .= "<div class=\"card-content\"><div class=\"table-responsive\">";
    //$rp .= Route::renderErrors();
    $rp .= '<div data-containter-tablefy="' . $this->index . '">';
    $rp .= $this->renderOnlyTable($attr);
    $rp .= '</div></div></div></div>';
    return $rp;
    $VISTA_HTML = $rp;
    unset($rp);
    //require_once(Route::g()->attr('views') . 'internal.php');
    exit;
  }
  public function render($attr = null) {
    if($this->ajax && $this->request($this->passVarAjax) !== false) {
      return $this->renderOnlyTable($attr);
    }
    $rp = '';
    //if(Route::requestByPopy()) {
      //$rp .= Route::renderNav();
    //}
    if(!is_null($this->title)) {
      $rp .= '<h2>' . $this->title . '</h2>';
    }
    //$rp .= Route::renderErrors();
    $rp .= '<div data-containter-tablefy="' . $this->index . '">';
      $rp .= $this->renderOnlyTable($attr);
    $rp .= '</div>';
    return $rp;
  }
}
