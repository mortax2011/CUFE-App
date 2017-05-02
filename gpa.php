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

		$sql="SELECT Student_Name_AR,Program_Name,Student_Code,Student_Name_EN,Student_Credits,Student_GPA, Student_Last_GPA FROM Program p,Student s WHERE Student_ID='".$Student_ID."' AND s.Program_ID=p.Program_ID;";

		DB_Manager::Query("set names utf8");

		$run=DB_Manager::Query($sql);
	
	
	if($row=$run->fetch_assoc())

	{			

		$Student_Code=$row['Student_Code'];
		$Student_Last_GPA=$row['Student_Last_GPA'];
		$Student_GPA=$row['Student_GPA'];
		$Student_Credits=$row['Student_Credits'];

		echo '<div  id="GPATranscript_Div_Basic">'.$Student_Code." ". $row['Student_Name_EN']." </div>";
		echo '<div  id="GPATranscript_Div">Transcript for calculating GPA "';
		
		echo '	<table id="GPA Table Header" align="center">
		<tr>
						<td style= border: 1px solid black > Course NO </td>
						<td style= border: 1px solid black > Course Title </td>
						<td style= border: 1px solid black >  Grade </td>
						<td style= border: 1px solid black >  Hours </td>
						<td style= border: 1px solid black >  Quality Points </td>
		</tr>
		</table>';
	
	
	$sql_first="SELECT * FROM Grades WHERE Student_ID='".$Student_ID."' ORDER BY Semester_ID;";
	$run_first=DB_Manager::Query($sql_first);
	if($row=$run_first->fetch_assoc())
	{
	$Course_ID=$row['Course_ID'];
	$Semester_ID_Prev=$row['Semester_ID'];
	$Semester_ID_Next=$row['Semester_ID'];
	$Grade=$row['Grade'];
	
	$sql_Semester="SELECT * FROM Semester WHERE Semester_ID='".$Semester_ID_Prev."'";
	$run_Semester=DB_Manager::Query($sql_Semester);
	if($row=$run_Semester->fetch_assoc())
	{
		$Semester_Name=$row['Semester_Name'];
	}
	$sql_Course="SELECT * FROM Course WHERE Course_ID='".$Course_ID."'";
	$run_Course=DB_Manager::Query($sql_Course);
	if($row=$run_Course->fetch_assoc())
	{
		$Course_Credits=$row['Course_Credits'];
		$Course_Name=$row['Course_Name'];
	}
	$Quality_points=calc_quality($Grade, $Course_Credits);
	echo	"<table id=GPATranscript_main>
	<tr>
	<th id=semester_title colspan='5'>".$Semester_Name."
	</th>
	</tr>
	<tr>
	<td>".$Course_ID."</td><td>".$Course_Name."</td><td>".$Grade."</td><td>".$Course_Credits."</td><td>".$Quality_points."</td>
	</tr>
	
	" ; 
	}
	while($row=$run_first->fetch_assoc())
	{
	$Course_ID=$row['Course_ID'];
	$Semester_ID_Next=$row['Semester_ID'];
	$Grade=$row['Grade'];
	
	if($Semester_ID_Next==$Semester_ID_Prev)
	{
	$sql_Semester="SELECT * FROM Semester WHERE Semester_ID='".$Semester_ID_Prev."'";
	$run_Semester=DB_Manager::Query($sql_Semester);
	if($row=$run_Semester->fetch_assoc())
	{
		$Semester_Name=$row['Semester_Name'];
	}
	$sql_Course="SELECT * FROM Course WHERE Course_ID='".$Course_ID."'";
	$run_Course=DB_Manager::Query($sql_Course);
	if($row=$run_Course->fetch_assoc())
	{
		$Course_Credits=$row['Course_Credits'];
		$Course_Name=$row['Course_Name'];
	}
	 $Quality_points=calc_quality($Grade, $Course_Credits);
	echo	"
	<tr>
	<td>".$Course_ID."</td><td>".$Course_Name."</td><td>".$Grade."</td><td>".$Course_Credits."</td><td>".$Quality_points."</td>
	</tr>
	
	" ; 
	}
	else{
	$Semester_ID_Prev=$row['Semester_ID'];
	$Course_ID=$row['Course_ID'];
	$Semester_ID_Next=$row['Semester_ID'];
	$Grade=$row['Grade'];
	
	$sql_Semester="SELECT * FROM Semester WHERE Semester_ID='".$Semester_ID_Prev."'";
	$run_Semester=DB_Manager::Query($sql_Semester);
	if($row=$run_Semester->fetch_assoc())
	{
		$Semester_Name=$row['Semester_Name'];
	}
	$sql_Course="SELECT * FROM Course WHERE Course_ID='".$Course_ID."'";
	$run_Course=DB_Manager::Query($sql_Course);
	if($row=$run_Course->fetch_assoc())
	{
		$Course_Credits=$row['Course_Credits'];
		$Course_Name=$row['Course_Name'];
	}
	$Quality_points=calc_quality($Grade, $Course_Credits);
	echo	"
	<tr>
	<th id=semester_title colspan='5'>" .$Semester_Name."
	</th>
	</tr>
	<tr>
	<td>".$Course_ID."</td><td>".$Course_Name."</td><td>".$Grade."</td><td>".$Course_Credits."</td><td>".$Quality_points."</td>
	</tr>
	
	" ; 
	}
	
	}
	echo' </table>' ;
	
	echo " <div > Cumulative GPA = ".$Student_GPA." </div>";
		
	echo " <div > Last Term GPA = ".$Student_Last_GPA." </div>";
	
	echo " <div > Total Credits = ".$Student_Credits." </div>";
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
