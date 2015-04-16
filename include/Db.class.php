<?php

defined( '_VALID_INGWER' ) or die( 'Restricted access' );

class db {
	var $fetch=False;
	var $connection;
	var $resultType;
	var $result=False;


	function db ($Server,$User,$Pass,$Db,$ResultType=MYSQL_ASSOC){
		$this->resultType=$ResultType;
		$this->connection = mysql_connect  ($Server  , $User, $Pass  , False);
		mysql_set_charset('utf8',$this->connection);
		mysql_select_db($Db);
		if (!$this->connection)throw new mysqlEx($this);
		

	}

	function datetime($timestamp){
		return date("Y-m-d h:i:s",$timestamp);
	}

	function numRows() {
		return mysql_num_rows($this->result); 
	}

	function lastId() {
		return mysql_insert_id($this->connection); 
	}

	function update($table,$values,$where=False,$escapeWhere=False){
		if (is_array($table)) $qtable = implode(",",$table);
		else $qtable = $table;

		foreach ($values as $k=>$v) {
			$values[$k] = "$k='" . $this->escape_string($v) . "'";
		}
		$qvalues = implode(",",$values);
		

		$q="UPDATE $qtable SET $qvalues";
		$q .= $this->_escapeWhere($where,$escapeWhere);
		if (!($this->result=mysql_query($q,$this->connection)))	new mysqlEx($q);
	}


	function delete($table,$where=False,$escapeWhere=False){
		if (is_array($table)) $qtable = implode(",",$table);
		else $qtable = $table;

		$q="DELETE FROM $qtable";
		$q .= $this->_escapeWhere($where,$escapeWhere);
		if (!($this->result=mysql_query($q,$this->connection)))	new mysqlEx($q);
	}

	function escape_string($str){
		if (1 == get_magic_quotes_gpc()) return mysql_real_escape_string(stripslashes($str),$this->connection);
		return mysql_real_escape_string($str,$this->connection);
	}

	function insert($table,$values){
		if (is_array($table)) $qtable = implode(",",$table);
		else $qtable = $table;

		foreach ($values as $k=>$v) {
			$values[$k] = "'" . $this->escape_string($v) . "'";
		}
		$qvalues = implode(",",$values);
		$qinsert = implode(",",array_keys($values));

		$q="INSERT INTO $qtable ($qinsert) VALUES ($qvalues)";
		
		if (!($this->result=mysql_query($q,$this->connection)))	new mysqlEx($q);
	}

	//$where="id=%s AND xx=%s"
	//$escapeWhere= array('1','sdfdsf')
	function _escapeWhere($where=False,$escapeWhere=False){
		$q="";
		if ($escapeWhere || $escapeWhere===0 || $escapeWhere==='0') {
			if (is_array($escapeWhere)){ 
				foreach ($escapeWhere as $k=>$v) $escapeWhere[$k] = $this->escape_string($v);
				if ($where) $q .= " where " . vsprintf($where,$escapeWhere);
			}
			elseif ($where) $q .= " where " . sprintf($where,$this->escape_string($escapeWhere));
		}
		elseif ($where) $q .= " where $where";
		return $q;
	}

	function select($table,$values="*",$where=False,$escapeWhere=False,$limit=False,$debug=False){
		if (is_array($table)) $qtable = implode(",",$table);
		else $qtable = $table;

		if (is_array($values)) $qvalues = implode(",",$values);
		else $qvalues = $values;

		$q="SELECT $qvalues FROM $qtable";
		$q .= $this->_escapeWhere($where,$escapeWhere);
		if ($limit) $q .= " $limit";
		if ($debug!==False) {
			print_r("<br/>\n$debug ########################################################<br/>\n");
			print_r($q);
			print_r("<br/>\n$debug EE########################################################<br/>\n");
		}
		if (!($this->result=mysql_query($q,$this->connection)))	new mysqlEx($q);
		return $this->result;
	}


	function query($q){
		if (!($this->result=mysql_query($q,$this->connection)))	new mysqlEx($q);
	}
	
	function selectOne($table,$values="*",$where=False,$escapeWhere=False){
		$this->select($table,$values,$where,$escapeWhere,"limit 0,1");
		return $this->nextQ();
	}

	function nextQ($field=False,&$rs=False){
		if ($rs!==False) $result = $rs;
		else $result=$this->result;
		if (!is_resource($result) || !$result) return False;

		$this->fetch=mysql_fetch_array($result,$this->resultType);


		if (!$field) return ($this->fetch);

		if (isset($this->fetch[$field])) return $this->fetch[$field];
		return False;
	}
}

class mysqlEx extends ErrorException {

	function mysqlEx($object=""){
		parent::__construct('MySQL Error: ' . mysql_error(), 0, $errno, $errfile, $errline);
		if (defined(DEBUG)) {
			print_r($object);
			print_r($this->getMessage());
			debug_print_backtrace();
		}
	}
}
?>
