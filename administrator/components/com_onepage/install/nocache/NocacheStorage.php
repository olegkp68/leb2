<?php

namespace Joomla\CMS\Cache\Storage;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Cache\CacheStorage;
use Joomla\CMS\Log\Log;


class NocacheStorage extends CacheStorage
{
	protected $_root;
	protected $_locked_files = array();
	
	public function __construct($options = array())
	{
		parent::__construct($options);
	}
	
	public function contains($id, $group)
	{
		return false;
	}

	
	public function get($id, $group, $checkTime = true)
	{
		return false; 
	}

	
	public function getAll()
	{
		return array(); 
		
	}

	
	public function store($id, $group, $data)
	{
		return true; 
	}

	
	public function remove($id, $group)
	{
		return true; 
	}

	
	public function clean($group, $mode = null)
	{
		return true; 
		
	}

	
	public function gc()
	{
		return true; 
	}

	
	public static function isSupported()
	{
		return true; 
	}

	
	public function lock($id, $group, $locktime)
	{
		$returning             = new \stdClass();
		$returning->locklooped = true;
		$returning->locked     = true;
		return $returning;

		
	}

	public function unlock($id, $group = null)
	{
		return true; 
	}
}
