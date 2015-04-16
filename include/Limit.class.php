<?php

defined( '_VALID_INGWER' ) or die( 'Restricted access' );

class Limit {
	var $memoryLimit=true;
	var $memoryLimitServer=true;
	var $timeLimit=30;

	function Limit(){
		$this->setTimeLimit();
	    	$this->memoryLimitServer = trim(ini_get('memory_limit'));
		$last = strtolower($this->memoryLimitServer[strlen($this->memoryLimitServer)-1]);
		switch($last) {
			case 'g':
				$this->memoryLimitServer *= 1024;
			case 'm':
				$this->memoryLimitServer *= 1024;
			case 'k':
				$this->memoryLimitServer *= 1024;
		}
	    	$this->memoryLimitServer *= 0.9;
	}
	
	function checkMemoryLimit(){
		if (!$this->memoryLimit) return false;
		$this->memoryLimit= ((memory_get_usage(true)) < $this->memoryLimitServer);
		return $this->memoryLimit;
	}

	function setTimeLimit($seconds=30){
		$t=posix_times();
		if( ini_get('safe_mode') ){
			$this->timeLimit=25*90+$t['utime']; #Bei safe_mode nicht zu setzen
		}else {
			$this->timeLimit=$seconds*90+$t['utime'];
			set_time_limit($seconds);
		}
	}

	function checkTimeLimit(){
		$t=posix_times();
		return ($this->timeLimit > $t['utime']);
	}

	function checkAllLimit(){
		return ($this->checkTimeLimit() && $this->checkMemoryLimit());
	}
}

$globalLimit = new Limit();
