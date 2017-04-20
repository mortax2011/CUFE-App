<?php include('func.php'); CheckLogin(true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Term Classwork</title>
<link type="text/css" rel="stylesheet" href="files/main_stylesheets.css" />
</head>
<body>

<table id="MainTable" align="center">
	
    <tr>
    	<td id="Header">Term Classwork</td>
    </tr>
  <tr>
    	<td height="90%">
        	<div id="Main_Body">
		<th>Subject Code</th><th>Subject Name</th><th>Midterm</th><th>CourseWork</th>
        	</div>
        </td>
    </tr>
	<?php
	
	include('db_connect.php');	
	
	$Student_ID = getID();
	$query=mysql_query("select Course_Code,Course_Name,Midterm,Classwork from enrolled_in,course where course.Course_ID = enrolled_in.Course_ID && enrolled_in.Student_ID = '".$Student_ID."'") or die('Error while Loading the Grades!');	
	
	
	
	while($row=mysql_fetch_array($query))
	{
		#every iteration in the loop represents a row(tupple) which is included in the '$row' array
		$Code = $row['Course_Code'];	#Get Data1 from the row and put it in variable '$Data1'
		$Name= $row['Course_Name'];
		$Midterm= $row['Midterm'];
		$Classwork= $row['Classwork'];
		
		
		#for every iteration we are going to create a row (<tr>)
		#Inside the row, we are going to add both columns (Data1 and Data2)
		echo('<tr>');
			
			
			echo('<td>'.$Code.'</td>');
			echo('<td>'.$Name.'</td>');
			echo('<td>'.$Midterm.'</td>');
			echo('<td>'.$Classwork.'</td>');
			
		echo('</tr>');	
	}
	
	mysql_close();	#Closes the mysql server connection
	
?>
    </table>
    
</body>
</html>
