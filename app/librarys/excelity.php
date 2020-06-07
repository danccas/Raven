<?php
//$ls = array_map(function($n) { return 'B' . $n; }, range('A','Z'));
//echo implode("','", $ls);exit;
class Excelity {
    private $_version    = 20170831110200;
    private $letras      = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ'];
    private $ExcelBase   = null;
    private $titulo      = '';
    private $subtitulo   = '';
    private $margin_left = 0;
    private $margin_top  = 0;
    private $styles      = null;
    private $max         = array('x' => 0, 'y' => 0);
    private $current     = array('x' => 0, 'y' => 0);
    private $current_page = null;
    private $page_save   = false;
    private $has_header  = false;
    private $empresa     = null;
    private $busy_cells  = [];
    private $moving_cells = true;
    const FILTER_HEADER = 1;

    function __construct($empresa = null) {
      $this->empresa   = $empresa;
      if(is_string($empresa) && file_exists($empresa)) {
        //
      } else {
        require_once(ABS_LIBRERIAS . 'dist/PHPExcel.php');
        $this->ExcelBase = new PHPExcel();
      }
    }
    function l2n($letra) {
      return array_search($letra, $this->letras) + 1;
    }
    function n2l($n) {
      return $this->letras[$n - 1];
    }
    function movingCells($x) {
      $this->moving_cells = !empty($x);
    }
    function parseLoad($page = 0) {
      require_once(LIBRARYS . 'dist/PHPExcel/IOFactory.php');
      $objPHPExcel = PHPExcel_IOFactory::load($this->empresa);
      $objPHPExcel->setActiveSheetIndex($page);
      $size = $objPHPExcel->setActiveSheetIndex($page)->getHighestRowAndColumn(); //[row] => 34 [column] => AA
      $datos = array();
      for($y = 1; $y <= $size['row']; $y++) {
        for($x = 1; $x <= $this->l2n($size['column']); $x++) {
          $datos[$y][] = $objPHPExcel->getActiveSheet()->getCell($this->n2l($x). $y)->getValue();
        }
      }
      return $datos;
    }
    function setTitle($titulo) {
      return $this->newPage($titulo);
    }
    function newPage($titulo) {
      $titulo = !empty($titulo) ? $titulo : 'SIN TITULO';
      if(is_null($this->current_page)) {
        $this->current_page = 0;
        $this->ExcelBase->setActiveSheetIndex($this->current_page);
        $this->ExcelBase->getActiveSheet()->setTitle(preg_replace("/[^\w\s]/", '', $titulo));
      } else {
        $this->buildHeaderandStyle();
        $this->margin_left = 0;
        $this->margin_top  = 0;
        $this->max = array('x' => 0 , 'y' => 0);
        $this->current = array('x' => 0 , 'y' => 0);
        $this->current_page += 1;
        $this->ExcelBase->createSheet($this->current_page);
        $this->ExcelBase->setActiveSheetIndex($this->current_page);
        $this->page_save  = false;
        $this->has_header = false;
        $this->ExcelBase->getActiveSheet()->setTitle(preg_replace("/[^\w\s]/", '', $titulo));
      }
    }
    function createHeader($titulo, $subtitulo = '') {
        $this->titulo    = $titulo;
        $this->subtitulo = $subtitulo;
        $this->has_header = true;
        $this->max['y']  = 2;
        $this->max['x']  = 0;
        $this->setStyle('A1:AG100', array('background-color' => 'ffffff'));
    }
    function buildHeaderandStyle() {
        global $USUARIO;
        if(!$this->page_save) {
          $this->processStyle();
        }
        if($this->page_save || !$this->has_header) {
          return;
        }
        $rtd = $this->n2l($this->margin_left + 1) . ($this->margin_top + 1);
        $rt = $rtd . ':' . $this->n2l($this->max['x']) . ($this->margin_top + 1);
        $this->ExcelBase->getActiveSheet()->mergeCells($rt);
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText();
        $parte1 = $objRichText->createTextRun($this->titulo . "\n");
        $parte1->getFont()->setBold(true);
        $parte1->getFont()->setItalic(false);
        $parte1->getFont()->setSize(20);
        $parte2 = $objRichText->createTextRun($this->subtitulo);
        $parte2->getFont()->setBold(false);
        $parte2->getFont()->setItalic(false);
        $parte2->getFont()->setSize(16);
        $this->ExcelBase->getActiveSheet()->getCell($rtd)->setValue($objRichText);
        $this->ExcelBase->getActiveSheet()->getRowDimension($this->margin_top + 1)->setRowHeight(100);
        $this->ExcelBase->getActiveSheet()->getStyle($rtd)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->ExcelBase->getActiveSheet()->getStyle($rtd)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $this->ExcelBase->getActiveSheet()->getStyle($rtd)->getAlignment()->setWrapText(true);
        if(!empty($this->empresa)) {
          $this->setPoint($rtd);
          $this->insertImage(ABS_PUBLIC . 'ugel07.edu.pe' . $this->empresa['ruta_imagen'], null, 100, 30, 17);
        }
        if(!empty($USUARIO) && !empty($USUARIO['persona'])) {
          $rtd = $this->n2l($this->margin_left + 1) . ($this->max['y'] + 2);
          $rt = $rtd . ':' . $this->n2l($this->max['x']) . ($this->max['y'] + 2);
          $this->ExcelBase->getActiveSheet()->mergeCells($rt);
          $objRichText = new PHPExcel_RichText();
          $objRichText->createText();
          $nombres = strtoupper($USUARIO['persona']['apellido_paterno'] . ' ' . $USUARIO['persona']['apellido_materno'] . ', ' . $USUARIO['persona']['nombres']) . "\n";
          $parte1 = $objRichText->createTextRun($nombres);
          $parte1->getFont()->setBold(true);
          $parte1->getFont()->setItalic(false);
          $parte1->getFont()->setSize(16);
          $parte2 = $objRichText->createTextRun('Generado el ' . fecha_larga(null, true));
          $parte2->getFont()->setBold(false);
          $parte2->getFont()->setItalic(true);
          $parte2->getFont()->setSize(14);
          if(!empty($this->empresa)) {
            $parte2 = $objRichText->createTextRun("\n" . strtoupper($this->empresa['nombre']));
            $parte2->getFont()->setBold(true);
            $parte2->getFont()->setItalic(false);
            $parte2->getFont()->setSize(15);
          }
          $this->ExcelBase->getActiveSheet()->getCell($rtd)->setValue($objRichText);
          $this->ExcelBase->getActiveSheet()->getRowDimension($this->max['y'] + 2)->setRowHeight(70);
          $this->ExcelBase->getActiveSheet()->getStyle($rtd)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
          $this->ExcelBase->getActiveSheet()->getStyle($rtd)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
          $this->ExcelBase->getActiveSheet()->getStyle($rtd)->getAlignment()->setWrapText(true);
          $this->ExcelBase->getActiveSheet()->getCell('A1');
        }
        return;
    }
    function setSizeColumn($celda, $size = null) {
      if(!is_array($celda)) {
        $toPx = (double) $size;
        $toPx /= 3.72;
        $this->ExcelBase->getActiveSheet()->getColumnDimension($celda)->setWidth($toPx);
      } else {
        foreach($celda as $k => $c) {
          $sp = explode(',', $k);
          if(!empty($sp)) {
            foreach($sp as $k){
              if(!empty($k)) {
                $toPx = (double) $c;
                $toPx /= 3.72;
                $this->ExcelBase->getActiveSheet()->getColumnDimension($k)->setWidth($toPx);
              }
            }
          }
        }
      }
    }
    function setSizeRow($celda, $size = null) {
      if(!is_array($celda)) {
        $this->ExcelBase->getActiveSheet()->getRowDimension($celda)->setRowHeight($size);
      } else {
        foreach($celda as $k => $c) {
          $sp = explode(',', $k);
          if(!empty($sp)) {
            foreach($sp as $k){
              if(!empty($k)) {
                $this->ExcelBase->getActiveSheet()->getRowDimension($k)->setRowHeight($c);
              }
            }
          }
        }
      }
    }
    function setMargin($left = 0, $top = 0) {
        $this->margin_left = $left;
        $this->margin_top  = $top;
        $this->max['y']   += $top;
    }
    function setStyle($celda, $style){
        if(strpos($celda, ',') !== false) {
            $separado = explode(',', $celda);
            foreach ($separado as $l) {
                if(!empty($l)) {
                    $this->setStyle($l, $style);
                }
            }
            return;
        }
        if(!isset($this->styles[$celda])) {
            $this->styles[$celda] = array(
                'font'      => array(),
                'alignment' => array(),
                'fill'      => array(),
                'borders'   => array(),
                'others'    => array()
            );
        }
        if(!empty($style)){
            foreach($style as $kst => $vst) {
                $this->css_to_excel($celda, $kst, $vst);
            }
        }
    }
    function css_to_excel($celda, $key, $value) {
        if(!empty($key)) {
            $key = strtolower($key);
            if($key == 'font-size'){
                $this->styles[$celda]['font']['size'] = $value;

            } elseif($key == 'font-family'){
                $this->styles[$celda]['font']['name'] = $value;

            } elseif($key == 'color') {
                $this->styles[$celda]['font']['color'] = array('rgb' => $value);

            } elseif($key == 'text-align'){
                if($value == 'center') {
                    $this->styles[$celda]['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_CENTER;

                } elseif($value == 'left') {
                    $this->styles[$celda]['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;

                } elseif($value == 'right') {
                    $this->styles[$celda]['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
                }
            } elseif($key == 'vertical-align'){
                if($value == 'top') {
                    $this->styles[$celda]['alignment']['vertical'] = PHPExcel_Style_Alignment::VERTICAL_TOP;

                } elseif($value == 'middle') {
                    $this->styles[$celda]['alignment']['vertical'] = PHPExcel_Style_Alignment::VERTICAL_CENTER;

                } elseif($value == 'bottom') {
                    $this->styles[$celda]['alignment']['vertical'] = PHPExcel_Style_Alignment::VERTICAL_BOTTOM;
                }
            } elseif($key == 'background-color') {
              $value = str_replace('#', '', $value);
              $this->styles[$celda]['fill'] = array(
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => $value)
              );

            } elseif($key == 'border') {
              $this->styles[$celda]['borders'] = array(
                'allborders' => array(
                  'style'  => PHPExcel_Style_Border::BORDER_THIN,
                  'color' => array('rgb' => $value),
                ),
              );
            } elseif($key == 'font-weight') {
              $this->styles[$celda]['font']['bold'] = $value == 'bold';
            } elseif($key == 'rowspan') {
              $this->styles[$celda]['others']['rowspan'] = (int) $value - 1;
            } elseif($key == 'colspan') {
              $this->styles[$celda]['others']['colspan'] = (int) $value - 1;
            } elseif($key == 'filter') {
              $this->styles[$celda]['others']['filter'] = (boolean) $value;
            } elseif($key == 'width') {
              $this->styles[$celda]['others']['width'] = $value;
            } elseif($key == 'height') {
              $this->styles[$celda]['others']['height'] = $value;
            }
        }
    }
    function insertImage($url, $width = null, $height = null, $x = null, $y = null) {
        $img = new PHPExcel_Worksheet_Drawing();
        $img->setName($this->titulo . ':' . uniqid());
        $img->setDescription(utf8_encode($this->titulo));
        $img->setPath($url);
        if(!is_null($x)) {
            $img->setOffsetX($x);
        }
        if(!is_null($y)) {
            $img->setOffsetY($y);
        }
        if(!is_null($width)) {
            $img->setHeight($width);
        }
        if(!is_null($height)) {
            $img->setHeight($height);
        }
        $img->setWorksheet($this->ExcelBase->getActiveSheet());
        $img->setCoordinates($this->lastPoint);
    }
    function stepRow(){
      $this->max['y']++;
    }
    function stepCol() {
      $this->max['x']++;
    }
    function setPoint($point) {
      $this->lastPoint = $point;
    }
    function getLastRow($i = 0) {
      return $this->max['y'] + $i;
    }
    function getLastCol($i = 0) {
      return $this->n2l($this->max['x'] + $i);
    }
    function getLastPoint($i = 0) {
      return $this->getLastCol($i) . $this->getLastRow($i);
    }
/*    function point($point, $label) {
        $point = $this->__analyzePoint($point);
        if(!empty($point)) {
            $points = explode(':', $point);
            if(count($points) > 1) {
                $this->ExcelBase->getActiveSheet()->mergeCells($point);
            }
            $point = $points[0];
            $this->__insertPoint($label, $point);
        }
        
    }*/
    function setHeader($body) {
      if(!empty($body)) {
        $sty = array(
          'font-weight' => 'bold',
          'text-align'  => 'center'
        );
        $body = array_map(function($n) use($sty) {
          if(is_array($n)) {
            $n['style'] = array_merge($sty, $n['style']);
          } else {
            $n = array('text' => $n, 'style' => $sty);
          }
          return $n;
        }, $body);
        $body = array($body);
        $e = $this->setData($body, true);
        if($e === false) {
          //$e(false, null);
        }
      }
    }
    function insertPoint($label, $point) {
      return $this->__insertPoint($label, $point);
    }
    function insertLine($fila) {
      $this->max['y']++;
      $this->current['x'] = $this->margin_left + 1; 
      if(!empty($fila)) {
        foreach($fila as $i => $label) {
          $point = $this->n2l($this->current['x']) . $this->max['y'];
          while(in_array($point, $this->busy_cells) && $this->moving_cells) {
            $point = $this->__addcelltoPoint($point, 1, 0);
            $this->current['x']++;
          }
          $this->__insertPoint($label, $point);
          $this->current['x']++;
        }
      }
    }
    function setData($body, $border_cell = false) {
      if(!empty($body) && is_array($body)) {
        $ancho = $this->obtener_ancho_body($body);
        $this->max['x'] = ($ancho + $this->margin_left) > $this->max['x'] ? $ancho + $this->margin_left : $this->max['x'];
        $inicio = $this->max;
        foreach ($body as $i => $fila) {
          $this->insertLine($fila);
        }
        if(!empty($border_cell)) {
          $this->setStyle($this->n2l($this->margin_left + 1) . $inicio['y'] . ':' . $this->n2l($this->margin_left + $ancho) . $this->max['y'], array('border' => '000000'));
        }
      }
    }
    function __insertLabel($label, $point) {
      $tipo = PHPExcel_Cell_DataType::TYPE_STRING;
      if(is_numeric($label) && is_int($label)) {
        $tipo = PHPExcel_Cell_DataType::TYPE_NUMERIC;
      }
      $this->ExcelBase->getActiveSheet()->getStyle($point)->getAlignment()->setWrapText(true);
      $this->lastPoint = $point;
      $this->ExcelBase->getActiveSheet()->setCellValueExplicit($point, $label, $tipo);
    }
    function __insertPoint($label, $point) {
      if(!is_array($label) && !is_null($label)) {
        $this->__insertLabel($label, $point);
      } elseif(is_array($label) && in_array(count($label), [1,2,3])) {
        $this->__insertLabel($label['text'], $point);
        if(!empty($label['style'])) {
          if(!empty($label['style']['colspan'])) {
            $this->current['x'] += $label['style']['colspan'] - 1;
          }
          if(!empty($label['style']['rowspan']) && $label['style']['rowspan'] > 1) {
            for($i = 1; $i <= $label['style']['rowspan'] - 1; $i++) {
              $this->busy_cells[] = $this->__addcelltoPoint($point, 0, $i);
            }
          }
          $this->setStyle($point, $label['style']);
        }
      }
    }
    function __addcelltoPoint($point, $more_x = 0, $more_y = 0) {
      if(!empty($point)) {
        $point = strtoupper($point);
        if(preg_match("/^(?<ll>[A-Z]+)(?<nn>\d+)$/i", $point, $salida)) {
          return $this->n2l($this->l2n($salida['ll']) + $more_x) . '' .  ((int) $salida['nn'] + $more_y);
        }
      }
      return null;
    }
    function processStyle() {
      if(!empty($this->styles)) {
        $stylez = array();
        foreach($this->styles as $cell => $style_array) {
          $data = null;
          $cell = $this->__analyzePoint($cell, $data);
          if(!empty($style_array['others'])) {
            if(!empty($style_array['others']['width'])) {
              $cell = $data['from']['column'];
              $this->setSizeColumn($cell, $style_array['others']['width']);
            }
            if(!empty($style_array['others']['height'])) {
              $cell = $data['from']['row'];
              $this->setSizeRow($cell, $style_array['others']['height']);
            }
            if(!empty($style_array['others']['width']) || !empty($style_array['others']['height'])) {
              continue;
            }
          }
          $stylez[$cell] = $style_array;
        }
            foreach ($stylez as $cell => $style_array) {
                if(!empty($cell)) {
                    $others = $style_array['others'];
                    if(!empty($others)) {
                        $rowspan = !empty($others['rowspan']) ? $others['rowspan'] : 0;
                        $colspan = !empty($others['colspan']) ? $others['colspan'] : 0;
                        if($colspan >= 1 || $rowspan >= 1) {
                            $to = $this->__addcelltoPoint($cell, $colspan, $rowspan);
                            $this->ExcelBase->getActiveSheet()->mergeCells($cell . ':' . $to);
                            $cell = $cell . ':' . $to;
                        }
                        if(!empty($others['filter'])) {
                            $this->ExcelBase->getActiveSheet()->setAutoFilter($cell);
                        }
                    }
                    unset($style_array['others']);
                    $this->ExcelBase->getActiveSheet()->getStyle($cell)->applyFromArray($style_array);
                }
            }
            $this->styles = null;
        }
    }
    function __analyzePoint($k, &$data = null) {
      $data = array(
        'from' => array('column' => null, 'row' => null),
        'to'   => array('column' => null, 'row' => null),
      ); 
      if(!empty($k)) {
        $k = trim($k);
        if(preg_match("/^[\d]+$/i", $k)) {
          $k = 'A' . $k . ':' . $this->n2l($this->max['x']) . $k;
          $data = array(
            'type' => 'row',
            'from' => array('column' => 'A', 'row' => $k),
            'to'   => array('column' => $this->n2l($this->max['x']), 'row' => $k),
          );

        } elseif(preg_match("/^[A-Z]+$/i", $k)) {
          $min_y = $this->margin_top + 2;
          $data = array(
            'type' => 'column',
            'from' => array('column' => $k, 'row' => $min_y),
            'to'   => array('column' => $k, 'row' => $this->max['y']),
          );
          $k = $k . $min_y . ':' . $k . $this->max['y'];

        } elseif(preg_match("/^(?<c>[A-Z]+)(?<r>[\d]+)$/i", $k, $out)) {
          $data = array(
            'type' => 'cell',
            'from' => array('column' => $out['c'], 'row' => $out['r']),
            'to'   => array('column' => null, 'row' => null),
          );
          // Punto
        } elseif(preg_match("/^(?<c>[A-Z]+)(?<r>[\d]+)\:(?<cf>[A-Z]+)(?<rf>[\d]+)$/i", $k, $out)) {
          // Range
          $data = array(
            'type' => 'range',
            'from' => array('column' => $out['c'], 'row' => $out['r']),
            'to'   => array('column' => $out['cf'], 'row' => $out['rf']),
          );
        } else {
          $k = null;
        }
      }
      return $k;
    }
    function forceDownload($name) {
        $filename = $name . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $this->save();
        exit;
    }
    function save($save = 'php://output') {
        $this->buildHeaderandStyle();
        $this->ExcelBase->setActiveSheetIndex(0);
        $this->ExcelBase->getProperties()->setCreator("Infobox Peru")
        ->setLastModifiedBy("Infobox")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Infobox")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Infobox");                                      
        
        $objWriter = PHPExcel_IOFactory::createWriter($this->ExcelBase, 'Excel2007');
        $objWriter->save($save);
    }
    private function obtener_ancho_body($body) {
      $max = 0;
      if(!empty($body)) {
        foreach($body as $b) {
          if(is_array($b)) {
            $tmp = count($b);
            foreach($b as $c) {
              if(is_array($c) && !empty($c[1]) && !empty($c[1]['colspan'])) {
                $tmp += $c[1]['colspan'] - 1;
              }
            }
            $max = $max > $tmp ? $max : $tmp;
          }
        }
      }
      return $max;
    }
}
