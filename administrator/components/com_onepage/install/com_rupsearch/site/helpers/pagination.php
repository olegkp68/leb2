<?php
defined('_JEXEC') or die;


require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_rupsearch'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php'); 
RupHelper::getIncludes(); 
/*
if (!class_exists('VmPagination'))
{
	
	require_once(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'vmpagination.php'); 
}
*/


class rupPagination {

	private $_perRow = 5;
	public $limitstart = null;
	public $limit = null;
	public $total = null;
	public $prefix = null;
	protected $_viewall = false;
	protected $_additionalUrlParams = array();
	function __construct($total=999, $limitstart=0, $limit=50, $perRow=5){
				  
		//$this->total = $total; 
		$this->total = 999; 
													 
	}

	

	function setSequence($sequence){
		$this->_sequence = $sequence;
	}

	function getLimitBox($sequence=0)
	{
		return ''; 
	}
	
	public function vmOrderUpIcon ($i, $ordering = true, $task = 'orderup', $alt = 'JLIB_HTML_MOVE_UP', $enabled = true, $checkbox = 'cb') {
		return '';
		
	}
	public function vmOrderDownIcon ($i, $ordering, $n, $condition = true, $task = 'orderdown', $alt = 'JLIB_HTML_MOVE_DOWN', $enabled = true, $checkbox = 'cb') {
		return '';
	}
	public function getData()
	{
		return array();
	}
	function getPagesLinks()
	{
		
		return self::getFakePagination($this->total); 
		//return ''; 
	}
	public static function getFakePagination($currentCount=999) {
		$app = JFactory::getApplication(); 
		$limitstart = JRequest::getInt('limitstart', JRequest::getInt('start', 0)); 
		$limit = JRequest::getInt('limit', $app->getUserStateFromRequest('com_virtuemart.category.limit', 'limit', VmConfig::get('llimit_init_FE', 48), 'int')); 
		
		//if ($limit > 48) $limit = 48; 
		$total = 9999; 
		if ($currentCount < $limit) {
			$total = $limitstart+$currentCount;
			
		}
		
		if (!empty(RupHelper::$total_count)) {
			$total = RupHelper::$total_count;
		}
		
		JRequest::setVar('tmpl', null); 
		unset($_REQUEST['tmpl']); 
		unset($_POST['tmpl']); 
		unset($_GET['tmpl']);
		
		unset($_REQUEST['format']); 
		unset($_POST['format']); 
		unset($_GET['format']);
		
		$app = JFactory::getApplication();
		$router = $app->getRouter();
		$mode = $router->getMode(); 
		$router->setMode(0);
		
		
		$vmpagination = new VmPagination($total, $limitstart, $limit , 4 );
		$ret = $vmpagination->getPagesLinks();
		$ret = str_replace(array('&tmpl=component', '&amp;tmpl=component', '&format=opchtml', '&amp;format=opchtml'), array('', '', '', ''), $ret); 
		
		$router->setMode($mode);
		return $ret; 
		
	}
														  
	function getPagesCounter()
	{
		return ''; 
	}
	public function getListFooter()
	{
		return ''; 
	}
	function getResultsCounter()
	{
		return ''; 
	}
	public function getRowOffset($index)
	{
		return $index + 1 + $this->limitstart;
	}


}
