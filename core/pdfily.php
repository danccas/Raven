<?php
require_once(ABS_LIBRERIAS . 'dist/tcpdf/tcpdf.php');
class Pdf extends TCPDF {
  public $autor      = null;
  public $showHeader = false;
  public $empresa    = null;
  function __construct() {
      parent::__construct();
  }
  public function Header() {
    if(empty($this->empresa)) {
      return false;
    }
    $this->SetY(5);
    $html  = '<table style="width:1100px;">';
      $html .= '<tr>';
        $html .= '<td style="width:120px;"><img src="' . $this->empresa['ruta_imagen'] . '" style="width:80px;"/></td>';
        $html .= '<td><br /><br />';
          $html .= '<span style="font-size:15px;"><span style="font-weight:bold;">' . strtoupper($this->empresa['nombre']) . '</span></span><br>';
          $html .= '<span style="font-size:13px;">' . $this->empresa['descripcion'] . '</span><br>';
//          $html .= '<span style="font-size:11px;">' . DOMINIO_ACTUAL . '</span>';
        $html .= '</td>';
      $html .= '</tr>';
    $html .= '</table><hr>';
    $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
  }
  public function Footer() {
    global $USUARIO;
    if(is_null($this->autor)) {
      if(!empty($USUARIO) && !empty($USUARIO['persona']) && !EMPTY($USUARIO['persona']['nombres'])) {
        $this->autor = strtoupper($USUARIO['persona']['apellido_paterno'] . ' ' . $USUARIO['persona']['apellido_materno'] . ', ' . $USUARIO['persona']['nombres']);
      }
    }
    $this->SetY(-20);
    $fecha   = fecha_larga(null, true);
    $pagina  = 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages();
    $html = '<hr><br>';
    $html .= '<table style="width:100%;">';
      $html .= '<tr>';
        $html .= '<td style="width:50%">';
          if(!empty($this->autor)) {
            $html .= '<b>' . $this->autor . '</b><br>';
          }
          $html .= $fecha;
        $html .= '</td>';
        $html .= '<td style="text-align:right">';
          $html .= $pagina;
        $html .= '</td>';
      $html .= '</tr>';
    $html .= '</table>';
    $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
  }
}
class PDFily {
  private $_instance = null;

  function __construct($empresa = null) {
    $this->_instance = new Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $this->_instance->empresa = $empresa;
    $this->_instance->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH,PDF_HEADER_TITLE,PDF_HEADER_STRING);
    $this->_instance->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $this->_instance->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $this->_instance->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $this->_instance->SetMargins(10, (!empty($empresa) ? 35 : 10), 10);
    $this->_instance->SetHeaderMargin(PDF_MARGIN_HEADER);
    $this->_instance->SetFooterMargin(PDF_MARGIN_FOOTER);
    $this->_instance->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $this->_instance->setImageScale(PDF_IMAGE_SCALE_RATIO);
    //$this->_instance->setLanguageArray($l);
    $this->_instance->setFontSubsetting(true);
    $this->_instance->SetFont('helvetica', '', 10, '', true);
    }
    public function showHeader() {
      $this->_instance->showHeader = true;
    }
    public function setAutor($nombre) {
      $this->_instance->autor = $nombre;
    }
    public function addPage($html, $tipo = null) {
      $html = "<style>th { border: 1px solid #000000; padding: 5px; text-align:center; font-weight: bold; }</style>" . $html;
      $html = str_replace('<tbody>', '', $html);
      $html = str_replace('</tbody>', '', $html);
      $html = str_replace('<thead>', '', $html);
      $html = str_replace('</thead>', '', $html);
#echo $html;exit;
      if(is_null($tipo)) {
        $this->_instance->AddPage();
      } else {
        $this->_instance->AddPage($tipo, 'A4');
      }
      $this->_instance->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='L', $autopadding = true);
    }
    public function save($name, $tipo = 'I') {
      $name = strpos($name, '.pdf') === false ? $name . '.pdf' : $name;
      return $this->_instance->Output($name, $tipo);
    }
    public function forceDownload($name) {
        $this->save($name, 'D');
        exit();
    }
}
