<?php 
class DB_Manager
{
	public static $connection;
	public static $con;
	private static $servername="sql208.rf.gd";
	private static $username="rfgd_19086182";
	private static $password="cufe123";
	private static $dbname="rfgd_19086182_cufe";
	
	private function __construct()
	{
		self::$con = new mysqli(self::$servername, self::$username, self::$password, self::$dbname) or die("Database connection error!");
	}
	
	public static function Query($SQL)
	{
		if(!isset(self::$connection))
			self::$connection=new DB_Manager;
		if($query=self::$con->query($SQL))
			return $query;
		return false;
	}
	
	private function __destruct()
	{
		mysqli_close(self::$con);
	}
}

?>
