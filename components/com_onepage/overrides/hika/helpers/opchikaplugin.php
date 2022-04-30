<?php
class OPCHikaPlugin {
	private static $detached; 
	public static function unregister($type) {
		if (empty(self::$detached[$type])) {
			self::$detached[$type] = array(); 
		}
		JPluginHelper::importPlugin($type);
		$dispatcher = JDispatcher::getInstance();
		$x = $dispatcher->get('_observers'); 
		foreach ($x as &$instance) {
			if (is_a($instance, $type.'Plugin')) {
			$dispatcher->detach($instance); 
			//$className = get_class($instance); 
			self::$detached[$type][] =& $instance;
			}
		}
		
		
		
	}
	
	public static function register($type) {
		JPluginHelper::importPlugin($type);
		$dispatcher = JDispatcher::getInstance();
		if ((!empty(self::$detached)) && (!empty(self::$detached[$type]))) {
			foreach (self::$detached[$type] as $key => &$instance) {
				$dispatcher->attach($instance); 
				unset(self::$detached[$type][$key]); 
				$className = get_class($instance); 
				//var_dump($className); 
			}
		}
	}
	
}