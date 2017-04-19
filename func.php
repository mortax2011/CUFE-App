<?php

session_start();

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
	
	//Secure Inputs From Any Special Chars
	$Student_Username = mysql_real_escape_string($Student_Username);
	$Student_Password = mysql_real_escape_string($Student_Password);
	
	include('db_connect.php');
	$query=mysql_query("SELECT * FROM Login WHERE Student_ID='".$Student_Username."' AND Student_Password='".$Student_Password."';");
	$row=mysql_fetch_array($query);
	
	if(!empty($row))
	{
		//Authenticated!		
		$_SESSION['username']=encrypt_decrypt('encrypt', $Student_Username);
		return true;
	}
	else
		return false;
	
}
function Logout()
{
	session_destroy();	
	unset($_SESSION['username']);
}

function CheckLogin($redirect)
{
	if(!isset($_SESSION['username'])||empty($_SESSION['username']))
	{
		if($redirect==true)
			echo("<script>window.location='login.php'</script>");
		else
			return false;
	}
	return true;
}

function getID()
{
	return encrypt_decrypt('decrypt', $_SESSION['username']);	
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

?>