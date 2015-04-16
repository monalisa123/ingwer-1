<?php

defined( '_VALID_INGWER' ) or die( 'Restricted access' );

require_once('init.php');

class User {

	var $Data;

	function User ($id,$name=false){
		global $db, $tbUser;

		if (!$name) $this->Data=$db->selectOne($tbUser,'*',"id='%s'",$id);
		else $this->Data=$db->selectOne($tbUser,'*',"name='%s'",$name);
		if (!is_array($this->Data) || !array_key_exists('id',$this->Data)) {
			$this->Data = array('id'=>0);
			if ($name) $this->Data['name']=$name;
		}
	}
	
	function save(){
		global $db, $tbUser;
		$update = $this->Data;
		unset($update['id']);

		if ($this->Data['id'] == 0){
			$db->insert($tbUser,$update);
			$this->Data['id']=$db->lastId();
		}else
			$db->update($tbUser,$update,"id=%s",$this->Data['id']);
	}


	function del(){
		global $db, $tbUser;
		
		$db->delete($tbUser,"id=%s",$this->Data['id']);
	}

}




