<?php
class OPCNullSession implements SessionHandlerInterface , SessionIdInterface {
	function open($savePath,$sessionName) {
		$db = JFactory::getDBO(); 
		$q = 'START TRANSACTION'; 
		$db->setQuery($q); 
		$db->query();
		
		return true;
	}
	function close()
	{
		return true;
	}
	function read($sessionId) {
		
	}
	
	function write($sessionId, $data) {
		return true;
	}
	function destroy($sessionId) {
		return true;
	}
	
	function gc($lifetime) {
		return true;
	}
	function create_sid() {
		return null;
	}
	
	function shutDown() {
		
		try {
		 $db = JFactory::getDBO(); 
		 $q = 'ROLLBACK'; 
		 $db->setQuery($q); 
		 $db->query(); 
		}
		catch (Exception $e) {
			
		}
	}
}