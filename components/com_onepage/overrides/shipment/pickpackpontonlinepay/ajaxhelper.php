<?php
/* 
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
*/
defined('_JEXEC') or die('Restricted access');

	 
	  if (stripos($shipping_method, 'jelolo()')!==false)
	 {
	    $shipping_method = str_replace('\'jelolo();\'', '\'jelolo3();\'', $shipping_method); 
		$html = str_replace('\'jelolo();\'', '\'jelolo3();\'', $html); 
		
		
	 }