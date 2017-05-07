<?php include('func.php'); CheckLogin(true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>GPA Transcript</title>
<link type="text/css" rel="stylesheet" href="files/main_stylesheets.css" />
<script src="js_func.js" language="javascript"></script>
</head>
<body>

<table id="MainTable" align="center">
	
    <tr>
    	<td id="Header">GPA Transcript</td>
    </tr>
    <tr>
    	<td height="90%">
        	<div id="Main_Body">

					<?php 

		$Student_ID=getID();

		$sql="SELECT Student_Code,Student_Name_EN,Student_Credits,Student_GPA, Student_Last_GPA FROM Student s WHERE Student_ID=".$Student_ID;

		DB_Manager::Query("set names utf8");

		$run=DB_Manager::Query($sql);
	
	
	if($row=$run->fetch_assoc())

	{			

		$Student_Code=$row['Student_Code'];
		$Student_Last_GPA=$row['Student_Last_GPA'];
		$Student_GPA=$row['Student_GPA'];
		$Student_Credits=$row['Student_Credits'];

		echo '<div id="Timetable_Div" style="text-align:center;">'.$Student_Code." ". $row['Student_Name_EN']." </div>";		
		echo '	<table id="Timetable_Table" align="center">
		<tr class="GPA_Header">
						<th>Course Code</th>
						<th>Course Title</th>
						<th>Grade</th>
						<th>Hours</th>
						<th>Quality Points</th>
		</tr>';
	$Semester_ID_Prev="";
	
	$sql="SELECT C.Course_Code, C.Course_Name, C.Course_Credits, G.Grade, S.Semester_ID,S.Semester_Name FROM Course C, Grades G, Semester S WHERE C.Course_ID=G.Course_ID AND G.Semester_ID=S.Semester_ID AND G.Student_ID=".getID()." ORDER BY S.Semester_ID;";
	$query=DB_Manager::Query($sql);
	while($row=$query->fetch_assoc())
	{
		$Course_Code=$row['Course_Code'];
		$Semester_ID_Next=$row['Semester_ID'];
		$Grade=$row['Grade'];
		$Semester_Name=$row['Semester_Name'];
		$Course_Credits=$row['Course_Credits'];
		$Course_Name=$row['Course_Name'];
		$Quality_points=calc_quality($Grade, $Course_Credits);
		
		if($Semester_ID_Prev!=$Semester_ID_Next)
			echo("<tr><th colspan='5'>".$Semester_Name."</th></tr>");
		$Semester_ID_Prev=$Semester_ID_Next;
		
		echo "
		<tr>
		<td>".$Course_Code."</td><td>".$Course_Name."</td><td>".$Grade."</td><td>".$Course_Credits."</td><td>".$Quality_points."</td>
		</tr>
		" ; 
	}
	echo "<tr class='GPA_Footer'><td colspan='5'>Cumulative GPA = ".$Student_GPA."</td></tr>";
	echo "<tr class='GPA_Footer'><td colspan='5'>Last Term GPA = ".$Student_Last_GPA."</td></tr>";
	echo "<tr class='GPA_Footer'><td colspan='5'>Total Credits = ".$Student_Credits."</td></tr>";
	echo('</table>');
	}
		?>
        	</div>
        </td>
    </tr>
	 <tr>
    
    </tr>
   
    </table>
    
</body>
</html>
