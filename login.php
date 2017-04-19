<?php
include('func.php');
if(CheckLogin(false))
	echo("<script>window.location='index.php'</script>");
	
if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['submit']) && !empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['submit']))
{
	if(Login($_POST['username'], $_POST['password']))
		echo("<script>window.location='index.php'</script>");
	else
		echo("<script>alert('Username or Password may be incorrect , Try again!');</script>");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Login</title>
<link type="text/css" rel="stylesheet" href="files/main_stylesheets.css" />
</head>
<body>

<table id="MainTable" align="center">
<tr><td style="height:30vw"></td></tr>
<tr><td align="center">

	
    <form method="post">
    
    <table id="RoundedBox" align="center">
    <tr>
    	<td style="text-align:left;"><label id="login_label">ID: </label></td>
        <td style="text-align:left;"><input id="login_input" type="text" name="username"></td>
    </tr>
    <tr><td style="height:10px"></td></tr>
    <tr>
    	<td style="text-align:left;"><label id="login_label">Password: </label></td>
        <td style="text-align:left;"><input id="login_input" type="password" name="password"></td>
    </tr>
    <tr><td style="height:10px"></td></tr>
    <tr>
        <td colspan="2"><input id="login_input" type="submit" name="submit" value="Login"></td>
    </tr>
     </table>
      
     </form>
    <tr><td style="height:10px"></td></tr>
    <tr>
    	<td><img src="files/images/LOGO.png" width="60%" /></td>
    </tr>
   
    </table>
    
</body>
</html>
