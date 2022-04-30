<?php

class exampleExport {
	public function __construct(&$ref,&$validator) {
		$this->ref = $ref; 
		$this->validator = $validator; 
	}
	
	
	
	
	public function runExport($startdate='', $enddate='', $start_order_id=0, $end_order_id=0) {
		$where = $this->getWhere($startdate, $enddate, $start_order_id, $end_order_id);
		
	}
	
	
	private function getWhere($startdate='', $enddate='', $startid=0, $endid=0) {
		$where = array();

$db = JFactory::getDBO(); 
 
 if (!empty($startdate)) {
if (!empty($startdate)) $startdate = strtotime($startdate) == -1 ? '' :  strtotime($startdate);
$startdate = (int)$startdate; 
$startdate = date("Y-m-d H:i:s", $startdate); 
 }

if (!empty($startdate)) $where[] = ' o.created_on >= "'.$db->escape($startdate).'" '; 

if (!empty($enddate)) {
if (!empty($enddate)) $enddate = strtotime($enddate) == -1 ? '' :  strtotime($enddate);

if (!empty($enddate)) 
{
$enddate =  $enddate+60*60*24-1;


$enddate = date("Y-m-d H:i:s", $enddate); 

//if (!empty($where)) $where .= ' and ';
 //else $where = ' where ';
$where[] = ' o.created_on <= "'.$db->escape($enddate).'" ';
}
}

if (!empty($startid)) $where[] = ' o.virtuemart_order_id >= '.(int)$startid.' ';
if (!empty($endid)) 
{
 
 $where[] = ' o.virtuemart_order_id <= '.(int)$endid.' ';
}
$where[] .= ' o.coupon_code <> \'\' '; 
		return ' where '.implode(' and ', $where); 
	}
	
}