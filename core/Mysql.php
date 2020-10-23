<?php

/**
 * Mysql数据库类
 PHP>=5.0,Mysql>=5.0
 使用 mysqli 替换 pconnect
*/


class ATU_Mysql
{

    var $link_id    = NULL;
    var $settings   = array();
    var $error_message  = array();
    var $version        = '';
    var $starttime      = 0;
	var $mysqli   =0;
	var $querys    =NULL; //最后一次查询

 
    function __construct($db=NULL)
    {
		$config=& get_config();
		if($db){$config['db_name']=$db;}
		
        $this->cls_mysql($config['db_host'],$config['db_user'],$config['db_pass'], $config['db_name'], $config['db_charset'],$config['db_mysqli'],$config['db_quiet']);
    }

    function cls_mysql($dbhost, $dbuser, $dbpw, $dbname = '', $charset = 'utf8', $mysqli = 1, $quiet = 0)
    {
		$this->mysqli=$mysqli;
       
        if ($quiet)
        {
            $this->connect($dbhost, $dbuser, $dbpw, $dbname, $charset, $mysqli, $quiet);
        }
        else
        {
            $this->settings = array(
                                    'dbhost'   => $dbhost,
                                    'dbuser'   => $dbuser,
                                    'dbpw'     => $dbpw,
                                    'dbname'   => $dbname,
                                    'charset'  => $charset,
                                    'mysqli' => $mysqli
                                    );
        }
    }

    function connect($dbhost, $dbuser, $dbpw, $dbname = '', $charset = 'utf8', $mysqli = 0, $quiet = 0)
    {
        if ($mysqli)
        {
            if (!($this->link_id = @mysqli_connect($dbhost, $dbuser, $dbpw,$dbname)))
            {
                if (!$quiet)
                {
                    $this->ErrorMsg("Can't mysqli MySQL Server($dbhost)!");
                }

                return false;
            }
        }else{
            
           $this->link_id = @mysql_connect($dbhost, $dbuser, $dbpw, true);
            
            if (!$this->link_id)
            {
                if (!$quiet)
                {
                    $this->ErrorMsg("Can't Connect MySQL Server($dbhost)!");
					
                }

                return false;
            }
        }
		$this->starttime = time();
        $this->version = $this->mysqlFun("get_server_info",$this->link_id);
		$this->set_mysql_charset($charset);
        
            
       // mysql_query("SET sql_mode=''", $this->link_id);？
            
         /* 选择数据库 */
		if (!$mysqli && $dbname)
		{
			if (mysql_select_db($dbname, $this->link_id) === false )
			{
				if (!$quiet)
				{
					$this->ErrorMsg("Can't select MySQL database($dbname)!");
				}

				return false;
			}
			
		}
		return true;
    }

    function select_database($dbname)
    {
        return $this->mysqlFun("select_db",$dbname, $this->link_id);
    }

    function set_mysql_charset($charset)
    {
           if (in_array(strtolower($charset), array('gbk', 'big5', 'utf-8', 'utf8')))
            {
                $charset = str_replace('-', '', $charset);
            }
            if ($charset != 'latin1')
            {
                $this->mysqlFun("query","SET character_set_connection=$charset, character_set_results=$charset, character_set_client=binary", $this->link_id);
            }
        
    }

    function fetch_array($query, $result_type = MYSQL_ASSOC)
    {
        return $this->mysqlFun("fetch_array",$query, $result_type);
    }

    function query($sql, $type = '')
    {
        if ($this->link_id === NULL)
        {
            $this->connect($this->settings['dbhost'], $this->settings['dbuser'], $this->settings['dbpw'], $this->settings['dbname'], $this->settings['charset'], $this->settings['mysqli']);
            $this->settings = array();
        }
        /* 当当前的时间大于类初始化时间的时候，自动执行 ping 这个自动重新连接操作 */
        if (!$this->mysqli && time() > $this->starttime + 1)
        {
			$this->ping();
        }

        if (!($this->querys = $this->mysqlFun("query",$sql,$this->link_id)) && $type != 'SILENT')
        {
            $this->error_message[]['message'] = 'MySQL Query Error';
            $this->error_message[]['sql'] = $sql;
            $this->error_message[]['error'] = $this->mysqlFun("error",$this->link_id);
            $this->error_message[]['errno'] = $this->mysqlFun("errno",$this->link_id);

            $this->ErrorMsg();

            return false;
        }

        return $this->querys;
    }

    function affected_rows()
    {
        return $this->mysqlFun("affected_rows",$this->link_id);
    }
    function error()
    {
		return $this->mysqlFun("error",$this->link_id);
    }

    function errno()
    {
        return $this->mysqlFun("errno",$this->link_id);
    }

    function result($query, $row)
    {
		if($this->mysqli){
			mysqli_data_seek($query, $row);
			$row = mysqli_fetch_array($query);
			return $row[0];
		}else{
      		return @mysql_result($query, $row);
		}
    }

    function num_rows($query)
    {
        return $this->mysqlFun("num_rows",$query);
    }

    function num_fields($query)
    {
        return $this->mysqlFun("num_fields",$query);
    }

    function free_result($query)
    {
        return $this->mysqlFun("free_result",$query);
    }

    function insert_id()
    {
        return $this->mysqlFun("insert_id",$this->link_id);
    }

    function fetchRow($query)
    {
        return $this->mysqlFun("fetch_row",$query);
    }
	function fetchAssoc($query)
    {
        return $this->mysqlFun("fetch_assoc",$query);
    }
    function fetch_fields($query)
    {
        return $this->mysqlFun("fetch_field",$query);
    }
	
	

    function version()
    {
        return $this->version;
    }

    function ping()
    {
		return $this->mysqlFun("ping",$this->link_id);
       
    }

    function escape_string($unescaped_string)
    {
        return $this->mysqlFun("real_escape_string",$unescaped_string);
        
    }

    function close()
    {
		return $this->mysqlFun("close",$this->link_id);
       
    }

    function ErrorMsg($message = '', $sql = '')
    {
        if ($message)
        {
            echo "<b>MySQL info</b>: $message\n\n<br /><br />";
           
        }
        else
        {
            echo "<b>MySQL server error report:";
            print_r($this->error_message);
        }

        exit;
    }

    function selectLimit($sql, $num, $start = 0)
    {
        if ($start == 0)
        {
            $sql .= ' LIMIT ' . $num;
        }
        else
        {
            $sql .= ' LIMIT ' . $start . ', ' . $num;
        }

        return $this->query($sql);
    }

    function getOne($sql, $limited = false){
	
        if ($limited == true) {    $sql = trim($sql . ' LIMIT 1');  }
        $res = $this->query($sql);
		
        if ($res !== false)
        {
			
            $row = $this->fetchRow($res);

            if ($row !== false) {
				$arr[] = $row[0];   
                return $arr;
            }else{
                return '';
            }
			
        }else{
		
            return false;
        }
    }
    function getAll($sql)
    {
        $res = $this->query($sql);
        if ($res !== false)
        {
            $arr = array();
            while ($row = $this->fetchAssoc($res))
            {
                $arr[] = $row;
            }

            return $arr;
        }
        else
        {
            return false;
        }
    }
    function getRow($sql, $limited = false)
    {
        if ($limited == true)
        {
            $sql = trim($sql . ' LIMIT 1');
        }

        $res = $this->query($sql);
        if ($res !== false)
        {
            return $this->fetchAssoc($res);
        }
        else
        {
            return false;
        }
    }

   

    function autoExecute($table, $field_values, $mode = 'INSERT', $where = '', $querymode = '')
    {
        $field_names = $this->getCol('DESC ' . $table);

        $sql = '';
        if ($mode == 'INSERT')
        {
            $fields = $values = array();
            foreach ($field_names AS $value)
            {
                if (array_key_exists($value, $field_values) == true)
                {
                    $fields[] = $value;
                    $values[] = "'" . $field_values[$value] . "'";
                }
            }

            if (!empty($fields))
            {
                $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
            }
        }
        else
        {
            $sets = array();
            foreach ($field_names AS $value)
            {
                if (array_key_exists($value, $field_values) == true)
                {
                    $sets[] = $value . " = '" . $field_values[$value] . "'";
                }
            }

            if (!empty($sets))
            {
                $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $where;
            }
        }

        if ($sql)
        {
            return $this->query($sql, $querymode);
        }
        else
        {
            return false;
        }
    }

    function autoReplace($table, $field_values, $update_values, $where = '', $querymode = '')
    {
        $field_descs = $this->getAll('DESC ' . $table);

        $primary_keys = array();
        foreach ($field_descs AS $value)
        {
            $field_names[] = $value['Field'];
            if ($value['Key'] == 'PRI')
            {
                $primary_keys[] = $value['Field'];
            }
        }

        $fields = $values = array();
        foreach ($field_names AS $value)
        {
            if (array_key_exists($value, $field_values) == true)
            {
                $fields[] = $value;
                $values[] = "'" . $field_values[$value] . "'";
            }
        }

        $sets = array();
        foreach ($update_values AS $key => $value)
        {
            if (array_key_exists($key, $field_values) == true)
            {
                if (is_int($value) || is_float($value))
                {
                    $sets[] = $key . ' = ' . $key . ' + ' . $value;
                }
                else
                {
                    $sets[] = $key . " = '" . $value . "'";
                }
            }
        }

        $sql = '';
        if (empty($primary_keys))
        {
            if (!empty($fields))
            {
                $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
            }
        }
        else
        {
            
                if (!empty($fields))
                {
                    $sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
                    if (!empty($sets))
                    {
                        $sql .=  'ON DUPLICATE KEY UPDATE ' . implode(', ', $sets);
                    }
                }
           
        }

        if ($sql)
        {
            return $this->query($sql, $querymode);
        }
        else
        {
            return false;
        }
    }


    function get_table_name($query_item)
    {
        $query_item = trim($query_item);
        $table_names = array();

        /* 判断语句中是不是含有 JOIN */
        if (stristr($query_item, ' JOIN ') == '')
        {
            /* 解析一般的 SELECT FROM 语句 */
            if (preg_match('/^SELECT.*?FROM\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?(?:(?:\s*AS)?\s*`?\w+`?)?(?:\s*,\s*(?:`?\w+`?\s*\.\s*)?`?\w+`?(?:(?:\s*AS)?\s*`?\w+`?)?)*)/is', $query_item, $table_names))
            {
                $table_names = preg_replace('/((?:`?\w+`?\s*\.\s*)?`?\w+`?)[^,]*/', '\1', $table_names[1]);

                return preg_split('/\s*,\s*/', $table_names);
            }
        }
        else
        {
            /* 对含有 JOIN 的语句进行解析 */
            if (preg_match('/^SELECT.*?FROM\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?)(?:(?:\s*AS)?\s*`?\w+`?)?.*?JOIN.*$/is', $query_item, $table_names))
            {
                $other_table_names = array();
                preg_match_all('/JOIN\s*((?:`?\w+`?\s*\.\s*)?`?\w+`?)\s*/i', $query_item, $other_table_names);

                return array_merge(array($table_names[1]), $other_table_names[1]);
            }
        }

        return $table_names;
    }



    //20200419 增加
    function getSql($do, $table, $where = array(), $data = array())
    {
        $sql = $do . " " .$table;

        if (trim($do) == "update") {
            $sql .= " set ";
            $e = count($data);
            $i = 0;
            foreach ($data as $key => $val) {
                $i++;
                $sql .= is_string($val) ? "$key='$val'" : "$key=$val";
                $sql .= $i == $e ? "" : ",";
            }
        }
        if (trim($do) == "insert") {
            $data = $where;
            $sql = $do . " into " . $table;
            $sql .= " (";
            $e = count($data);
            $i = 0;
            foreach ($data as $key => $val) {
                $i++;
                $sql .= $key;
                $sql .= $i == $e ? "" : ",";
            }
            $sql .= " ) values(";
            $i = 0;
            foreach ($data as $key => $val) {
                $i++;
                $sql .= is_string($val) ? "'$val'" : "$val";
                $sql .= $i == $e ? "" : ",";
            }
            $sql .= " )";
        } else {
            if (sizeof($where) > 0) {
                $sql .= " where ";
                foreach ($where as $key => $val) {
                    $sql .= reset($where) == $val ? "" : "and ";
                    $sql .= is_string($val) ? "$key='$val' " : "$key=$val ";
                }
            }
        }
        return $sql;
    }
    function isExist($where, $table)
    {

        $d = $this->getRow($this->getSql("select * from ", $table, $where));
        return  $d;
    }
    function add($data, $table)
    {

        return  $this->publish(0, $data, $table);
    }
    function  del($ID, $isDel = true, $table)
    {
        if (is_array($ID)) {
            $where = $ID;
        } else {
            $where = array("ID" => $ID);
        }
        if (!$isDel) {
            $this->update(array("isDel" => 1), $where, $table);
        } else {
            $this->query($this->getSql("delete from ", $table, $where));
        }
    }
    function update($d, $n, $v)
    {
        if (is_array($d)) {
            $data = $d;
            $where = $n;
            $table = $v;
        } else {
          
            $data = array($n => $v);
            $where = array("ID" => $d);
        }
        return $this->query($this->getSql("update", $v, $where, $data));
    }
    //发布或者更新
    function publish($ID, $data, $table)
    {
      
        $s = false;


        if ($ID  && ((is_array($ID) && $this->isExist($ID, $table)) || (!is_array($ID) && $this->isExist(array("ID" => $ID), $table)))) {

            $s = $this->update($data, is_array($ID) ? $ID : array("ID" => $ID), $table);
            if ($s) {
                $s = $ID;
            }
        } else {

            $s = $this->query($this->getSql("insert", $table, $data));
            if ($s) {
                $s = $this->insert_id();
            }
        }
        return $s;
    }
    
    


    
	function mysqlFun($fun,$v,$r=""){
		if(in_array($fun,array("query"))){
			if(strpos($v,"character_set_connection")>0){
				//不记录
			}else{
				log_message("db",$fun.":".$v);
			}
		}
		if($fun=="select_db"){
			return $this->mysqli?mysqli_select_db($r,$v):mysql_select_db($v,$r);
		}
		if($fun=="get_server_info"){
			return $this->mysqli?mysqli_get_server_info($v):mysql_get_server_info($v);
		}
		if($fun=="query"){
			return $this->mysqli?mysqli_query($r,$v):mysql_query($v,$r);
		}
		if($fun=="fetch_array"){
			return $this->mysqli?mysqli_fetch_array($v,$r):mysql_fetch_array($v,$r);
		}
		if($fun=="affected_rows"){
			return $this->mysqli?mysqli_affected_rows($v):mysql_affected_rows($v);
		}
		if($fun=="error"){
			return $this->mysqli?mysqli_error($v):mysql_error($v);
		}
		if($fun=="errno"){
			return $this->mysqli?mysqli_errno($v):mysql_errno($v);
		}
		if($fun=="num_rows"){
			return $this->mysqli?mysqli_num_rows($v):mysql_num_rows($v);
		}
		if($fun=="num_fields"){
			return $this->mysqli?mysqli_num_fields($v):mysql_num_fields($v);
		}
		if($fun=="free_result"){
			return $this->mysqli?mysqli_free_result($v):mysql_free_result($v);
		}
		if($fun=="insert_id"){
			return $this->mysqli?mysqli_insert_id($v):mysql_insert_id($v);
		}
		if($fun=="fetch_assoc"){
			return $this->mysqli?mysqli_fetch_assoc($v):mysql_fetch_assoc($v);
		}
		if($fun=="fetch_field"){
			return $this->mysqli?mysqli_fetch_field($v):mysql_fetch_field($v);
		}
		if($fun=="ping"){
			return $this->mysqli?mysqli_ping($v):mysql_ping($v);
		}
		if($fun=="real_escape_string"){
			return $this->mysqli?mysqli_real_escape_string($v):mysql_real_escape_string($v);
		}
		if($fun=="close"){
			return $this->mysqli?mysqli_close($v):mysql_close($v);
		}
		if($fun=="fetch_row"){
			return $this->mysqli?mysqli_fetch_row($v):mysql_fetch_row($v);
		}
		
		
		
	}
}

?>