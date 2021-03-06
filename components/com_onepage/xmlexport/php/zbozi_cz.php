<?php
/*
*
* @copyright Copyright (C) 2007 - 2013 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* One Page checkout is free software released under GNU/GPL and uses code from VirtueMart
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* 
* stAn note: Always use default headers for your php files, so they cannot be executed outside joomla security 
*
*/

defined( '_JEXEC' ) or die( 'Restricted access' );


// all classes should be named by <element>Xml per it's manifest with upper letter for the element name and the Xml
class Zbozi_czXml {
 function clear()
  {
  }
  function startHeaders()
  {
     $xml = '<?xml version="1.0" encoding="utf-8"?>'."\n"; 
	 $xml .= '<SHOP xmlns="http://www.zbozi.cz/ns/offer/1.0">'."\n"; 
	 
	 return $xml; 
	 
  }
  function addItem($product, $vm1)
  {
	 
	 
	  
	  $type = 'new'; 
	  if (!empty($this->config->bazaar))
	  {
		  if (stripos($a, ',')===false)
		  {
			  $bc = (int)$a; 
			  if (!empty($bc))
				  {
					  if (in_array($bc, $product->categories))
					  {
						  $type = 'bazaar'; 
						 
					  }
				  }
		  }
		  else
		  {
		  $a = explode(',', $this->config->bazaar); 
		  if (is_array($a))
		  {
			  foreach ($a as $bc)
			  {
				  $bc = (int)$bc; 
				  if (!empty($bc))
				  {
					  if (in_array($bc, $product->categories))
					  {
						  $type = 'bazaar'; 
						 
					  }
				  }
			  }
		  }
		  }
	  }
	  
	  
          extract($vm1); 
  
  
  if (!empty($this->config->xml_export_unique))
			{
				$z = (int)$this->config->xml_export_unique; 
				if ($z === 1) {
				 if (!empty($sku))
				 {
				 $product_id = $sku; 
				 }
				}
			}
			
  
     			// zaciatok shopitem
			$data = '<SHOPITEM>'."\n";
			$data .= '<ITEM_ID>'.$product_id.'</ITEM_ID>'."\n";
			//$data .= '<SKU><![CDATA['.$sku.']]></SKU>'."\n";
			//$data .= '<ITEM_ID><![CDATA['.$sku.']]></ITEM_ID>'."\n";
			$data .= '<PRODUCTNAME><![CDATA['.$product_name.']]></PRODUCTNAME>'."\n";
			//$data .= '<PRODUCT><![CDATA['.$product_name.']]></PRODUCT>'."\n";
			//$data .= '<SUBTITLE><![CDATA['.$product_name.']]></SUBTITLE>'."\n";
			$data .= '<IMGURL><![CDATA['.$thumb_url.']]></IMGURL>'."\n";
			//$data .= '<FULLIMAGE><![CDATA['.$fullimg.']]></FULLIMAGE>'."\n";
			
			if ((!empty($fulldesc)) && (empty($desc)))
			{
				$desc = $fulldesc; 
				$desc = str_replace('<![CDATA[', "\n", $desc); 
				$desc = str_replace(']]>', "\n", $desc); 
				$desc = strip_tags($desc); 
			}
			
			$data .= '<DESCRIPTION><![CDATA['.$desc.']]></DESCRIPTION>'."\n";
			/*
			
			
			$data .= '<FULLDESCRIPTION><![CDATA['.$fulldesc.']]></FULLDESCRIPTION>'."\n";
			*/
			$data .= '<URL><![CDATA['.$link .']]></URL>'."\n";
			
			$live_site = $this->config->xml_live_site; 
			
			/*
			if (!empty($images))
			{
			$data .= '<IMAGES>'."\n";
			foreach ($images as $im)
			{
			
			$data .= "\t".'<IMAGE>'."\n"; 
			if (substr($im['file_name'], 0, 4) != 'http')
			$data .= "\t"."\t".'<PATH><![CDATA['.$live_site.$im['file_name'].']]></PATH>'."\n";
			else
			$data .= "\t"."\t".'<PATH><![CDATA['.$im['file_name'].']]></PATH>'."\n";
			
			$data .= "\t"."\t".'<TITLE><![CDATA['.$im['file_title'].']]></TITLE>'."\n";
			$data .= "\t".'</IMAGE>'."\n"; 
			}
			$data .= '</IMAGES>'."\n";
			}
			else $data .= '<IMAGES />'."\n"; 
			
			$data .= '<NONSEOLINK><![CDATA['.$node_link.']]></NONSEOLINK>'."\n";
			*/
			$t1 = (float)$cena_s_dph; 
			if ($t1 <= 0) return ''; 
			
			if (empty($cena_s_dph)) return ''; 
			
			$cena_s_dph = number_format ( $cena_s_dph, 2, '.', ''); 
			
			$z = (int)$cena_s_dph; 
			$z2 = floatval($cena_s_dph); 
			if ($z == $z2)
			{
				$cena_s_dph = (int)$cena_s_dph; 
			}
			if (empty($cena_s_dph)) return ''; 
			if ($cena_s_dph === '0,00') return ''; 
			
			$data .= '<PRICE_VAT>'.$cena_s_dph.'</PRICE_VAT>'."\n";
			
			//if ($cena_txt === '0,00') return ''; 
			//$data .= '<PRICE><![CDATA['.$cena_txt.']]></PRICE>'."\n";
			$tax_rate = number_format ( $tax_rate, 2, '.', ''); 
			//$data .= '<VAT>'.$tax_rate.'</VAT>'."\n"; 
			$data .= '<ITEM_TYPE>'.$type.'</ITEM_TYPE>'."\n";
			/*
			if ($product2->is_override)
			{
				$data .= '<ISCATEGORIZED>1</ISCATEGORIZED>'; 
			}
			*/
			
			if (!empty($longest_cats))
			{
			//<CATEGORYTEXT>Obuv | P??nska | Be??eck?? | Nike</CATEGORYTEXT>
			 $lcats = implode(' | ', $longest_cats); 
			}
			else return; 
			
			
			
			
			    if ((!empty($product->paired_category_name))
					 && (!empty($product->pairedObj))
						&& (!empty($product->pairedObj->id )))
				{
					$Cid = (int)$product->pairedObj->id; 
					
					$data .= '<CATEGORY_ID>'.$Cid.'</CATEGORY_ID>'."\n";
				}
				
				/*
				else
				{ 
    			 $data .= '<CATEGORYTEXT><![CDATA['.$lcats.']]></CATEGORYTEXT>'."\n";
				}
				*/
				//$data .= '<EAN />'."\n";
    			//$data .= '<CATEGORY_PATH><![CDATA['.$kat2.']]></CATEGORY_PATH>'."\n";
				/*
				if (empty($manufacturer))
				{
				$isset = false; 
				if (!empty($attribs))
				{
				  foreach ($attribs as $k=>$v)
				   {
				     if ($v['virtuemart_custom_id']==90)
					 {
					 $data .= '<MANUFACTURER><![CDATA['.$v['custom_value'].']]></MANUFACTURER>'."\n";
					 $isset = true; 
					
					 break; 
					 
					 
					 }
				   }
				}
				if (!$isset)
				$data .= '<MANUFACTURER />'."\n";
				}
				*/
				if (!empty($manufacturer))
    			$data .= '<MANUFACTURER><![CDATA['.$manufacturer.']]></MANUFACTURER>'."\n";
				/*
				if (empty($manufacturer_id))
				$data .= '<MANUFACTURER_ID />'."\n";
				else
    			$data .= '<MANUFACTURER_ID>'.$manufacturer_id.'</MANUFACTURER_ID>'."\n";
				*/
			/*
    			$data .= '<AVAILABILITY>'.$avaitext.'</AVAILABILITY>'."\n";
				$data .= '<AVAILABILITY_DAYS><![CDATA['.$avaidays.']]></AVAILABILITY_DAYS>'."\n";
				
    			$data .= '<AVAILABILITY_OBR>'.$avai_obr.'</AVAILABILITY_OBR>'."\n";
				*/
    			if (empty($ean))
				if (!empty($product->product_gtin))
				{
					if (is_numeric($product->product_gtin))
					{
						$ean = $product->product_gtin; 
					}
				}
				
				if (!empty($ean))
				$data .= '<EAN><![CDATA['.$ean.']]></EAN>'."\n";
			
			    /*
    			if (!empty($atr))
				$data .= '<ATTRIBUTES><![CDATA['.$atr.']]></ATTRIBUTES>'."\n";
    			$avaidays = (int)$avaidays; 
				*/
				
				if (!empty($product->product_in_stock))
				{
					$pi = (int)$product->product_in_stock; 
					if ($pi > 0)
					{
						$avaidays = 0; 
					}
				}
				
				$data .= '<DELIVERY_DATE>'.$avaidays.'</DELIVERY_DATE>'."\n";
				/*
				if ($published)
    			$data .= '<PUBLISHED>Y</PUBLISHED>'."\n";
				else
				$data .= '<PUBLISHED>N</PUBLISHED>'."\n";
				*/
				if (!$published) return; 
				
				
				if (!empty($product->customfields))
				{
				   foreach ($product->customfields as $cf)
				    {
					  
					}
				}
				
		//	echo '<output>'.var_export($id, true).'</output>'."\n";
 			$data .= '</SHOPITEM>'."\n";
			
			
			return $data; 

  }
  function endHeaders()
  {
	 $xml = '</SHOP>'."\n"; 
	 return $xml; 
  
  }
  function compress()
  {
  }
  
  
  
   
   
   function processPairingData($xml, &$converted)
  {
	 // $z = str_getcsv($xml, ',', '"'); 
	//echo $xml; die();  
  $lines = explode("\r\n", $xml); 
  
   $return = new xmlCategory(); 
  
  $ret = array();  
  $i =0; 
  foreach ($lines as $k=>$line)
   {     
     $i++; 
	 if ($i === 1) continue; 
     //get rid of the rest characters
     $lines[$k] = str_replace("\r", '', $line); 
	 if (substr($line, 0, 1)==='#') continue; 
	 $hash = $k;  
	 
	 //$ret[$hash] = $line; 
	 
	 
	 $z = str_getcsv($line, ',', '"'); 
	 if (count($z) != 3) continue; 
	 $id = $z[0]; 
	 $name = $z[1]; 
	 $path = $z[2]; 
	 $txt = $name.' ~|~ '.$path; 
	 //$new = new xmlCategory($id, $txt); 
	 $new =& $return::getInstance($id, $txt); 
	 $return->addItem($new); 
   }
   
   
   
   
   
  
   
   return $return; 


	   
  }
   
 
  
  
  
}
 