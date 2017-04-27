<?php
include('DB_Manager.php');
session_start();

//Some Useful Functions
function Location($url)
{
	echo("<script>window.location='".$url."'</script>");
}

function Message($msg)
{
	echo("<script>alert('".$msg."');</script>");
}

function getID()
{
	return encrypt_decrypt('decrypt', $_SESSION['username']);	
}

function Session_ON()
{
	if(isset($_SESSION['username']) && !empty($_SESSION['username']))
		return true;
	return false;
}

function CheckLogin($redirect)
{
	if(!Session_ON())
	{
		if($redirect==true)
			Location('login.php');
		else
			return false;
	}
	return true;
}


//Check for timeout
if(Auto_Logout())
	Logout();

function Login($username, $password)
{
	//Remove Whitespaces
	$Student_Username = preg_replace('/\s+/', '', $username);
	$Student_Password = preg_replace('/\s+/', '', $password);
	
	//Remove Double Quotations
	$Student_Username = preg_replace('/"/', '', $Student_Username);
	$Student_Password = preg_replace('/"/', '', $Student_Password);
	
	//Remove Single Quotes
	$Student_Username = preg_replace("/'/", '', $Student_Username);
	$Student_Password = preg_replace("/'/", '', $Student_Password);
	
	$query=DB_Manager::Query("SELECT S.Student_ID FROM Login L, Student S WHERE L.Student_ID=S.Student_ID AND S.Student_Code=".$Student_Username." AND Student_Password='".$Student_Password."';");
		
	$row=$query->fetch_assoc();
	
	if(!empty($row))
	{
		//Authenticated!		
		$_SESSION['username']=encrypt_decrypt('encrypt', $row['Student_ID']);
		$_SESSION["login_time"]=time();
		return true;
	}
	else
		return false;
}

function Logout()
{
	session_unset();
	session_destroy();
	Location('login.php');
}

function Auto_Logout()
{
	if(Session_ON())
	{
		$TimeOut=10;	//Timeout in minutes
		$t=time();
		$t0=$_SESSION["login_time"];
		$diff=$t - $t0;
		if($diff>($TimeOut*60) || !isset($t0))
			return true;
		else
			$_SESSION["login_time"]=time();	
	}
}

function encrypt_decrypt($action, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'Faculty of Engineering';
    $secret_iv = 'Cairo University';
    // hash
    $key = hash('sha256', $secret_key);
    
    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

//TimeTable Functions
function calculate_semester_credits($Student_ID,$Semester_ID)
{		
	$sql="SELECT SUM(Course_Credits) FROM Course C, Enrolled_In E
	WHERE E.Student_ID=".$Student_ID." AND E.Semester_ID=".$Semester_ID." AND C.Course_ID=E.Course_ID;";
	
	$run=mysql_query($sql);
	$row=mysql_fetch_array($run);
	$sum=$row['SUM(Course_Credits)'];
	
	if(!empty($sum))
		return $sum;
	return 0;
}
?>
