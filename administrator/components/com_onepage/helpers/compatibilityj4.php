<?php
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.controller' );

define('OPC_JOOMLA_DIR', JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'joomla3'); 
define('OPC_JOOMLA_DIR4', JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_onepage'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'joomla4'); 

define('OPCJ3', true); 
define('OPCJ4', true); 



JLoader::register('OPCcontroller', OPC_JOOMLA_DIR.DIRECTORY_SEPARATOR.'opccontroller.php' );
JLoader::register('OPCModel', OPC_JOOMLA_DIR.DIRECTORY_SEPARATOR.'opcmodel.php' );
JLoader::register('OPCView', OPC_JOOMLA_DIR4.DIRECTORY_SEPARATOR.'opcview.php' );
JLoader::register('OPCPane', OPC_JOOMLA_DIR4.DIRECTORY_SEPARATOR.'opcpane.php' );
JLoader::register('OPCParameter', OPC_JOOMLA_DIR.DIRECTORY_SEPARATOR.'opcparameter.php' );
JLoader::register('OPCObj', OPC_JOOMLA_DIR.DIRECTORY_SEPARATOR.'opcobj.php' );
JLoader::register('OPCUtility', OPC_JOOMLA_DIR.DIRECTORY_SEPARATOR.'opcutility.php' );
JLoader::register('JHTMLOPC', OPC_JOOMLA_DIR.DIRECTORY_SEPARATOR.'jhtmlopc.php' );

if (!class_exists('JRequest')) {
 JLoader::register('JRequest', OPC_JOOMLA_DIR4.DIRECTORY_SEPARATOR.'jrequest.php' );
}

if (!defined('DS'))
define('DS', DIRECTORY_SEPARATOR); 