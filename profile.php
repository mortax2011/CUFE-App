<?php include('func.php'); CheckLogin(true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Profile</title>
<link type="text/css" rel="stylesheet" href="files/main_stylesheets.css" />
</head>
<body>

<table id="MainTable" align="center">
	
    <tr>
    	<td id="Header">Profile</td>
    </tr>
    <tr>
    	<td height="90%">
        	<div id="Main_Body">
			<?php 
        include('db_connect.php');
		$Student_ID=getID();
		$sql="SELECT Student_Name_AR,Program_Name,Student_ID,Student_Name_EN,Student_Credits,Student_GPA, Student_Last_GPA FROM Program p,Student s WHERE Student_ID='".$Student_ID."'&&s.Program_ID=p.Program_ID;";
		$run=mysql_query($sql);
		if($row=mysql_fetch_array($run))
		{	

		 echo "<table id='Profile_Table'>";
		  $Student_Name_EN=$row['Student_Name_EN'];
		  $Student_Name_AR=$row['Student_Name_AR'];
		  $Program_Name=$row['Program_Name'];
		  $Student_Credits=$row['Student_Credits'];
		  $Student_Last_GPA=$row['Student_Last_GPA'];
		  $Student_GPA=$row['Student_GPA'];

		echo "<tr><th>Code</th><td> ".$Student_ID."</td></tr>";
		echo "<tr><th>English Name</th><td> ".$Student_Name_EN."</td></tr>";
		echo "<tr><th>Arabic Name</th><td> ".$Student_Name_AR."</td></tr>";
		echo "<tr><th>Program Name</th ><td> ".$Program_Name."</td></tr>";
		echo "<tr><th>last term GPA</th><td> ".$Student_Last_GPA."</td></tr>";
	    	echo "<tr><th>GPA</th><td> ".$Student_GPA."</td></tr>";
		echo "<tr><th>Total Credits</th><td> ".$Student_Credits."</td></tr>";
		
		echo "</table>";		
	}

		?>

			</div>

        </td>

    </tr>

   

    </table>

    

</body>

</html>

