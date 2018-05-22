<?php
namespace XTeam\Framework\Core;

class Model{
    
    
    public static $database;
    const table_name = "";
    const column_defenitions = array("columns"=>
        array(
            ["name"=>"id","type" => "INTEGER","options"=>"NOT NULL AUTO_INCREMENT"],
            ["name"=>"guid","type" => "VARCHAR(64)","options"=>"UNIQUE"]
        )
    
    , "primary_key"=>"id");
    const linked_classes = array();
    public $id;
    public $guid;
   
    public static function password($password){
        return static::setDB()->password($password);
    }
    
    protected function __construct($id=null){
        self::setDB();
      
        $this->guid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
       
        if ($id != null){
            $this->getMe($id);
        }
    }
    public function toJSON(){
        $rems = array();
        foreach(static::column_defenitions['columns'] as $col){
                if (isset($this->{$col['name']})){
                    if($col['type']=="BLOB"||$col['type']=="MEDIUMBLOB"){
                        $rems[$col['name']]=$this->{$col['name']};
                        $this->{$col['name']} = "file";
                    }
            }
        }
        $str = json_encode($this);
        foreach($rems as $key=>$value){
            $this->{$key} = $value;
        }
        return $str;
    }
    public static function fromGUID($guid){
        $res = static::where("guid",$guid);
        if ($res)
            return reset($res);
        return false;
    }
    public static function where($column, $value, $order = false){
        if (!$order)
        return static::whereSQL("`$column` = '$value'");
        else {
            $str = "WHERE `$column` = '$value' ORDER BY $order";
            return static::get($str);
        }
    }
    public static function getAll(){
        return static::whereSQL("1=1");
    }
    public static function whereSQL($sql){
    
        return static::get("WHERE $sql");
    }
    public static function whereOneSQL($sql){
        $ret = static::whereSQL($sql);
        if ($ret && count($ret)){
            return reset($ret);
        }
        else return false;
    }
    private static function get($sql=""){
        $sql = "SELECT * FROM ".static::table_name." ".$sql;
        $result = static::setDB()->getRows($sql);
        $ret = array();
        return static::arrayToObjectArray($result);
        
    }
    protected static function arrayToObjectArray($result){
         if ($result && count($result)>0){
            
            foreach($result as $r){
                $obj = new static();
                foreach($r as $key=>$value){
                    $obj->{$key} = ($value);
                }
                $ret[$obj->id] = $obj;
            }
            return $ret;
        }
        else return false;
        
    }
    public function getLastError(){
        return self::$database->getLastError();
    }
    public function getLinked($class){
        $res = $this->getLinkVars($class);
        $fid = $res[0];
        $secid = $res[1];
        $table = $res[2];
        if ($fid && $secid){
            $sql = "SELECT * FROM $table WHERE $fid = ".$this->id;
            $result = static::$database->getRows($sql);
            $where = "IN ( ";
            foreach($result as $rr){
                $where.="'".$rr[$secid]."', ";
            }
            $where = rtrim($where,", ");
            $where.=")";
            $sql = "SELECT * FROM ".$class::table_name." WHERE id $where";
            return static::arrayToObjectArray(static::$database->getRows($sql));
        }
        else{
            $sql = "SELECT * FROM ".$class::table_name."WHERE ".static::table_name."_id = '".$this->id."'";
            return static::arrayToObjectArray(static::$database->getRows($sql));
        }
    }
    public function linkObject($object){
        $class = get_class($object);
        $res = $this->getLinkVars($class);
        $fid = $res[0];
        $secid = $res[1];
        $table = $res[2];
        if ($fid && $secid){
            $sql = "INSERT INTO $table ($fid, $secid) VALUES (".$this->id.", ".$object->id.")";
            return static::$database->query($sql);
        }
    }
    public function unlinkObject($object){
        $class = get_class($object);
        $res = $this->getLinkVars($class);
        $fid = $res[0];
        $secid = $res[1];
        $table = $res[2];
        if ($fid && $secid){
            $sql = "DELETE FROM $table WHERE $fid = $this->id AND $secid = $object->id";
            return static::$database->query($sql);
        }
    }
    public function delete(){
        if (static::linked_classes){
            foreach(static::linked_classes as $class){
                $res = getLinkVars($class);
                $fid = $res[0];
                $secid = $res[1];
                $table = $res[2];
                if ($fid && $secid){
                    $sql = "DELTE FROM $table WHERE $fid = $this->id";
                    static::$database->query($sql);
                }
            }
        }
        $sql = "DELETE FROM ".static::table_name." WHERE id = ".$this->id;
        return static::$database->query($sql);
    }
    protected function getLinkVars($class){
        $fid = "";
        $secid = "";
        $table = "";
        if ((static::linked_classes)!=null && in_array($class,static::linked_classes)){
            $secid = $class::table_name."_id";
            $fid = static::table_name."_id";
            $table = static::table_name."_to_".$class::table_name;
        }
        else if ($class::linked_classes!=null && in_array(get_class($this),$class::linked_classes)) {
            $secid = $class::table_name."_id";
            $fid = static::table_name."_id";
            $table = $class::table_name."_to_".static::table_name;
        }
  
        return array($fid,$secid,$table);
    }
    public function save(){
        if (!static::table_name)
            return false;
        $sql = "";
        $defs = array_merge_recursive(self::column_defenitions,static::column_defenitions);
        if ($this->id){
            $sql = "UPDATE ".static::table_name." SET ";
            foreach(static::column_defenitions['columns'] as $col){
                if (isset($this->{$col['name']})){
                    $sql.=" `".$col['name']."` = '".($this->{$col['name']})."', ";
                }
            }
            $sql = rtrim($sql,", ");
            $sql.=" WHERE id = ".$this->id;
        }
        else{
            $sql = "INSERT INTO ".static::table_name." (";
            foreach($defs['columns'] as $col){
                if (isset($this->{$col['name']})){
                    $sql.=" `".$col['name']."`, ";
                }
            }
            $sql = rtrim($sql,", ");
            $sql .= ") VALUES ( ";
            foreach($defs['columns'] as $col){
                if (isset($this->{$col['name']})){
                    $sql.=" '".$this->{$col['name']}."', ";
                }
            }
            $sql = rtrim($sql,", ");
            $sql .= ") ";
            
        }
        if (!static::$database->query($sql)){
            return false;
        }
        if (!$this->id)
            $this->id = static::$database->insert_id();
        return true;
    }
    private function getMe($id){
        $sql = "SELECT * FROM ".static::table_name." WHERE id = $id";
        $result = static::$database->getRows($sql);
        if ($result){
            $result = reset($result);
            foreach($result as $name=>$value){
                $this->{$name} = $value;
            }
        }
    }
  
    public static function fromID($id){
        $sql = "SELECT * FROM ".static::table_name." WHERE id = $id";
        $result = static::$database->getRows($sql);
        if ($result){
            $result = reset($result);
            $var = new static();
            foreach($result as $name=>$value){
                $var->{$name} = $value;
            }
            return $var;
        }
        return false;
    }
    
    protected static function setLinkTable($link){
        $sec_class = $link::table_name;
        $f_class= static::table_name;
        $totname = $f_class."_to_".$sec_class;
        $sql = "DROP TABLE IF EXISTS ".$totname."";
        self::$database->query($sql);
        $c1 = $f_class."_id";
        $c2 = $sec_class."_id";
        $sql = "CREATE TABLE IF NOT EXISTS $totname ($c1 INT, $c2 INT)";
        self::$database->query($sql);
        
    }

    protected static function setDB(){
        if (self::$database == null){
            self::$database = new Database();
           // self::$database->setUTF16();
        }
        return self::$database;
    }
    public static function escapeAll(){
        self::setDB()->escapeAll();;
        
    }
    public static function refreshTable(){
        self::setDB();
        self::checkTable(static::table_name);
    }
    public static function reloadTable(){
        if (!static::table_name)
            return;
        self::setDB();
        $sql = "DROP TABLE IF EXISTS ".static::table_name."";
        
        self::$database->query($sql);
        self::checkTable(static::table_name);
      
        if (count(static::linked_classes)>0){
            foreach(static::linked_classes as $class){
              
                static::setLinkTable($class);
            }
        }
        
    }
    public static function truncate(){
         if (!static::table_name)
            return;
        self::setDB();
        $sql = "TRUNCATE ".static::table_name."";
        self::$database->query($sql);
        
    }
    private static function checkTable($table_name){
       
       
        if (!$table_name)
            return;
        self::setDB();
        $defs = array_merge_recursive(self::column_defenitions,static::column_defenitions);
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (";
        foreach ($defs['columns'] as $column){
            $sql.= "`".$column['name']."` ".$column['type'];
            if (isset($column['options'])){
               $sql.=" ".$column['options'];
            }
            $sql.=", ";
            
        }
        
        
        if (isset($defs['primary_key'])){
            $sql.= " PRIMARY KEY (".$defs['primary_key']."), ";
        }
        $sql = rtrim($sql,", ");
        $sql .= ") ";
        
        self::$database->query($sql);
        foreach($defs['columns'] as $column){
            self::checkColumn($table_name, $column);
        }
        if (isset($defs['indexes'])){
            $sql = "ALTER TABLE `$table_name` ADD INDEX IF NOT EXISTS ".$defs['indexes'];
            self::$database->query($sql);
        }
    }
    private static function checkColumn($table_name, $column){
        $cname = $column['name'];
        $result = self::$database->getRows("SHOW COLUMNS FROM $table_name LIKE '$cname'");
        $exists = (count($result))?TRUE:FALSE;
        if(!$exists) {
           $sql = "ALTER TABLE $table_name ADD `$cname` ".$column['type'];
           if (isset($column['options'])){
               $sql.=" ".$column['options'];
           }
           self::$database->query($sql);
           echo self::$database->getLastError();
        }
    }
    
}