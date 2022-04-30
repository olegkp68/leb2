<?php
/**
 * IceVmCategory Extension for Joomla 2.5 By IceTheme
 * 
 * 
 * @copyright	Copyright (C) 2008 - 2012 IceTheme.com. All rights reserved.
 * @license		GNU General Public License version 2
 * 
 * @Website 	http://www.icetheme.com/Joomla-Extensions/iceaccordion.html
 * @Support 	http://www.icetheme.com/Forums/IceVmCategory/
 *
 */
 
 
 class vmCategoryHelper{
	
	public static function getCategories($order = 'id', $ordering = 'asc', $publish = 0, $params, $active_cat=0) {
		$db = JFactory::getDBO();
        
        $add_where = ($publish)?(" C.published = '1' "):("");        
        if ($order=="id") $orderby = "category_id";
        if ($order=="name") $orderby = "L.`name`";
        if ($order=="ordering") $orderby = "CC.`ordering`";
        if (empty($order) || (empty($orderby))) $orderby = "CC.`ordering`";
        
		
		$query = 'SELECT L.category_name AS name,L.virtuemart_category_id AS category_id,C.published as category_publish, C.ordering,CC.category_parent_id  FROM `#__virtuemart_categories_'.VMLANG.'` as L';
		$query .= ' JOIN `#__virtuemart_categories` as C using (`virtuemart_category_id`)';
		$query .= ' LEFT JOIN `#__virtuemart_category_categories` as CC on C.`virtuemart_category_id` = CC.`category_child_id`';
		$query .= " WHERE " . $add_where. " ORDER BY ".$orderby." ".$ordering;
		
		
		
		$cfd2 = JPATH_CACHE.DS.'ice_menu'; 
	
		if (!file_exists($cfd2)) {
			mkdir($cfd2); 
		}
		$cat_file = $cfd2.DIRECTORY_SEPARATOR.'categories.php';
		
		$caching = (int)$params->get('cache', 0); 
		
		if ((!file_exists($cat_file)) || ($caching === 0))
		{
         $db->setQuery($query);
         $categories = $db->loadAssocList();
		 
		 
		 
		 
		 /*
		 $fx = '<?php $categories = array( '; 
		 foreach ($categories as $key => $row) {
			 $fx .= $key.' => new stdClass(5) { '; 
			 $fx .= ' public $name =
			 $fx .= ' } '; 
		 }
		 $fx .= ');'; 
		 */
		 $fx = '<?php $categories = '.var_export($categories, true).'; '; 
		 file_put_contents($cat_file.'.tmp', $fx); 
		 rename ($cat_file.'.tmp', $cat_file); 
		}
		else 
		{
			
			include($cat_file); 
		}
		
		
		
		foreach ($categories as $k => $v) {
			$categories[$k] = (object)$v; 
		}
		
		
		
		
        $imageWidth = $params->get('image_width', 20);
		$imageHeight = $params->get('image_heigth', 20); 
		$show_image = $params->get('show_image',0);
		
		$isThumb = true;
		if( empty($categories) ) return '';
		if(!class_exists('VirtueMartModelCategory')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'category.php'); 
		$categoryModel = VmModel::getModel('category');
		if(!class_exists('TableCategory_medias')) require(JPATH_VM_ADMINISTRATOR.DS.'tables'.DS.'category_medias.php'); 
		
		
		
		if (!empty($show_image)) {
        foreach ($categories as $key => $value){
			
			$xrefTable = new TableCategory_medias($db);
			$categories[$key]->virtuemart_media_id = $xrefTable->load($value->category_id);

            $categories[$key]->category_link = JRoute::_ ( 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $value->category_id );
        }
		$categoryModel->addImages($categories,1);
		
		if(!empty($categories)){
			foreach($categories as $key=>$category){
				
				$image = isset($category->images[0])?$category->images[0]:null;
				$categories[$key]->category_image = "";
				if(!empty($image)){
					if(file_exists( $image->file_url)){
						if( $image->file_url &&  $image= self::renderThumb($image->file_url, $imageWidth, $imageHeight, $categories[$key]->name, true) ){
							$categories[$key]->category_image = $image;
						}
					}
				}
			}
		}
		
		}
		else {
		  foreach ($categories as $key => $value){
			
            $categories[$key]->category_link = JRoute::_ ( 'index.php?option=com_virtuemart&view=category&virtuemart_category_id=' . $value->category_id );
        }
		}
		$children = array();
		if ( $categories )
		{
			foreach ( $categories as $v )
			{				
				$pt 	= $v->category_parent_id;
				$list 	= @$children[$pt] ? $children[$pt] : array();
				array_push( $list, $v );
				$children[$pt] = $list;
				
				
				
			}
		}
		
		self::$items = array(); 
		foreach ($categories as $i)
		{
			//if ($i->category_id == 600) { die('1'); }
			
			if (!isset(self::$items[$i->category_id])) self::$items[$i->category_id] = new stdClass(); 
			if (!isset(self::$items[$i->category_parent_id])) self::$items[$i->category_parent_id] = new stdClass(); 
			if (!isset(self::$items[$i->category_parent_id]->children)) 
			{
			
			self::$items[$i->category_parent_id]->children = array(); 
			}
			self::$items[$i->category_parent_id]->children[$i->category_id] =&  self::$items[$i->category_id]; 
		}
		$z = array(); 
		
		
		
		
		//debug_zval_dump($items); die(); 
		/*
		13482 - vyskach
13478 - hlava
13455 - dopl
13456 - dorg
13483 - obuv
13485 - rukav
13484 - odev

a.       pracovné odevy
b.      pracovné topánky
c.       pracovné rukavice
d.      ochrana hlavy
e.      práca vo výškach
f.        doplnky
g.       drogéria a upratovanie


13484,13483,13485,13478,13478,13482,13455,13456
		*/
		
		return $children;
    }
	static $items; 
	
	public static function getHtml($order = 'id', $ordering = 'asc', $publish = 0, $params){
		$children = self::getCategories($order, $ordering, $publish, $params);
		
		$topcats = $params->get('topcats', null); 
		
		
		if (!empty($topcats)) {
			$nc = array(); 
			$ea = explode(',', $topcats);
			
			foreach ($ea as $tc)			
			{
			   foreach ($children[0] as $x=>$cat) {
				   if ($cat->category_id == $tc) {
						$nc[] = $cat; 
						unset($children[0][$x]); 
				   }
			   }
			}
			if (!empty($nc)) {
			 $children[0] = $nc; 
			}
		}
		
		JFactory::getLanguage()->load('mod_ice_vm_categories', __DIR__); 
		
		$html = '';
		$cats = array(); 
		$html = self::getHtmlCate($children,0,$html, 0 ,$params);
		$sg_prices = $params->get('show_my_sg_prices', false); 
		$sg_prods = $params->get('show_my_sg_prods', false); 
		$sg_all = $params->get('show_my_sg_prods_and_prices', false); 
		
		$sg_prices_html = ''; 
		if ($sg_prices) {
			$sale_link = JRoute::_('index.php?option=com_rupsearch&view=search&tab=Akcia&virtuemart_category_id[]=0&primary_virtuemart_category_id=0&qf[]=11'); 
			$sg_prices_html = '<li class="sg_prices lofitem1 ice-parent"><a href="'.$sale_link.'"><span>Moja Cenová Ponuka</span></a></li>'; 
			
		}
		$sg_products_html = ''; 
		if ($sg_prods) {
			$sale_link = JRoute::_('index.php?option=com_rupsearch&view=search&tab=Akcia&virtuemart_category_id[]=0&primary_virtuemart_category_id=0&qf[]=12'); 
			$sg_products_html = '<li class="sg_prods lofitem1 ice-parent"><a href="'.$sale_link.'"><span>Moje Produkty a Služby</span></a></li>'; 
		}
		
		$sg_products_all_html = ''; 
		if ($sg_all) {
			$sale_link = JRoute::_('index.php?option=com_rupsearch&view=search&tab=Akcia&virtuemart_category_id[]=0&primary_virtuemart_category_id=0&qf[]=13'); 
			$sg_products_html = '<li class="sg_all lofitem1 ice-parent"><a href="'.$sale_link.'"><span>'.JText::_('MOD_ICE_VM_CATEGORIES_MYPRODUCTS').'</span></a></li>'; 
		}
		$html = $sg_prices_html.$sg_products_html.$html;
		$html = "<ul class='lofmenu cat_0' id='lofmainul'>".$html."</ul>";
		return $html;
	}
	static $_listcates = array();

	public static function getListCates( ){
		return;
		static $_listcates;
		if(empty( $_listcates )){
			$category_id = JRequest::getCmd('virtuemart_category_id', 0);
			if(!empty($category_id)){
				$db = &JFactory::getDBO();
				$tmp[ $category_id ] = $category_id;
				/*Select children category ids*/
				$query = "SELECT L.category_name as name, L.virtuemart_category_id AS category_id, CC.category_parent_id, C.published as category_publish FROM `#__virtuemart_categories_".VMLANG."` as L
				JOIN `#__virtuemart_categories` as C using (`virtuemart_category_id`)
				LEFT JOIN `#__virtuemart_category_categories` as CC on C.`virtuemart_category_id` = CC.`category_child_id`
				WHERE C.published = '1' ORDER BY category_parent_id, C.ordering";
				
				$db->setQuery($query);
				$all_cats = $db->loadObjectList();
				$tmp2 = array();
				if(count($all_cats)) {
					foreach ($all_cats as $key => $value) {
						$tmp2[ $value->category_id ] = $value->category_parent_id;
						if(!empty( $value->category_id ) && in_array($value->category_id, $tmp)){
							$tmp[ $value->category_parent_id ] = $value->category_parent_id;
							foreach($tmp2 as $key=>$val){
								if( !empty($key)  && !empty($val) && in_array($key, $tmp)){
									$tmp[ $val ] = $val;
								}
							}
						}
					}
				}
				$_listcates = $tmp;
				return $_listcates;
			}
		}
		else{
			return $_listcates;
		}
	}
	
	public static function getChildren($items, &$ret)
	{
		if (!empty($items->children))
		{
			foreach ($items->children as $id=>$z)
			{
				$ret[] = $id; 
				if (!empty($z) && (!empty($z->children))) 
					self::getChildren($z, $ret);
			}
		}
		return $ret; 
	}
	
	public static function getHtmlCate($children, $id = 0 , $str, $leve = 0 , $params){
		
		$str = $extra = ''; 
		$show_image = $params->get('show_image', 0); 
		$showcounter = $params->get('showcounter', 0); 
		$cates = self::getListCates();
		if(empty($cates)){
			$cates = array();
		}
		
		
		
		$leve ++;
		if(!empty($children[$id])){
			
			
			foreach($children[$id] as $item){
				
			if (empty($id))
			{
			 $extra = '';
			}
			   
			    $last_extra = ' hascat_'.$item->category_id;
				$extra .= $last_extra; 
			
				
				
				$class = "";
				/*
				if(in_array($item->category_id, $cates)){
					$class = " ice-current ";
				}
				*/
				
				//$cats[] = $item->category_id; 
				$cats = array(); 
				$save_cats = $cats; 
				if(!empty($children[$item->category_id])){
					$class .= " ice-parent ";
				}
				
				
					$extra = ''; 
				if (!empty(self::$items[$item->category_id]))
					{
					  $cats = array(); 
					  self::getChildren(self::$items[$item->category_id], $cats);
					}
				foreach ($cats as $c)
					{
						$extra .= ' hascat_'.$c.' '; 
					}
				
				$str .= "<li class='cat_".$item->category_id." lofitem".$leve.$class." ".$extra."'>";
				$str .= "<a href='".$item->category_link."' >".($show_image ? $item->category_image : "")."<span>".$item->name.($showcounter ? " <span class=\"counter\">(".self::getTotalItem($children,$item->category_id).") </span>" : "")."</span>";
				
				
				if(!empty($children[$item->category_id])){
					$str .= "<i></i></a>";
					
					
					
					$cats = array(); 
					$str2 = self::getHtmlCate($children, $item->category_id ,$str ,$leve, $params);
					
					
					
					$str3 = "<ul class=\"cat_".$item->category_id." ".$extra."\">";
					
					
					
					$str .= $str3.$str2; 
					
					if (empty($str2)) $extra = ''; 
					
					if ($leve === 1) {
						$show_sale = $params->get('show_vypredaj', 0);
						if (!empty($show_sale)) {
						$sale_link = JRoute::_('index.php?option=com_rupsearch&view=search&tab=Akcia&virtuemart_category_id[]='.$item->category_id.'&primary_virtuemart_category_id='.$item->category_id.'&qf[]=1'); 
						$str .= '<li class="sale"><a href="'.$sale_link.'">'.JText::_('MOD_ICE_VM_CATEGORIES_SALE').'</a></li>'; 
						}
						$show_akcia = $params->get('show_akcia', 0);
						if (!empty($show_akcia)) {
						$akcia_link = JRoute::_('index.php?option=com_rupsearch&view=search&tab=Akcia&virtuemart_category_id[]='.$item->category_id.'&primary_virtuemart_category_id='.$item->category_id.'&qf[]=2'); 
						$str .= '<li class="akcia sale"><a href="'.$akcia_link.'">'.JText::_('MOD_ICE_VM_CATEGORIES_AKCIA').'</a></li>'; 
						}
					}
					
					$str .= "</ul>";
				}else{
					$str .= "</a>";
					$cats = $save_cats; 
				}
				$str .="</li>";
			}
		}
		
		
		
		return $str;
	}
	/*
	* get Total item in  category
	* return integer
	*/
	public static function getTotalItem($children, $category_id){
		$arrCate = array();
		$arrCate = self::getAllSubcates($category_id);
		if(empty($arrCate)){
			return 0;
		}
		if(count($arrCate) == 1){
			$where = " WHERE pc.virtuemart_category_id = ".$arrCate[0]. " ";
		}else{
			$strCate = implode(',',$arrCate);
			$where = " WHERE pc.virtuemart_category_id IN (".$strCate.") ";
		}
		$where .= ' and p.product_parent_id = 0 and p.virtuemart_product_id = pc.virtuemart_product_id and p.published = 1'; 
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(DISTINCT pc.virtuemart_product_id) AS total FROM `#__virtuemart_product_categories` as pc, `#__virtuemart_products` as p  ".$where;
		
		$db->setQuery($query);
        $total = $db->loadObject();
		return $total->total;
	}
	/*
	* get all subcategories
	* return array
	*/
	public static function getAllSubcates($category_id){
		$db = JFactory::getDBO();
		$tmp[] = $category_id;
		$query = "SELECT L.category_name as name, L.virtuemart_category_id AS category_id, CC.category_parent_id, C.published as category_publish FROM `#__virtuemart_categories_".VMLANG."` as L
				JOIN `#__virtuemart_categories` as C using (`virtuemart_category_id`)
				LEFT JOIN `#__virtuemart_category_categories` as CC on C.`virtuemart_category_id` = CC.`category_child_id`
				WHERE C.published = '1' ORDER BY category_parent_id, C.ordering";
		$db->setQuery($query);
		$all_cats = $db->loadObjectList();

		if(count($all_cats)) {
			foreach ($all_cats as $key => $value) {
				if(!empty( $value->category_parent_id ) && in_array($value->category_parent_id, $tmp)){
					$tmp[] = $value->category_id;
				}
			}
		}
		return $tmp;
	}
	/**
     *  check the folder is existed, if not make a directory and set permission is 755
     *
     * @param array $path
     * @access public,
     * @return boolean.
     */
     public static function renderThumb( $path, $width = 100, $height = 100, $title = '', $isThumb = true ){
      if( !preg_match("/.jpg|.png|.gif/",strtolower($path)) ) return '&nbsp;';
      if( $isThumb ){
		
        $path = str_replace( JURI::base(), '', $path );
        $imagSource = str_replace( '/', DS,  $path );
		
        if( file_exists($imagSource)  ) {
			
          $path =  $width."x".$height.'/'.$path;
          $thumbPath = JPATH_SITE.DS.'images'.DS.'mod_ice_vm_categories'.DS. str_replace( '/', DS,  $path );
          if( !file_exists($thumbPath) ) {
            $thumb = PhpThumbFactory::create( $imagSource  );  
            if( !self::makeDir( $path ) ) {
                return '';
            }   
            $thumb->adaptiveResize( $width, $height);
            
            $thumb->save( $thumbPath  ); 
          }
          $path = JURI::base().'images/mod_ice_vm_categories/'.$path;
        } 
      }
	  $path = str_replace('http:', '', $path); 
	  
	  self::addImage($thumbPath, $width, $height); 
	  
	  return '<span class="inline_image image_'.md5($thumbPath).'"></span>'; 
      return '<img src="'.$path.'" title="'.$title.'" alt="'.$title.'" width="'.$width.'px" height="'.$height. 'px" />';
    }
	
	public static function addImage($thumbPath, $width, $height)
	{
		if (!file_exists($thumbPath)) return;
		$cfd = JPATH_SITE.DS.'media'.DS.'ice_menu'; 
		$css = $cfd.DIRECTORY_SEPARATOR.'images.css'; 
		
		if (!file_exists($css))
		{
			$data = '/* dynamically created images */ 
			.lofmenu_virtuemart .lofmenu a span.inline_image {
				width: '.$width.'px; 
				height: '.$height.'px; 
				display: inline-block; 
				float: left; 
				margin: 2px 2px 2px 2px;
			}
			
			'."\n"; 
			file_put_contents($css, $data); 
		}
		
		$data = file_get_contents($thumbPath); 
		$image = '.image_'.md5($thumbPath).' { background: url(data:image/jpg;base64,'.base64_encode($data).') !important;}'."\n"; 
	    file_put_contents($css, $image, FILE_APPEND); 
		
		if (!defined('MOD_VMICE_ADDED'))
		{
		JHtml::stylesheet(Juri::base().'/media/ice_menu/images.css'); 
		define('MOD_VMICE_ADDED', 1); 
		}
	}
	
	static $images; 
	/**
     *  check the folder is existed, if not make a directory and set permission is 755
     *
     * @param array $path
     * @access public,
     * @return boolean.
     */
    static function makeDir( $path ){
      $folders = explode ( '/',  ( $path ) );
      $tmppath =  JPATH_SITE.DS.'images'.DS.'mod_ice_vm_categories'.DS;
      if( !file_exists($tmppath) ) {
        JFolder::create( $tmppath, 0755 );
      }; 
      for( $i = 0; $i < count ( $folders ) - 1; $i ++) {
        if (! file_exists ( $tmppath . $folders [$i] ) && ! JFolder::create( $tmppath . $folders [$i], 0755) ) {
          return false;
        } 
        $tmppath = $tmppath . $folders [$i] . DS;
      }   
      return true;
    }
}
