<?php

defined( '_VALID_INGWER' ) or die( 'Restricted access' );

require_once('initDb.php');

class Session {

	var $Data;
	var $Type;
	var $Id;
	var $New=false;

	function Session ($type,$id=false){
		global $db, $tbSession;

		if (!$id) $this->Id=session_id();
		else $this->Id=$id;

		$this->Type=$type;
		$data=$db->selectOne($tbSession,'data',"type='%s' AND session_id='%s'",array($type,$this->Id));
		$this->Data=json_decode($data['data'],true);
		$this->Tmp=$data['data'];
		if (!is_array($this->Data)){
			$this->Data=array();
			$this->New = true;
		}
	}

	function _replace($in){
		$ret=array();
		foreach($in as $k=>$v){
			if (is_array($v)) $ret[$k] = $this->_replace($v);
			else $ret[$k] = preg_replace('/[\n]/','\n',$v);
		}
	}

	function save(){
		global $db, $tbSession;


		$data = $this->Data;
		$update = array('data' => json_encode($data) );
		if (!$this->New) $db->update($tbSession,$update,"type='%s' AND session_id='%s'",array($this->Type,$this->Id));
		else {
			$update['session_id'] = $this->Id;
			$update['type'] = $this->Type;
			$db->insert($tbSession,$update);
		}
		$this->New = false;
		
	}


	function del(){
		global $db, $tbSession;
		
		$db->delete($tbSession,"type='%s' AND session_id='%s'",array($this->Type,$this->Id));
		$this->New = true;
	}
	
	function delOld(){
		global $db, $Session;
		
		$db->delete($tbSession,"last < SUBDATE(NOW(), 10)",$this->Type,$this->Id);
	}

}



