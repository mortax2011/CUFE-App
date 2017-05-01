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
		 <table id="Classwork_Table">
            <tr>
		<th>Subject Code</th><th>Subject Name</th><th>Midterm</th><th>CourseWork</th>
   	    </tr>
	<?php	
	$Student_ID = getID();
	$query=DB_Manager::Query("select Course_Code,Course_Name,Midterm,Classwork from Grades,Course,Semester where Course.Course_ID = Grades.Course_ID && Grades.Semester_ID = Semester.Semester_ID && Semester.Is_Current = 1 && Grades.Student_ID = '".$Student_ID."'") or die('Error while Loading the Grades!');	
	while($row=$query->fetch_assoc())
	{
		$Code = $row['Course_Code'];
		$Name= $row['Course_Name'];
		$Midterm= $row['Midterm'];
		$Classwork= $row['Classwork'];
		echo('<tr>');
		echo('<td>'.$Code.'</td>');
		echo('<td>'.$Name.'</td>');
		echo('<td>'.$Midterm.'</td>');
		echo('<td>'.$Classwork.'</td>');
		echo('</tr>');	
	}
?>
    </table>
    </div>
    </td></tr>
    </table>
</body>

</html>
