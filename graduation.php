<?php include('func.php'); CheckLogin(true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Graduation Report</title>
<link type="text/css" rel="stylesheet" href="files/main_stylesheets.css" />
<script src="js_func.js" language="javascript"></script>
</head>
<body>

<table id="MainTable" align="center">
	
    <tr>
    	<td id="Header">Graduation Report</td>
    </tr>
    <tr>
    	<td height="90%">
        	<div id="Main_Body">
        
			<?php 
				
	$Student_ID=getID();
	$sql="SELECT Student_Code,Student_Name_EN,Student_Credits,Student_GPA FROM Student WHERE Student_ID='".$Student_ID."';";
	$run=DB_Manager::Query($sql);

	$Sum_Total_Credits=0;
	$Sum_Actual_Credits=0;
	$Sum_Points=0;
	$Previous_Type_Name="";
	
	if($row=$run->fetch_assoc())

	{			
		$Student_Code=$row['Student_Code'];
		echo '<br><div id="Timetable_Div_Basic" style="text-align:center;">'.$Student_Code." ". $row['Student_Name_EN']." ".'['.$row['Student_Credits'].']'." ".'['.$row['Student_GPA']."]</div>";
		
		$Student_GPA=$row['Student_GPA'];
		
		$run=DB_Manager::Query($sql);
		$row=$run->fetch_assoc();
			
		
		$sql_final="
		SELECT Course.Course_ID,Course_Code,Course_Name,Course_Credits,Grade,Type_Name, Course_Type.Type_ID
		FROM 
		Offered_For JOIN Student ON Student.Program_ID = Offered_For.Program_ID
		JOIN Course ON Offered_For.Course_ID = Course.Course_ID
		JOIN Course_Type ON Course.Type_ID = Course_Type.Type_ID
		LEFT JOIN Grades ON Student.Student_ID = Grades.Student_ID AND Grades.Course_ID = Course.Course_ID
		WHERE Student.Student_ID =".$Student_ID." ORDER BY Course.Type_ID ASC, Grades.Grade ASC, Course.Course_Code ASC";
		
		$run_final=DB_Manager::Query($sql_final);

		echo '<table id="Timetable_Table" align="center">';

		while($rows_final=$run_final->fetch_assoc())

		{			
			$Course_Code=$rows_final['Course_Code'];
			$Course_Name=$rows_final['Course_Name'];
			$Course_Credits=$rows_final['Course_Credits'];
			$Grade=$rows_final['Grade'];
			if(empty($Grade))
				$Grade='-';
			$Actual_Credits=$Course_Credits;
			$Points = calc_quality($Grade,$Course_Credits);
			$Type_Name=$rows_final['Type_Name'];
			$Type_ID=$rows_final['Type_ID'];
			if($Type_Name!=$Previous_Type_Name)

			{
				$Type_Actual=Type_Actual_Credits($Student_ID, $Type_ID);
				$Type_Total=Type_Total_Credits($Student_ID, $Type_ID);
				
				$Sum_Total_Credits+=$Type_Actual;
				$Sum_Actual_Credits+=$Type_Actual;
				
				$Previous_Type_Name=$Type_Name;
				echo '<tr><th colspan="6" align="left">'.$Type_Name.'</th></tr>';
				echo '<tr><th colspan="6" align="left"> Passed '.$Type_Actual.' Credits Out of '.$Type_Total.' Credits Required</th></tr>';
				echo '	
		<tr class="Graduation_Headers">
						<td>Code</td>
						<td>Subject Name</td>
						<td>Credits</td>
						<td>Grade</td>
						<td>Actual Credit </td>
						<td>Points</td>
		</tr>
		';
			}
			
			echo '<tr class="';
			if($Grade!='-')
				echo 'Graduation_Passed';
			else
				echo 'Graduation_NotPassed';	 
			echo '"><td>'.$Course_Code."</td><td>".$Course_Name." </td><td> ".$Course_Credits." </td><td> ".$Grade." </td><td> ".$Actual_Credits." </td><td> ".$Points.'</td></tr>';	

			$Sum_Points=$Sum_Points+$Points;
		}
	}
		echo '<tr class="Graduation_Passed"><td colspan="2"> Cumulative GPA = '.$Student_GPA." </td><td>".$Sum_Total_Credits."</td><td></td><td>".$Sum_Actual_Credits."</td><td>".$Sum_Points.'</td></tr>';
		
		echo "</table>";

			?>

			
        	</div>
        </td>
    </tr>
   
    </table>
    
</body>
</html>
