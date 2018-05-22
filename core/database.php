<?php
namespace XTeam\Framework\Core;
global $_SESSION;

class Database{
	private $connection;
	 
	 
	 
	function __construct($username=null,$password=null,$database=null){
		if(!$username)
			$username = MYSQL_USER;
		if(!$password)
			$password = MYSQL_PASSWORD;
		if(!$database)
			$database = MYSQL_DB;
		$con = mysqli_connect("localhost",$username,$password,$database);

		// Check connection
		if (mysqli_connect_errno())
		{
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
			return;
		}
		mysqli_select_db($con,$database);
		$this->connection = $con;
	
			
	//	mysqli_set_charset($con,"utf8mb4");
	}
	public function escapeAll(){
			foreach ($_REQUEST as $id => $value) {
			$_REQUEST[$id] = $this->escape($_REQUEST[$id]);
		}
		
	}
	
	public function query($sql){
		
		$res = mysqli_query($this->connection,$sql);
		return $res;
	}
	public function setUTF(){
		mysqli_set_charset($this->connection,"utf8");
	}
	public function setUTF16(){
		mysqli_set_charset($this->connection,"utf16");
	}
	public function getLastError(){
		return mysqli_error($this->connection);
	}
	public function getRows($result){
	//	mysqli_set_charset($this->connection,"utf8mb4");
		if (is_string($result))
			$result = $this->query($result);
		$teams = array();
		if ($result)
		{
			while($row = mysqli_fetch_assoc($result))
			$teams[] = $row;
		}
		
		return $teams;
	}
	
	function __destruct(){
			mysqli_close($this->connection);
	}
	function escape($value){
		if (!is_array($value)){
		return mysqli_real_escape_string($this->connection,$value);
		}
		else{
			$ret = array();
			foreach($value as $key=>$v){
				$v = $this->escape($v);
				$ret[$key]=$v;
			}
			return $ret;
		}
	}
	function insert_id(){
		return mysqli_insert_id($this->connection);
	}
	
	function password($password){
		return md5(sha1("dbsHash".$password));
	}
	public static function passwordify($password){
		return md5(sha1("dbsHash".$password));
	}
	public static function getImage($filename,$newwidth){

	// get uploaded file name
	$image = $_FILES[$filename]["name"];
 
	if( empty( $image ) ) {
		$error = 'File is empty, please select image to upload.';
	} else if($_FILES[$filename]["type"] == "application/msword") {
		$error = 'Invalid image type, use (e.g. png, jpg, gif).';
	} else if( $_FILES[$filename]["error"] > 0 ) {
		$error = 'Oops sorry, seems there is an error uploading your image, please try again later.';
	} else {
	
		// strip file slashes in uploaded file, although it will not happen but just in case ;)
		$fn = stripslashes( $_FILES[$filename]['name'] );
		$ext = pathinfo($fn, PATHINFO_EXTENSION);
		$ext = strtolower( $ext );
		
		if(( $ext != "jpg" ) && ( $ext != "jpeg" ) && ( $ext != "png" ) && ( $ext != "gif" ) ) {
			$error = 'Unknown Image extension.';
			return false;
		} else {
			// get uploaded file size
			$size = filesize( $_FILES[$filename]['tmp_name'] );
			// get php ini settings for max uploaded file size
			$max_upload = ini_get( 'upload_max_filesize' );
			// check if we're able to upload lessthan the max size
			if( $size > $max_upload )
				$error = 'You have exceeded the upload file size.';
 
			// check uploaded file extension if it is jpg or jpeg, otherwise png and if not then it goes to gif image conversion
			$uploaded_file = $_FILES[$filename]['tmp_name'];
			return self::getImageData($uploaded_file,$ext,$newwidth);
 
		}
 
	}

		
	}
	public static function getImageData($uploaded_file,$ext,$newwidth){
		if( $ext == "jpg" || $ext == "jpeg" )
				$source = imagecreatefromjpeg( $uploaded_file );
			else if( $ext == "png" )
				$source = imagecreatefrompng( $uploaded_file );
			else
				$source = imagecreatefromgif( $uploaded_file );
 
			// getimagesize() function simply get the size of an image
			list( $width, $height) = getimagesize( $uploaded_file );
			$ratio = $height / $width;
 
			// new width 50(this is in pixel format)
			if ($newwidth)
			$nw = $newwidth;
			else $nw = $width;
			$nh = ceil( $ratio * $nw );
			$dst = imagecreatetruecolor( $nw, $nh );
			imagesavealpha($source,true);
			imagefill($dst,0,0,0x7fff0000);
			
 
			imagecopyresampled( $dst, $source, 0, 0, 0,0, $nw, $nh, $width, $height );
 
			
			if( $ext == "jpg" || $ext == "jpeg" )
				imagejpeg($dst, dirname(__FILE__).'/temp.dat');
			else if( $ext == "png" ){
				(imagesavealpha($dst,true));
				
				(imagesavealpha($source,true));
				
				imagepng($dst, dirname(__FILE__).'/temp.dat');
			}
			else
				imagegif($dst, dirname(__FILE__).'/temp.dat');
 
			// I think that's it we're good to clear our created images
			imagedestroy( $source );
			imagedestroy( $dst );
 
			// so all clear, lets save our image to database
		

		
			$str = file_get_contents(dirname(__FILE__).'/temp.dat');
			unlink(dirname(__FILE__).'/temp.dat');
			return array("image"=>$str,"format"=>$ext);
	}
	
	
}

?>