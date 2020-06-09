<?php
class Pagination {
  public $id;
  public $page     = 1;
  public $npages   = 0;
  public $cantidad = 8;
  public $number_pages   = 0;
  public $number_results = 0;
  public $next;
  public $previous;
  public $ajax = false;
  public $link = null;
  public $order_by = null;
  public $has_filter = false;
  public $filter     = null;
  private $cb_filter = null;
  public $vkey = null;

  function __construct($id) {
    $this->id = $id;
    $this->vkey = Pagination::hash('_p4' . $id);
  }
  public static function hash($st) {
    return substr(md5($st), 0, 4);
  }
  private static function generar_indices($max, $current = 1) {
    if($max < 5) {
      return range(1, $max);
    } else {
      if($current < 4 || ($max - $current < 4)) {
        if($current < 5) {
          $l = range(1, 4);
          $l[] = '...';
          $l[] = $max;
        } else {
          $l = [1, '...'];
          for($i = $max - 4; $i <= $max; $i++) {
            $l[] = $i;
          }
        }
        return $l;
      } else {
        return array(1, '...', $current - 1, $current, $current + 1, '...', $max);
      }
    }
  }
  public function setFilter($f) {
    if($f instanceof Formity) {
      $this->cb_filter = $f;
      $f->setMethod('GET');
      $f->button = 'FILTRAR';
      $this->has_filter = true;
      return true;
    } elseif(is_string($f)) {
      if(Formity::exists($f)) {
        $this->cb_filter = Formity::getInstance($f);
        $this->cb_filter->setMethod('GET');
        $this->cb_filter->button = 'FILTRAR';
        $this->has_filter = true;
        return true;
      }
    }
    return false;
  }
  function analyzeFilter() {
    $re = $this->cb_filter;
    if($re->byRequest()) {
      if($re->isValid($err)) {
        $this->filter = $re->getData();
      }
    }
  }
  function setRequest() {
    if(isset($_GET[$this->vkey])) {
      $this->page = !empty($_GET[$this->vkey]) ? (int) $_GET[$this->vkey] : 1;
      if(!is_int($this->page) || $this->page < 1) {
        return false;
      }
    }
    return true;
  }
  function renderFilter() {
    $rp = '<div class="FormityFilter">';
    $rp .= $this->cb_filter->render();
    $rp .= '</div>';
    return $rp;
  }
  function renderPagination() {
    return $this->render();
  }
  function setNumResults($n) {
    $this->number_results = $n;
    $this->number_pages   = (int) ceil($n /  $this->cantidad);
    if(!is_int($this->page) || $this->page > $this->number_pages) {
      return false;
    }
    $this->previous = $this->page > 1;
    $this->next     = $this->page < $this->number_pages;
    $this->offset = ($this->page - 1) * $this->cantidad;
    return true;
  }
  public function render() {
    if(empty($this->number_results) || $this->number_pages == 1) {
      return '';
    }
    $link = !is_null($this->link) ? $this->link : Route::uri(null, $this->vkey . '=$p1');
    $link_anterior  = str_replace("\$p1", ($this->page - 1), $link);
    $link_siguiente = str_replace("\$p1", ($this->page + 1), $link);

    $attrs = $this->ajax ? 'data-is-ajax' : '';
    $attrs .= ' data-in-popy';
    $html = "<div style=\"padding:0.5rem 1.5rem;\">";
    $html .= "<nav class=\"pagination is-centered is-small\" role=\"navigation\" aria-label=\"pagination\" data-id=\"" . $this->id . "\">";
    if(!empty($this->previous)) {
      $html .= "<a class=\"pagination-previous\" href=\"" . $link_anterior . "\" " . $attrs . ">Anterior</a>";
    } else {
      $html .= "<a class=\"pagination-previous disabled\">Anterior</a>";
    }
//    $html .= "<div><b>Pagina " . $this->page . " de " . $this->number_pages . "</b></div>";
//    $html .= "<small>Se ha encontrado " . $this->number_results . " resultado" . ($this->number_results == 1 ? '' : 's') . "</small>";

    $indices = static::generar_indices($this->number_pages, $this->page);
    $html .= "<ul class=\"pagination-list\">";
    foreach($indices as $i) {
      $link_i  = str_replace("\$p1", $i, $link);
      if($i == '...') {
        $html .= "<li><span class=\"pagination-ellipsis\">&hellip;</span></li>";
      } elseif($i == $this->page) {
        $html .= "<li><a class=\"pagination-link is-current\" aria-label=\"Ir página " . $i . "\" href=\"" . $link_i . "\" " . $attrs . ">" . $i . "</a></li>";
      } else {
        $html .= "<li><a class=\"pagination-link\" aria-label=\"Ir página " . $i . "\" href=\"" . $link_i . "\" " . $attrs . ">" . $i . "</a></li>";
      }
    }
    $html .= "</ul>";
    if(!empty($this->next)) {
      $html .= "<a class=\"pagination-next\" href=\"" . $link_siguiente . "\" " . $attrs . ">Siguiente</a>";
    } else {
      $html .= "<a class=\"pagination-next disabled\">Siguiente</a>";
    }
    $html .= "</nav>";
    $html .= "</div>";
    return $html;
  }
}

