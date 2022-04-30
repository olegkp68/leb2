<?php

class anwbExport {
	public function __construct(&$ref,&$validator) {
		$this->ref = $ref; 
		$this->validator = $validator; 
	}
	
	public function toFormat($key, $value) {
		
		$ca = array('order_salesPrice', 'order_subtotal', 'refunded_order_subtotal', 'product_subtotal', 'order_tax', 'product_discountedPriceWithoutTax', 'product_quantity'); 
		if (in_array($key, $ca)) {
			return number_format((float)$value, 2, '.', ''); 
		}
		if ($key === 'order_status_name') {
			$value = JText::_($value); 
		}
		if ($key === 'created_on') return $value; 
		//if (is_numeric($value)) return "'".$value; 
		return $value; 
		return "'".$value;
	}
	
	public function runExport($startdate='', $enddate='', $start_order_id=0, $end_order_id=0) {
		
		$q = 'select o.virtuemart_order_id, h.created_on, o.order_number, o.order_salesPrice, o.order_subtotal, \'\' as `refunded_order_subtotal`, o.order_tax, o.coupon_code';
		$q .= ',i.order_item_sku, i.order_item_name, i.product_discountedPriceWithoutTax, i.product_quantity '; 
		//$q .= ' , u.first_name, u.last_name, u.phone_1, u.address_1, u.house_nr, u.addon, u.city, u.zip, u.email';
		$q.= ', s.order_status_name, o.order_status from #__virtuemart_orders as o ';
		//$q .= ' inner join #__virtuemart_order_userinfos as u on (u.virtuemart_order_id = o.virtuemart_order_id and u.address_type = \'BT\') '; 
		$q .= ' inner join #__virtuemart_order_histories as h on ((h.virtuemart_order_id = o.virtuemart_order_id) and (h.order_status_code = o.order_status)) ';
		$q .= ' inner join #__virtuemart_orderstates as s on (s.order_status_code = o.order_status) '; 
		$q .= ' inner join #__virtuemart_order_items as i on (i.virtuemart_order_id = o.virtuemart_order_id) '; 
		 
		
		$where = $this->getWhere($startdate, $enddate, $start_order_id, $end_order_id);
		$q .= $where; 
		$q .= ' and o.order_status IN (\'S\', \'R\') '; 
		$q .= ' order by h.created_on asc '; 
		
		
		//echo $q; die(); 
		$db = JFactory::getDBO(); 
		$db->setQuery($q); 
		$res = $db->loadAssocList(); 
		
		
		
		return $this->getXLS($res, $startdate, $enddate); 
	}
	
	private function getXLS($data, $startdate, $enddate)
	{
		
@ini_set("memory_limit",-1);
if (!file_exists(JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php')) die('Cannot find PHPExcel in '.JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel');
require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'export_helper.php');
require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel.php');
require_once ( JPATH_ROOT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Classes'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'IOFactory.php');

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$templatefile = __DIR__.DIRECTORY_SEPARATOR.'template.xlsx'; 
$templatefile_tmp = __DIR__.DIRECTORY_SEPARATOR.'template.'.time().'tmp.xlsx'; 

$objReader = PHPExcel_IOFactory::createReaderForFile($templatefile);
$objPHPExcel = $objReader->load($templatefile);






$ccx = 0; 
while (@ob_end_clean() ) {
	$ccx++; 
	if ($ccx > 20) break; 
}

$order_ids = array(); 
$first = array('created_on', 'order_number', 'order_subtotal', 'order_tax', 'coupon_code', 'order_status_name' ); 

$firstSheet = array(); 
$secondSheet = array(); 
foreach ($data as $k=>$drow) {
	
	$ccode = $drow['coupon_code']; 
	$ret = $this->validator->validateCouponSyntax($ccode); 
	if ($ret->ret !== true) {
		unset($data[$k]); 
		continue; 
	}
	$order_id = (int)$drow['virtuemart_order_id']; 
	$order_ids[$order_id] = $order_id; 
	if (empty($firstSheet[$order_id])) {
	$firstSheet[$order_id] = array(); 
	foreach ($drow as $kk=>$vv) {
		if (in_array($kk, $first)) {
		 $firstSheet[$order_id][$kk] = $vv; 
		}
	}
	
	if ($drow['order_status'] === 'R') {
		//$firstSheet[$order_id]['order_salesPrice']  = $firstSheet[$order_id]['order_salesPrice']  * -1; 
		$firstSheet[$order_id]['refunded_order_subtotal']  = $firstSheet[$order_id]['order_subtotal']  * -1; 
		$firstSheet[$order_id]['order_subtotal'] = '0.00'; 
		$firstSheet[$order_id]['order_tax']  = '0.00'; //$firstSheet[$order_id]['order_tax']  * -1; 
	}
	
	}
	$sRow = array(); 
	$sRow['created_on'] = $drow['created_on']; 
	$sRow['order_number'] = $drow['order_number']; 
	$sRow['Lidmaatschapsnummer'] = $drow['coupon_code']; 
	$sRow['sku'] = $drow['order_item_sku']; 
	$sRow['productnaam'] = $drow['order_item_name']; 
	$sRow['product_subtotal'] = floatval($drow['product_discountedPriceWithoutTax']) * intval($drow['product_quantity']); 
	if ($drow['order_status'] === 'R') {
		$sRow['product_subtotal']  = $sRow['product_subtotal']  * -1; 
	}
	$secondSheet[] = $sRow; 
}




$f = reset($firstSheet); 
$keys = array_keys($f);
$col = 0;
$row = 5;

$indexKeys = array(); 

for ($i=0; $i<count($keys)+1; $i++) {
	
	$val = $objPHPExcel->setActiveSheetIndex(0)
             ->getCellByColumnAndRow($i, $row)->getValue();

	foreach ($keys as $kname => $xx) {
		if ($xx == $val) {
			$indexKeys[$xx] = $i; 
		}
	}
}


/*
foreach ($f as $key=>$val)
{
 $objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow($col, $row, $key);
 $col++;
  
}
*/
$row++;
$col=0;
foreach ($firstSheet as $k => $value)
{
 foreach ($value as $k2 => $colval)
 {
	 
	 $colval = $this->toFormat($k2, $colval); 
	 if (!isset($indexKeys[$k2])) continue; 
	$col = $indexKeys[$k2]; 
 	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValueByColumnAndRow($col, $row, $colval);
    //$col++;
	
	 
	
            
 }
 unset($data[$k]);
 $row++;
 $col=0;
}

$row--; 
$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(1, 1, '=SUM(C6:C'.$row.')');

$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(3, 1, $startdate);

if (empty($enddate)) {
	$enddate = date("Y-m-d H:i:s"); 
}
			 
$objPHPExcel->setActiveSheetIndex(0)
             ->setCellValueByColumnAndRow(5, 1, $enddate);

$row = 1; 
$col = 0; 
$f = reset($secondSheet); 
$keys = array_keys($f);


foreach ($f as $key=>$val)
{
 $objPHPExcel->setActiveSheetIndex(1)
             ->setCellValueByColumnAndRow($col, $row, $key);
 $col++;
  
}
$row++;
$row++;
$col=0;
foreach ($secondSheet as $k => $value)
{
 foreach ($value as $k2 => $colval)
 {
	 
	 $colval = $this->toFormat($k2, $colval); 
	 
	 $test = trim($colval); 
	 //if (substr($test, 0,1)==='=') $colval = "'".$colval; 
 	$objPHPExcel->setActiveSheetIndex(1)
            ->setCellValueByColumnAndRow($col, $row, $colval);
    $col++;
	
	
	
            
 }
 unset($data[$k]);
 $row++;
 $col=0;
}


//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$tmp = $templatefile_tmp;
$objWriter->save($templatefile_tmp); 

unset($objWriter); 
$objWriter = null; 

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="export.xlsx"');
header('Cache-Control: max-age=0');
$handle = fopen($templatefile_tmp, 'r'); 
if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle, 4096);
        // Process buffer here..
		echo $buffer; 
    }
    fclose($handle);
}
unlink($templatefile_tmp); 
$handle = null; 

JFactory::getApplication()->close();



	}
	
	
	private function getWhere($startdate='', $enddate='', $startid=0, $endid=0) {
		$where = array();

$db = JFactory::getDBO(); 
 
 if (!empty($startdate)) {
if (!empty($startdate)) $startdate = strtotime($startdate) == -1 ? '' :  strtotime($startdate);
$startdate = (int)$startdate; 
$startdate = date("Y-m-d H:i:s", $startdate); 
 }

if (!empty($startdate)) $where[] = ' h.created_on >= "'.$db->escape($startdate).'" '; 

if (!empty($enddate)) {
if (!empty($enddate)) $enddate = strtotime($enddate) == -1 ? '' :  strtotime($enddate);

if (!empty($enddate)) 
{
$enddate =  $enddate+60*60*24-1;


$enddate = date("Y-m-d H:i:s", $enddate); 

//if (!empty($where)) $where .= ' and ';
 //else $where = ' where ';
$where[] = ' h.created_on <= "'.$db->escape($enddate).'" ';
}
}

if (!empty($startid)) $where[] = ' o.virtuemart_order_id >= '.(int)$startid.' ';
if (!empty($endid)) 
{
 
 $where[] = ' o.virtuemart_order_id <= '.(int)$endid.' ';
}
$where[] .= ' (o.`coupon_code` <> \'\' and o.`coupon_code` IS NOT NULL) '; 
		return ' where '.implode(' and ', $where); 
	}
	
}