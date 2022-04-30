<?php
/*
*
* @copyright Copyright (C) 2007 - 2012 RuposTel - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

	defined( '_JEXEC' ) or die( 'Restricted access' );
	jimport( 'joomla.filesystem.file' );
	
	
	class JModelHikaconfig extends OPCModel
	{	
		function __construct()
		{
			parent::__construct();
		
		}
		
		function getAcyFields() {
			require_once(JPATH_ROOT.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'userfields.php');  
			return OPCUserFields::getAcyFields(); 
			
		}
		
		
		
		function checkOtherPlugins()
		{
			
		}
		
		
		
		
		
		function storeRegistration()
		{
		   
		}
		
		function getCurrencies()
		{
		}
		function getDisabledOPC()
		{
		
		  	
			{
			if(version_compare(JVERSION,'1.7.0','ge') || version_compare(JVERSION,'1.6.0','ge') || version_compare(JVERSION,'2.5.0','ge')) 
	  {
	   $q = "select * from `#__extensions` where `element` = 'hikaopc' and `type` = 'plugin' and `folder` = 'system' limit 0,1 "; 
	  }
	  else
	  {
	    $q = "select * from #__plugins where element = 'hikaopc' and folder = 'system'  limit 1 "; 
	  }
		$db = JFactory::getDBO(); 
		$db->setQuery($q); 
		$r = $db->loadAssoc(); 
		if (!empty($r['enabled'])) return false; 
		if (!empty($r['published'])) return false; 
		
					
			}
			return true; 
			
		}
// admin
		function listExtsaAdmin(&$exts, &$langsr)
		{
		  jimport( 'joomla.filesystem.folder' );
		  jimport( 'joomla.filesystem.file' );
		  
		  $xts = array(); 
		  $langs = array(); 
		  
		  $files = JFolder::files(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'language', 'ini', 1, true, array('.svn', 'CVS')); 
		 
		  foreach ($files as $f)
		   {
		     $f = str_replace('/', DS, $f); 
			 $f = str_replace('\\', DS, $f); 
			 $adminpath = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR; 
			 $adminpath = str_replace('/', DS, $adminpath); 
			 $adminpath = str_replace('\\', DS, $adminpath); 

		     $f = str_replace($adminpath, '', $f); 
			 $fa = explode(DS, $f); 
			 
			  $lang = $fa[0];
			 
			 // some error: 
			if (strlen($lang)>7) continue; 
			
			$langs[$lang] = $lang; 
			
			$xt = $fa[1]; 
			 
			 $xt = str_replace($lang.'.', '', $xt); 
			 if (stripos($xt, 'bck')===false)
			 if ($xt !== 'ini')
			 {
			  if (!isset($xts[$xt]))
			   {
			     $xts[$xt] = array(); 
			     $xts[$xt]['name'] = $xt;  
				 $xts[$xt]['lang'] = array();  
				 $xts[$xt]['lang'][$lang] = $lang; 
			   }
			   else
			    $xts[$xt]['lang'][$lang] = $lang; 
			 }
			 
		   }
		   
		   $exts = $xts; 
		   $langsr = $langs;
		   
		  return true; 
		}

		// site
		function listExts(&$exts, &$langsr)
		{
		  jimport( 'joomla.filesystem.folder' );
		  jimport( 'joomla.filesystem.file' );
		  
		  $xts = array(); 
		  $langs = array(); 
		  
		  $files = JFolder::files(JPATH_SITE.DIRECTORY_SEPARATOR.'language', 'ini', 1, true, array('.svn', 'CVS')); 
		  foreach ($files as $f)
		   {
			 $jpath_site = JPATH_SITE.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR;
			 $jpath_site = str_replace('/', DS, $jpath_site); 
		     $f = str_replace('/', DS, $f); 
		     $f = str_replace($jpath_site, '', $f); 
			 $fa = explode(DS, $f); 
			 
			 if (count($fa) <= 1) continue; 
			 
			 $lang = $fa[0];
			
			$langs[$lang] = $lang; 
			
			$xt = $fa[1]; 
			 
			 $xt = str_replace($lang.'.', '', $xt); 
			 if (stripos($xt, 'bck')===false)
			 if ($xt !== 'ini')
			 {
			  if (!isset($xts[$xt]))
			   {
			     $xts[$xt] = array(); 
			     $xts[$xt]['name'] = $xt;  
				 $xts[$xt]['lang'] = array();  
				 $xts[$xt]['lang'][$lang] = $lang; 
			   }
			   else
			    $xts[$xt]['lang'][$lang] = $lang; 
			 }
			 
		   }
		   
		   $exts = $xts; 
		   $langsr = $langs;
		   
		  return true; 
		}
		
		
		function getExtLangVars()
		{
		   
   $jlang = JFactory::getLanguage(); 
   	 if(version_compare(JVERSION,'1.7.0','ge') || version_compare(JVERSION,'1.6.0','ge') || version_compare(JVERSION,'2.5.0','ge')) {

   $jlang->load('com_content', JPATH_ADMINISTRATOR, 'en-GB', true); 
   $jlang->load('com_content', JPATH_ADMINISTRATOR, $jlang->getDefault(), true); 
   $jlang->load('com_content', JPATH_ADMINISTRATOR, null, true); 
   
   
  
 }
		}
		
		function getArticleSelector($name, $value, $required=false)
		{
		
		$id = $name; 
	
		if (empty($value) || (!is_numeric($value))) $value = null; 
		
		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.opcmodal');

		// Build the script.
		$script = array();
		$html	= array();
		//if (stripos($id, '{')===false)
		{
		
		if(version_compare(JVERSION,'1.7.0','ge') || version_compare(JVERSION,'1.6.0','ge') || version_compare(JVERSION,'2.5.0','ge')) {
			
		
		$html[] = '<script type="text/javascript">'; 
		$html[] = '//<![CDATA['; 
		
		$html[] = '	function jSelectArticle_'.$id.'(id, title, catid, object) {';
		$html[] = '		document.id("'.$id.'_id").value = id;';
		$html[] = '		document.id("'.$id.'_name").value = title;';
		$html[] = '		SqueezeBox.close();';
		$html[] = '	}';
		$html[] = '//]]>'; 
		$html[] = '</script>'; 

		}
		else
		{
		$html[] = '<script type="text/javascript">'; 
		$html[] = '//<![CDATA['; 
		$html[] = "
		function jSelectArticle(id, title, object) {
			document.getElementById(object + '_id').value = id;
			document.getElementById(object + '_name').value = title;
			document.getElementById('sbox-window').close();
		}";
		$html[] = '//]]>'; 
		$html[] = '</script>'; 
		
		}
		// Add the script to the document head.
		//JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
		}

		// Setup variables for display.
		
		 if(version_compare(JVERSION,'1.7.0','ge') || version_compare(JVERSION,'1.6.0','ge') || version_compare(JVERSION,'2.5.0','ge')) {
		$link	= 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_'.$id;
		}
		else
		$link = 'index.php?option=com_content&amp;task=element&amp;tmpl=component&amp;object='.$id;
		$db	= JFactory::getDBO();
		$db->setQuery(
			'SELECT `title` ' .
			' FROM #__content' .
			' WHERE id = '.(int) $value
		);
		$title = $db->loadResult();

		
		
		if (empty($title)) {
		   if(version_compare(JVERSION,'1.7.0','ge') || version_compare(JVERSION,'1.6.0','ge') || version_compare(JVERSION,'2.5.0','ge')) 
			$title = JText::_('COM_CONTENT_SELECT_AN_ARTICLE');
			else
			$title = JText::_('Select an Article');
		}
		
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
		
		// The current user display field.
		$html[] = '<div class="fltlft">';
		$html[] = '  <input type="text" id="'.$id.'_name" value="'.$title.'" disabled="disabled" size="35" />';
		$html[] = '</div>';

		// The user select button.
		
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		$html2 = ''; 
		$html2 .= '	<a class="opcmodal" id="modal_link_'.$id.'" title="';
		if(version_compare(JVERSION,'1.7.0','ge') || version_compare(JVERSION,'1.6.0','ge') || version_compare(JVERSION,'2.5.0','ge')) 
		$html2 .= JText::_('COM_CONTENT_CHANGE_ARTICLE'); 
		else
		$html2 .= JText::_('Select an Article');
		$html2 .= '"  href="'.$link.'" rel="'.htmlentities('{handler: \'iframe\', size: {x: 800, y: 450}}').'">';
		if(version_compare(JVERSION,'1.7.0','ge') || version_compare(JVERSION,'1.6.0','ge') || version_compare(JVERSION,'2.5.0','ge')) 
		$html2 .= JText::_('COM_CONTENT_CHANGE_ARTICLE_BUTTON');
		else
		$html2 .= JText::_('Select');
		$html2 .= '</a>';
		$html[] = $html2; 
		$html[] = '  </div>';
		$html[] = '</div>';

		// The active article id field.
		if (0 == (int)$value) {
			$value = '';
		} else {
			$value = (int)$value;
		}

		// class='required' for client side validation
		$class = '';
		if ($required) {
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="'.$id.'_id"'.$class.' name="'.$name.'" value="'.$value.'" />';

		$zhtml = implode("\n", $html);; 

		return $zhtml; 
	
		}
		
		public static function isLess($x) {
	  if (!class_exists('VmVersion')) {
		  require(JPATH_VM_ADMINISTRATOR.DIRECTORY_SEPARATOR.'version.php'); 
		}	
	  
	  if (!isset(VmVersion::$REVISION)) return false; 
	  $rev = (int)VmVersion::$REVISION; 
	  
	  
	  // only VM3 is supported here: 
	  if ($rev < 8578) return false; 
	  //3.0.16....9204
	  //3.0.14....9194
	  //3.0.13.2..9162
	  //3.0.12....9058
	  //3.0.9.6...8956
	  //3.0.6.2...8771
	  //3.0.4.....8672
	  //3.0.2.....8615
	  //3.0.8.....8836
	  //3.0.0.....8578
	  $x = (int)$x; 
	  switch ($x) {
	    case 3016: 
		  if ($rev > 9204) return false; 
		case 3014: 
		  if ($rev <= 9203) return true; 
		case 3012:
		  if ($rev <= 9162) return true; 
		case 306: 
		  if ($rev <= 8771) return true; 
		case 304: 
		  if ($rev <= 8672) return true; 
		case 302: 
		  if ($rev <= 8615) return true; 
		case 308: 
		  if ($rev <= 8836) return true; 
		case 3090: 
		  if ($rev <= 8847) return true; 
		case 30910: 
		 if ($rev <= 8986) return true; 
		case 3098: 
		 if ($rev <= 8971) return true; 
		case 3096: 
		 if ($rev <= 8956) return true; 
		case 3094: 
		 if ($rev <= 8872) return true; 
		case 309:
		 if ($rev <= 8847) return true; 
		case 300: 
		  if ($rev <= 8578) return true; 
		
		
		
	  }
	  //custom build: 
		return false; 
	  
	}
	
		
		function getUserFieldsLists(&$corefields)
		{
			
		}
		private function fileContains($file, $string){
			
		}
		
		
		function checkLangFiles()
		{
			
		}
	
		function updateOPCShipping() {
			
		}
		function loadVmConfig()
		{
			
		}
		function listShopperGroups()
		{
		  return array(); 
		  
		  
		}
		

		
		function getVendorCurrency() {
			return;
		}
		
		
		function renameTheme()
		{
		
		   $from = JRequest::getVar('orig_selected_template');   
		   $to = JRequest::getVar('selected_template');   
		   
		   jimport( 'joomla.filesystem.file' );
		   jimport( 'joomla.filesystem.folder' );
		  

		   
		   $path = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR; 
		   if (!file_exists($path.$from)) 
		   {
		    JRequest::setVar('selected_template', JRequest::getVar('orig_selected_template'));   
		    return; 
		   }
		   $to = JFile::makeSafe($to); 
		   JRequest::setVar('selected_template', $to);   
		   
		   require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
		   
		   OPCconfig::copy('theme_config', $from, $to); 
		   
		   
		   
		   if (JFolder::copy($path.$from, $path.$to, '', true)===false)
		   return JText::sprintf('COM_ONEPAGE_CANNOT_CREATE_DIRECTORY', $path.$to); 
		 
		}
		function getFTypes() {
			
		 
		}
		
		function storeTracking($data) {
		
		
		}
		
		function storeTY($data)
		{
		 
		}
		
		private function storeVMuserfields($data) {
	
		  
		}
		
		function store($data = null)
		{
			$config_template = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.'hikaconfig.xml';
			$fields = simplexml_load_file($config_template); 
			$post = JRequest::get('post'); 
			/*
			foreach ($post as $key=>$val) {
				echo '<field name="'.$key.'" default="0" tranform="boolval" />'."\n"; 
			}
			*/
			
			foreach ($fields as $field) {
				
				$attributes = $field->attributes(); 
				$name = (string)$attributes->name; 
				$default = (string)$attributes->default; 
				if (isset($attributes->transform)) {
				 $transform = (string)$attributes->transform;
				}
				else {
					$transform = ''; 
				}
				
			    if (isset($post[$name])) {
					$value = $post[$name]; 
					$value = OPCHikaConfig::transform($name, $value, $transform);
				}
				else {
					$value = $default; 
					//checkbox handling: 
					if ($transform === 'boolval') {
						$value = false; 
					}
					$value = OPCHikaConfig::transform($name, $value, $transform);
				}
				
				OPCHikaConfig::set($name, $value); 
				echo $name.':'.var_export($value, true).':'.$transform.'<br />'; 
			
			}
			if (!empty($post['disable_op'])) {
				
				$this->setPluginEnabled('hikaopc', 'system', false, 'plugin'); 
			}
			else {
				$this->setPluginEnabled('hikaopc', 'system', true, 'plugin'); 
			}
			return true; 
			
			
			
		}
		
		
		
		public function getConfigModel() {
			  require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'config.php'); 
			  require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php'); 
		 
		   
			$configModel = new JModelConfig(); 
			return $configModel;
		}
		private function getMissingLangs() {
			 $configModel = $this->getConfigModel(); 
			 $lang_codes = $configModel->getJLanguages(); 
			 $missing = array(); 
			 foreach ($lang_codes as $c) {
				 $lang = $c['code']; 
				 if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.$lang.DIRECTORY_SEPARATOR.$lang.'.com_virtuemart.ini')) {
					 $missing[$lang] = $lang; 
				 }
			 }
			 return $missing; 
		}
		public function installCheck() {
			 $msg = ''; 
			 $configModel = $this->getConfigModel(); 
			 $msg .= $configModel->checkInstallOPCTable(); 
			 
			 //check VM language files: 
			 $missing = $this->getMissingLangs(); 
			 if (!empty($missing)) {
			 $msg .= '<div class="msgwrap">'.JText::_('COM_ONEPAGE_LANGUAGE_FILES_MISSING'); 
			 $btn = '<button type="button" class="btn btn-small btn-success" onclick="return installVMLangFiles(this);" />'.JText::_('COM_ONEPAGE_FIELD_MISCONFIGURATION_FIX').'</button>';
			 $msg .= $btn.'</div>'; 
			 //end check VM lang files
			 }
			 
			 
			 
			return $msg; 
		}
	
	
	
	public function installVmLanguageFiles() {
		$msg = ''; 
		$missing = $this->getMissingLangs(); 
		$configModel = $this->getConfigModel(); 
		if (empty($missing)) return; 
		foreach ($missing as $lang) {
	   require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'loader.php'); 
	   $data = OPCloader::fetchUrl('http://www.virtuemart.net/community/translations/virtuemart/download/'.$lang); 
	   if (!empty($data))
	    {
		   $zip = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$lang.'.com_virtuemart.zip'; 
		   $dest = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$lang.'.com_virtuemart'; 
		   jimport( 'joomla.filesystem.file' );
		   jimport( 'joomla.filesystem.folder' );
		   jimport('joomla.filesystem.archive');
		   jimport('joomla.archive.archive'); 
		   if (JFolder::create($dest) !== false)
		   if (JFile::write($zip, $data)!==false)
		   if (JArchive::extract($zip,$dest.DIRECTORY_SEPARATOR)!==false)		   
			{
			   $result = false; 
			   $configModel->installFromPath($dest, $result); 
			   if (empty($result)) {
				   $msg .= $lang.': '.JText::_('COM_ONEPAGE_LANGUAGE_FILES_FAILED')."<br />"; 
			   }
			}
			if (file_exists($dest)) {
			 JFolder::delete($dest); 
			}
			if (file_exists($zip)) {
			 JFile::delete($zip); 
			}
		   
		   
		}
		else {
			 $msg .= $lang.': '.JText::_('COM_ONEPAGE_LANGUAGE_FILES_FAILED')."<br />"; 
		}
		
		}
		if (empty($msg))
		$msg = JText::_('COM_ONEPAGE_OK'); 
		
		
        return $msg; 
		
		
		
	   
	
		}
		
		
	public function fixOrderReuse() {
	}
	public function getCalcs() {
	}
	
	private function alterCartsTable($disable=true)
	{
	}
	private function fixVmCache()
	{
		
	}
	   private function setPluginEnabled($element, $folder='system', $enabled=false, $type='plugin') 
	    {
			
		  	$configModel = $this->getConfigModel(); 
			
		  $db = JFactory::getDBO(); 
		  $q = "select * from `#__extensions` where `element` = '".$db->escape($element)."' and `type` = '".$db->escape($type)."' and `folder` = '".$db->escape($folder)."' limit 0,1"; 
		  $db->setQuery($q); 
		  $isInstalled = $db->loadAssoc(); 
		  if (empty($isInstalled) && (!$enabled)) return; 
		  
		 
		  
		  $element = JFile::makeSafe($element); 
		  $result = false; 
		  if (file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.$element)) {
			  if ($enabled) {
			   $configModel->installFromPath(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.$element, $result); 
			  // if ($result === false) return false; 
			  }
		  }
		  
		  
		  if (!empty($isInstalled))
		  {
		    if ($enabled)
			{
		      $q = " UPDATE `#__extensions` SET  `enabled` =  '1', `state` = 0 WHERE  `element` = '".$db->escape($element)."' and `folder` = '".$db->escape($folder)."' "; 
			  $db->setQuery($q); 
			  $db->execute(); 
			}
			else
			{
			  $q = " UPDATE `#__extensions` SET  `enabled` =  '0', `state` = 0 WHERE  `element` = '".$db->escape($element)."' and `folder` = '".$db->escape($folder)."' "; 
			  $db->setQuery($q); 
			  $db->execute(); 
			}
		  }
		  
		
		  
		}
		
		function copyPlugin($type, $plugin)
		{
			jimport('joomla.filesystem.folder');
		    jimport('joomla.filesystem.file');
		   
				$dst = JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$plugin; 
				if (!file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$plugin))
					  {
						  			JFolder::create(JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$plugin); 
					  }
				
				try {
				$z = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.$plugin; 

				$src = realpath(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.$plugin); 
				$dst = realpath($dst);

				if ($src != $dst)
				{
				  if (JFolder::copy(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.$plugin, $dst, '', true)===false) {
				    
					
					$msg .= 'Cannot copy '.$plugin.' plugin to '.$type.' directory. If the plugin is already at it\'s place, ignore this message<br />'; 
					JFactory::getApplication()->enqueueMessage($msg); 
					$msgs = true; 
				   
				   
				   }
				  
				}
				
				
				}
				catch (Exception $e)
				{
					
					$s = (string)$e; 
					JFactory::getApplication()->enqueueMessage('Cannot copy to: '.$dst.': '.$s); 
				}
		}
		
		function enableOpcRegistration($enabled=false)
		{
			
		}
	
		function updateVmConfig($arr)
		{
		}
		function getOPCExtensions(&$ename='')
		{
			$configModel = $this->getConfigModel(); 
			return $configModel->getOPCExtensions($ename); 
			
		 
		}
		
		
		function copylang()
		{
			
		}
		function getlangerr()
		{
		}
		function getShipmentMethods()
		{
		}

		function getPaymentMethods()
		{
		}
		function getSC()
		{
		}
		function getShippingCountries()
		{
		return $this->getSC();
		
		
		}

	function install_ps_checkout()
	{
  		return true;
	}

    
    

	function install_ps_order()
	{
      return true;
	}
	function install($firstRun = false)
	{

	   return true;
	  
	}
	function getShippingRates()
	{
	  return array(); 
	}
	
	function setTemplateSetting($k, $value)
	{ 
	
	if ($value === 'on') $value = '1';
	
		  $db = JFactory::getDBO();
		  
		  $a = explode('_',$k);
		  
		  if (count($a)==3)
		  {
		   $keyname = $a[0].'_'.$a[1];
		  
		   $tid = $a[2];
		   if (is_numeric($tid))
		   {
		   $keyname = $db->escape($keyname);
		   $q = 'select value from #__onepage_export_templates_settings where `keyname` = "'.$keyname.'" and `tid` = "'.$tid.'"';
		   $db->setQuery($q);
		   $res = $db->loadResult();
		   $value = $db->escape($value);
		   
		   if (!isset($res) || $res===false)
		   {
		    // ( `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY , `tid` INT NOT NULL DEFAULT '0', `keyname` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', `value` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', `original` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' )
		    $q = 'insert into #__onepage_export_templates_settings (`id`, `tid`, `keyname`, `value`, `original`) values (NULL, "'.$tid.'", "'.$keyname.'", "'.$value.'", ""); ';
		    
		   }
		   else
		   {
		    $q = 'update #__onepage_export_templates_settings set `value` = "'.$value.'" where `tid`="'.$tid.'" and `keyname`= "'.$keyname.'"';
		     //($res != $data[$k]))
		   }
		  
		   $db->setQuery($q);
		   $db->execute();
		   
		   }
		  }
	
	}
	
	function getDefaultC()
	{
		
	}
		function removeCache()
		{
		
		//stAn install debug: 

		
		   $dir = JPATH_SITE.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'com_onepage';
		   if (file_exists($dir))
		    {
			  $arr = @scandir($dir);
		 if (!empty($arr))
		 {
		  foreach ($arr as $file)
		  {
		   if (($file != 'overrides') && ($file != '.') && ($file != '..')) $ret[] = $file;
		  }
		 }
		 if (!empty($ret))
				foreach ($ret as $file)
				 {
				    JFile::delete($dir.DIRECTORY_SEPARATOR.$file); 
				 }
			}
		}
		
		function getTemplates()
		{
			
		 $configModel = $this->getConfigModel(); 
		 $templates = $configModel->getTemplates(true); 
		 return $templates;  
		
		}
		function getClassNames()
		{
		return array(); 
		
    	}
    
    /**
 * strposall
 *
 * Find all occurrences of a needle in a haystack
 *
 * @param string $haystack
 * @param string $needle
 * @return array or false
 */
function strposall($haystack,$needle){
   
    $s=0;
    $i=0;
   
    while (is_integer($i)){
       
        $i = strpos($haystack,$needle,$s);
       
        if (is_integer($i)) {
            $aStrPos[] = $i;
            $s = $i+strlen($needle);
        }
    }
    if (isset($aStrPos)) {
        return $aStrPos;
    }
    else {
        return false;
    }
}

function retCss()
	{
		return ""; 	
	}

function retPhp()
	{
		return array(); 
	}

function tableExists($table)
{

 $dbj = JFactory::getDBO();
 $prefix = $dbj->getPrefix();
 $table = str_replace('#__', '', $table); 
 $table = str_replace($prefix, '', $table); 
 
  $q = "SHOW TABLES LIKE '".$dbj->getPrefix().$table."'";
	   $dbj->setQuery($q);
	   $r = $dbj->loadResult();
	   if (!empty($r)) return true;
 return false;

 $db = JFactory::getDBO();
 $q = "SHOW TABLES LIKE '".$db->getPrefix().$db->escape($table)."'";
 $db->setQuery($q);
 $r = $db->loadResult();
 if (!empty($r))
 return true;
 return false;
}
function createTempOrderTables()
{
 $db = JFactory::getDBO();
 if (!$this->tableExists('vm_orders_opctemp'))
 {
   $q = 'CREATE TABLE '.$db->getPrefix().'vm_orders_opctemp LIKE '.$db->getPrefix().'vm_orders';
   $db->setQuery($q);
   $db->execute();
   $q = '';  
 }
 
}

// gets list of order statuses 
function getOrderStatuses()
{
  $db = JFactory::getDBO();
  $q = 'select * from #__virtuemart_orderstates where 1 limit 999';
  $db->setQuery($q);
  $res = $db->loadAssocList();
  if (empty($res)) return array();
  return $res; 
}

function getPhpTrackingThemes()
{

  // stAn install debug: 

  $path = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'; 

  		  if(!class_exists('JFile')) require(JPATH_LIBRARIES.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'file.php');
		  if(!class_exists('JFolder')) require(JPATH_LIBRARIES.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'filesystem'.DIRECTORY_SEPARATOR.'folder.php');

  
  $files = JFolder::files($path, $filter = '.php', false, true);
  $arr = array(); 
  
 
  
  foreach ($files as $f)
  {
    $pi = pathinfo($f); 
	$file = $pi['filename']; 
	$jf = JFile::makesafe($file);
    // security here: 	
	if ($jf != $file) continue; 
	$path = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'trackers'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$file.'.xml'; 
	if (!file_exists($path)) continue; 
	$arr[] = $file; 
	
    
  }
  return $arr; 
  
}
/**
* Compiles a list of installed languages
*/
function getLanguages()
{
	

	// Initialize some variables
	$db		= JFactory::getDBO();
	 
	$path = JPATH_SITE.DIRECTORY_SEPARATOR.'language'; 
	$dirs = JFolder::folders( $path );

   $rows = array(); 
    $rowid = 0; 
	foreach ($dirs as $dir)
	{
		$files = JFolder::files( $path.DIRECTORY_SEPARATOR.$dir, '^([-_A-Za-z]*)\.xml$' );
		foreach ($files as $file)
		{
		    $file = str_replace('/', DS, $file); 
			//$data = JApplicationHelper::parseXMLLangMetaFile($path.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.$file);

			$row 			= new StdClass();
			$row->id 		= $rowid;
			$row->language 	= substr($file,0,-4);

			/*
			foreach($data as $key => $value) {
				$row->$key = $value;
			}
			*/

			// if current than set published
			$params = JComponentHelper::getParams('com_languages');
			if ( $params->get('site', 'en-GB') == $row->language) {
				$row->published	= 1;
			} else {
				$row->published = 0;
			}

			$row->checked_out = 0;
			
			
			
            $row->short = $row->language;
			$rows[] = $row;
			$rowid++;
		}
	}
	return $rows; 
}

function getErrMsgs()
{
 $msg = ''; 
   $conf = JPATH_SITE.DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR."com_onepage".DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."onepage.cfg.php";
   if ((file_exists($conf) && (!is_writable($conf))))
   $msg = 'File is not writable: '.$conf."<br />";
   
   $db = JFactory::getDBO(); 
   
   // check if there is ANY vendor within the shop
   $q = 'select * from #__virtuemart_vmusers where user_is_vendor = 1 and virtuemart_vendor_id <> 0'; 
   $db->setQuery($q); 
   $r = $db->loadAssocList(); 
   $novendor = false; 
   if (empty($r))
    {
	  $msg .= JText::_('COM_ONEPAGER_VENDOR_ERROR').' <br />'; 
	  $novendor = true; 
	}
	
	// more users marked as vendors are sharing the same vendor ID
	$arr = array(); 
	if (count($r)>1)
	{
	
	    foreach ($r as $vendor)
		 {
		   if (empty($arr[$vendor['virtuemart_vendor_id']])) $arr[$vendor['virtuemart_vendor_id']] = array(); 
		   $arr[$vendor['virtuemart_vendor_id']][] = $vendor['virtuemart_user_id']; 
		 }
	
		 foreach ($arr as $v_id => $users)
		  {
		     $count = count($users); 
			 $names = array(); 
		     if ($count > 1)
			   {
			      $msg .= 'PROBLEM: More than one user shares the same Vendor ID ('.$v_id.') which will lead to various problems<br />'; 
				  foreach ($users as $user_id)
				  {
				   
				    $q = 'select * from #__users where id = '.(int)$user_id.' limit 0,1'; 
				    $db->setQuery($q); 
				    $res = $db->loadAssoc(); 
					
				    if (empty($res))
					  {
					     $msg .= 'FIXED: User ID ('.$user_id.') in #__virtuemart_vmusers does not exists in #__users! OPC deactivates this vendor to fix further problems.<br />'; 
						 $q = 'update `#__virtuemart_vmusers` set `user_is_vendor` = "0", `virtuemart_vendor_id` = "0" where `virtuemart_user_id` = "'.(int)$user_id.'" and `user_is_vendor` = "1" and `virtuemart_vendor_id` = "'.$v_id.'" limit 1'; 
						 $db->setQuery($q); 
						 $db->execute(); 
						 
						 $count--; 
					  }
					  else
					  $names[] = $res['username']; 
				  }
			   }
			  if ($count === 0)
			   {
			      $msg .= 'None of the vendors had a record in #__users and thus they all were deactivated. Deactivated users in #__virtuemart_users are: '.implode(', ', $users).'<br />'; 
			   }
			  if ($count > 1)
			   {
			      $msg .= 'MANUAL ACTION REQUIRED: There are still two vendors sharing the same virtuemart_vendor_id, please make sure that only one has virtuemart_vendor_id = 1 and user_is_vendor = 1 in your #__virtuemart_vmusers. List of original user_id\'s: '.implode(', ', $users).' with usernames ('.implode(', ', $names).') Having two or more vendors sharing the same Vendor ID will lead to unpredicted email or other issues. This also may be fixed by removing one of the users with Virtuemart user management.<br />'; 
			   }
			   
		  }
		 
	}
	
	
	$arr = array('airedirectwww', 'cache'); 
	foreach ($arr as $k=>$v)
	{
	  $q = "select * from `#__extensions` where `element`='".$db->escape($v)."' and `type`='plugin' and `enabled`=1 and `folder`='system'"; 
	  $db->setQuery($q); 
	  $res = $db->loadAssoc(); 
	  
	  if (!empty($res))
	  {
		  $msg .= 'Incompatible 3rd party plugin detected. This is only a notice and the underlying issue might have already been fixed by the developer. If you are having problems with your checkout, you may want to try to disable or reconfigure this plugin: '.JText::_($res['name']).' ('.$v.')<br />'; 
	  }
	}
	
	// note - user is marked as vendor, but has zero vendor id
	// this can lead either to make him a real vendor
	// OR to unmark him as a vendor
	$q = 'select * from #__virtuemart_vmusers where user_is_vendor = 1 and virtuemart_vendor_id = 0'; 
	$db->setQuery($q); 
	$res = $db->loadAssoc(); 
	if (!empty($res))
	 {
	   $q = 'select * from #__users where id = '.(int)$res['virtuemart_user_id'].' limit 0,1'; 
	   $db->setQuery($q); 
	   $juser = $db->loadAssoc(); 
	   
	    if (($novendor === false) || (empty($juser)))
		{
	     $q = 'update `#__virtuemart_vmusers` set `user_is_vendor` = "0", `virtuemart_vendor_id` = "0" where `virtuemart_user_id` = "'.(int)$res['virtuemart_user_id'].'" and `user_is_vendor` = "1" and virtuemart_vendor_id = 0'; 
		 $msg .= 'FIXED: A user ('.$res['virtuemart_user_id'].') was marked as a vendor, but had no Vendor ID associated. He was unmarked as vendor by OPC.'; 
		}
		else
		if ($novendor)
		{
		
		$q = 'update `#__virtuemart_vmusers` set `user_is_vendor` = "1", `virtuemart_vendor_id` = "1" where `virtuemart_user_id` = "'.(int)$res['virtuemart_user_id'].'" and `user_is_vendor` = "1" and virtuemart_vendor_id = 0'; 
		$msg .= 'FIXED: A user ('.$res['virtuemart_user_id'].') was marked as a vendor, but had no Vendor ID associated. Because OPC detected you had no valid vendors in your shop, this users was marked as your vendor. Please check your Virtuemart vendor settings closely.'; 
		}
		$db->setQuery($q); 
		$db->execute(); 
		
		
	 }
	 // note: user is marked as vendor, but has no record in #__users - joomla
	 $q = 'select * from #__virtuemart_vmusers where user_is_vendor = "1"'; 
	 $db->setQuery($q); 
	 $res = $db->loadAssocList(); 
	 if (!empty($res))
	 foreach ($res as $user)
	  {
	     $q = 'select * from #__users where id = '.(int)$user['virtuemart_user_id'].' limit 0,1'; 
		 $db->setQuery($q); 
		 $juser = $db->loadAssoc(); 
		 if (empty($juser))
		  {
		     $msg .= 'Problem: A user ID ('.$user['virtuemart_user_id'].') in your #__virtuemart_vmusers is marked as vendor, but does not exists in #__users <br />'; 
			 $q = 'update `#__virtuemart_vmusers` set `user_is_vendor` = "0" where `virtuemart_user_id` = "'.(int)$user['virtuemart_user_id'].'" and `user_is_vendor` = "1" '; 
			 $db->setQuery($q); 
			 $db->execute(); 
			 
			 $msg .= 'FIXED: A user ID ('.$user['virtuemart_user_id'].') in your #__virtuemart_vmusers was unmarked as vendor because he is not registered in #__users<br />'; 
		  }
		 
	  }
	 
	 
	
	/*
   $db = JFactory::getDBO(); 
   $q = 'select * from #__virtuemart_vmusers where user_is_vendor = 0 and virtuemart_vendor_id <> 0'; 
   $db->setQuery($q); 
   $r = $db->loadAssocList(); 
    */   
	$session = JFactory::getSession(); 
		$msgs = $session->get('onepage_err', ''); 
			if (!empty($msg)){
				  $session->set('onepage_err', $msg.$msgs); 
			}

}

/* this function is from Virtuemart SVN for editing language files
*/

function getDecodeFunc($langCharset) {
	$func = 'strval';
	// get global charset setting
	$iso = explode( '=', @constant('_ISO') );
	// If $iso[1] is NOT empty, it is Mambo or Joomla! 1.0.x - otherwise Joomla! >= 1.5
	$charset = !empty( $iso[1] ) ? $iso[1] : 'utf-8';
	// Prepare the convert function if necessary
	if( strtolower($charset)=='utf-8' && stristr($langCharset, 'iso-8859-1' ) ) {
		$func = 'utf8_decode';
	} elseif( stristr($charset, 'iso-8859-1') && strtolower($langCharset)=='utf-8' ) {
		$func = 'utf8_encode';
	}
	if( !function_exists( $func )) {
		$func = 'strval';
	}
	return $func;
}







function check_syntax($file)
{
// load file
$code = file_get_contents($file);

$bom = pack("CCC", 0xef, 0xbb, 0xbf);
				if (0 == strncmp($code, $bom, 3)) {
					//echo "BOM detected - file is UTF-8\n";
					$code = substr($code, 3);
				}

// remove non php blocks
$x = 0; 
ob_start(); 
$f = @eval('$x = 1;'."?>$code"); 
$y = ob_get_clean(); 
return $x; 

}


function getLangVars()
{


  return array(); 
}
	
	function removepatchusps()
	{
		$msg = ''; 
	$file = JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'vmshipment'.DIRECTORY_SEPARATOR.'alatak_usps'.DIRECTORY_SEPARATOR.'alatak_usps.php'; 

		$file2 = str_replace('.php', '_opc_backup.php', $file); 			
if (JFile::copy($file2, $file)===false)
{
$msg = 'Could not copy '.$file2.' to '.$file.'<br />';
	
}
else 
{
	
if (JFile::delete($file2)===false)
{
$msg = 'Could not remove backup file'.$file2.'<br />';	
}

return $msg.'Patch was removed'; 
}
if (JFile::delete($file)===false)
{
$msg = 'Could not remove '.$file.'<br />';	
}
if (JFile::move($file2, $file)===false)
{
$msg = 'Could not move '.$file2.' to '.$file.'<br />';
	
}
else return 'Patch was removed'; 

if (!empty($msg))
{
  $msg .= 'Please restore the original file '.$file.' from '.$file2;	
  
}
return $msg; 



	}		
	
	function installShopfunctions(&$msg) {
	if(version_compare(JVERSION,'3.5.1','ge')) {
	   if (file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'shopfunctionsf'.DIRECTORY_SEPARATOR.'shopfunctionsf.php')) 
	   {
		 JFile::copy(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'shopfunctionsf.php', JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'shopfunctionsf.bck.'.time().'.php'); 
	     $this->installExt(-1, 'shopfunctionsf.php', $msg); 
	   }
		}
	}
	
	function patchusps()
	{
		  jimport( 'joomla.filesystem.folder' );
		  jimport( 'joomla.filesystem.file' );
		
		$file = JPATH_SITE.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'vmshipment'.DIRECTORY_SEPARATOR.'alatak_usps'.DIRECTORY_SEPARATOR.'alatak_usps.php'; 
		if (file_exists($file))
		{
$file2 = str_replace('.php', '_opc_backup.php', $file); 			
if (JFile::copy($file, $file2)===false)
{
$msg = 'Could not copy '.$file.' to '.$file2. ' patch wasn\'t applied';
return $msg; 	
}
		  $data = file_get_contents($file); 	
		  $data = str_replace("\r\r\n", "\r\n", $data); 
		  $data = str_replace('function _sendRequest', "\r\n\tstatic ".'$uspsCache;'." \r\n\tfunction _sendRequest", $data); 
		  $x1 = strpos($data, 'function _sendRequest'); 
		  $x2 = strpos($data, '{', $x1); 
		  $x3 = strpos($data, 'return true;', $x2); 
		  $data2 = substr($data, 0, $x2+1)."\r\n".'
	if (!empty(plgVmShipmentAlatak_USPS::$uspsCache))
		if (isset(plgVmShipmentAlatak_USPS::$uspsCache[$xmlPost]))
		{
			if (isset(plgVmShipmentAlatak_USPS::$uspsCache[$xmlPost][\'method\']))
			if (plgVmShipmentAlatak_USPS::$uspsCache[$xmlPost][\'method\'] == $method)
			{
				$xmlResult = plgVmShipmentAlatak_USPS::$uspsCache[$xmlPost][\'result\']; 
				return  true; 
			}
		}			  
		'.substr($data, $x2+1, $x3-($x2+1))."\r\n".'
		if (empty(plgVmShipmentAlatak_USPS::$uspsCache)) plgVmShipmentAlatak_USPS::$uspsCache = array(); 
		if (empty(plgVmShipmentAlatak_USPS::$uspsCache[$xmlPost])) plgVmShipmentAlatak_USPS::$uspsCache[$xmlPost] = array(); 
		plgVmShipmentAlatak_USPS::$uspsCache[$xmlPost][\'method\'] = $method;
		plgVmShipmentAlatak_USPS::$uspsCache[$xmlPost][\'result\'] = $xmlResult; 		
		
		
		'.substr($data, $x3); 
		  
		if (JFile::write($file, $data2)===false)
		{
			$msg = 'Could not write to '.$file;
			return $msg; 
		}
		else
		{
			$msg = 'Patch applied in '.$file;
			return $msg; 
			
		}
		}
		
	}
	
	/**
	 * Joomla modified function from installer.php file of /libraries/joomla/installer.php
	 *
	 * Method to extract the name of a discreet installation sql file from the installation manifest file.
	 *
	 * @access	public
	 * @param	string  $file 	 The SQL file
	 * @param	string	$version	The database connector to use
	 * @return	mixed	Number of queries processed or False on error
	 * @since	1.5
	 */
	function parseSQLFile($file)
	{
		// Initialize variables
		$queries = array();
		$db =  JFactory::getDBO();
		$dbDriver = strtolower($db->get('name'));
		if ($dbDriver == 'mysqli') {
			$dbDriver = 'mysql';
		}
		$dbCharset = ($db->hasUTF()) ? 'utf8' : '';

		if (!file_exists($file)) return 0;

		// Get the array of file nodes to process

		// Get the name of the sql file to process
		$sqlfile = '';
			// we will set a default charset of file to utf8 and mysql driver
			$fCharset = 'utf8'; //(strtolower($file->attributes('charset')) == 'utf8') ? 'utf8' : '';
			$fDriver  = 'mysql'; // strtolower($file->attributes('driver'));

			if( $fCharset == $dbCharset && $fDriver == $dbDriver) {
				$sqlfile = $file;
				// Check that sql files exists before reading. Otherwise raise error for rollback

				$buffer = file_get_contents($file);

				// Graceful exit and rollback if read not successful
				if ( $buffer === false ) {
					return false;
				}

				// Create an array of queries from the sql file
				jimport('joomla.installer.helper');
				$queries = JInstallerHelper::splitSql($buffer);

				if (count($queries) == 0) {
					// No queries to process
					return 0;
				}

				// Process each query in the $queries array (split out of sql file).
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query{0} != '#') {
						$db->setQuery($query);
						if (!$db->execute()) {
							JError::raiseWarning(1, 'JInstaller::install: '.JText::_('SQL Error')." ".$db->stderr(true));
							return false;
						}
					}
				}
			}
		

		return (int) count($queries);
	}


		
	}

