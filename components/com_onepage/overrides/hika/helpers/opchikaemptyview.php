<?php

class OPCHikaEmptyView {
	 var $config; 
	 public function __construct() {
		 $ref = OPCHikaRef::getInstance(); 
		 $this->config = $ref->config; 
	 }
	 
	 public function  __call($name, $arguments) {
		 
	 }
	 
	 public static function __callStatic($name, $arguments) {
		 
	 }
	 
	 public function  __get($name) {
		 
	 }
}