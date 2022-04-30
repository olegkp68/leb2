<?php


// Load the view framework
jimport('joomla.application.component.view');



require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'version.php'); 
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'ajaxhelper.php'); 
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'language.php'); 

if(!class_exists('VmView'))
{
if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'vmview.php'))
require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'vmview.php');
else
require(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'overrides'.DIRECTORY_SEPARATOR.'vmview.php');
}

	if (!class_exists( 'VmConfig' )) 
			{
				require(JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'config.php');
				VmConfig::loadConfig(); 
			}

			
			
class viewHelper extends VmView {
	public function __construct() {
		$template = JFactory::getApplication()->getTemplate();
		$this->_name = 'productdetails'; 
		$this->_basePath = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'; 
		$config = array(); 
		$config['name'] = $this->_name; 
		$config['base_path'] = $this->_basePath; 
		$config['template'] = JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'productdetails'; 
		$config['layout'] = 'default_stockavai'; 
		parent::__construct($config); 
		
	}
	public function display($tpl = null) {
		$template = JFactory::getApplication()->getTemplate();
		$tpath = JPATH_SITE.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'productdetails'; 
		if (method_exists($this, 'addTemplatePath')) {
				  $this->addTemplatePath($tpath);
				}
				else
				{
					if (method_exists($this, 'addIncludePath')) 
					{
					$this->addIncludePath( $tpath );
					}
				}
		
		$this->layoutName = 'default_stockavai'; 
		ob_start(); 
		parent::display($tpl); 
		$z = ob_get_clean(); 
		return $z; 
		
	}
	
	public function assignProduct($sku) {
			
			$db = JFactory::getDBO(); 
			$q = 'select `virtuemart_product_id` from `#__virtuemart_products` where `product_sku` = \''.$db->escape($sku).'\' limit 1'; 
			$db->setQuery($q); 
			$virtuemart_product_id = (int)$db->loadResult(); 
			if (empty($virtuemart_product_id)) return false; 

			$this->show_prices = (int)VmConfig::get('show_prices', 1);

			$document = JFactory::getDocument();

			$app = JFactory::getApplication();

			$menus	= $app->getMenu();
			$menu = $menus->getActive();

		

			$pathway = $app->getPathway();
			$task = 'display'; 

			if (!class_exists('VmImage'))
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'image.php');

			// Load the product
			//$product = $this->get('product');	//Why it is sensefull to use this construction? Imho it makes it just harder
			$product_model = VmModel::getModel('product');
			$this->assignRef('product_model', $product_model);

			

			

			$quantity = 1;
			
			$ratingModel = VmModel::getModel('ratings');
			$this->showRating = false;
			$product = $product_model->getProduct($virtuemart_product_id,TRUE,TRUE,TRUE,$quantity);
			
			if (empty($product->product_name)) return false; 
			

			if(!class_exists('shopFunctionsF'))require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
			

			$customfieldsModel = VmModel::getModel ('Customfields');

			if ($product->customfields){

				if (!class_exists ('vmCustomPlugin')) {
					require(VMPATH_PLUGINLIBS . DS . 'vmcustomplugin.php');
				}
				$customfieldsModel -> displayProductCustomfieldFE ($product, $product->customfields);
			}

			

			$isCustomVariant = false;
			if (!empty($product->customfields)) {
				foreach ($product->customfields as $k => $custom) {
					if($custom->field_type == 'C' and $custom->virtuemart_product_id != $virtuemart_product_id){
						$isCustomVariant = $custom;
					}
					if (!empty($custom->layout_pos)) {
						$product->customfieldsSorted[$custom->layout_pos][] = $custom;
					} else {
						$product->customfieldsSorted['normal'][] = $custom;
					}
					unset($product->customfields);
				}

			}



			$product_model->addImages($product);


			if (isset($product->min_order_level) && (int) $product->min_order_level > 0) {
				$this->min_order_level = $product->min_order_level;
			} else {
				$this->min_order_level = 1;
			}

			if (isset($product->step_order_level) && (int) $product->step_order_level > 0) {
				$this->step_order_level = $product->step_order_level;
			} else {
				$this->step_order_level = 1;
			}

			$currency = CurrencyDisplay::getInstance();
			$this->assignRef('currency', $currency);


			


			if (VmConfig::get('show_manufacturers', 1) && !empty($product->virtuemart_manufacturer_id)) {
				$manModel = VmModel::getModel('manufacturer');
				$mans = array();
				// Gebe die Hersteller aus
				foreach($product->virtuemart_manufacturer_id as $manufacturer_id) {
					$manufacturer = $manModel->getManufacturer( $manufacturer_id );
					$manModel->addImages($manufacturer, 1);
					$mans[]=$manufacturer;
				}
				$product->manufacturers = $mans;
			}
			// Load the category
			$category_model = VmModel::getModel('category');
			$seo_full = VmConfig::get('seo_full',true);
			

			
			
			



			

			$this->allowReview = false;
			$this->showReview = false;
			$this->rating_reviews='';
			$this->allowRating = false;

			

			// Load the user details
			$this->user = JFactory::getUser();

			
			$showBasePrice = (vmAccess::manager() or vmAccess::isSuperVendor());
			$this->assignRef('showBasePrice', $showBasePrice);

			$product->event = new stdClass();
			$product->event->afterDisplayTitle = '';
			$product->event->beforeDisplayContent = '';
			$product->event->afterDisplayContent = '';
			if (VmConfig::get('enable_content_plugin', 0)) {
				//shopFunctionsF::triggerContentPlugin($product, 'productdetails','product_desc');
			}

			
			
			vmLanguage::loadJLang('com_virtuemart');

			if ($this->show_prices) {
				if (!class_exists('calculationHelper'))
					require(VMPATH_ADMIN . DS . 'helpers' . DS . 'calculationh.php');
			}
			
		  $this->product = $product; 
			
		  return true; 
	}
	
	
}