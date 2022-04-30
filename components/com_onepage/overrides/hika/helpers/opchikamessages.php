<?php

class OPCHikaMessages {
	public static function implodeMsgs($msgs) {
		$msA = array(); 
		foreach ($msgs as $line ) {
			if (is_array($line)) {
				foreach($lines as $msg) {
					if ((is_string($msg)) && (!empty($msg))) {
						$msA[$msg] = $msg; 
					}
				}
			}
			else {
				if ((!empty($line)) && (is_string($line))) {
					$msA[$line] = $line; 
				}
				
			}
		}
		$msgs = implode('<br />', $msA); 
		return $msgs;
	}
}