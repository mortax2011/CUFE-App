<?php include('func.php'); CheckLogin(true); $Number_Of_Days=0;	$Previous_Slot_Day=" ";
$Week_Days=array("Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday"); #For freshman and rest
 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>TimeTable</title>
<link type="text/css" rel="stylesheet" href="files/main_stylesheets.css" />
</head>
<body>

<table id="MainTable" align="center">
	
    <tr>
    	<td id="Header">TimeTable</td>
    </tr>
    <tr>
    	<td height="90%">
        	<div id="Main_Body">
		<?php 
			include('db_connect.php');
			$Student_ID=getID();
	$sql="SELECT Student_Name_EN,Student_Credits,Student_GPA FROM Student WHERE Student_ID='".$Student_ID."';";
	$run=mysql_query($sql);
	
	if($row=mysql_fetch_array($run))
	{			
		echo '<div  id="Timetable_Div_Basic">'.$Student_ID." ". $row['Student_Name_EN']." ".'['.$row['Student_Credits'].']'." ".'['.$row['Student_GPA']."]</div>";
		echo '<div  id="Timetable_Div">'."You are registered in the following courses only for ";
		$sql= "SELECT Semester_ID, Semester_Name FROM Semester WHERE Is_Current=1;";
		
		$run=mysql_query($sql);
		$row=mysql_fetch_array($run);
		$Semester_Name=$row['Semester_Name'];
		$Semester_ID=$row['Semester_ID'];
		$Semester_credits=calculate_semester_credits($Student_ID,$Semester_ID);
		$Semester_Name=str_replace(" "," Semester 	",$Semester_Name);
		echo $Semester_Name." ( ".$Semester_credits." Credits )<br><br>";
					
		

		$sql_final="SELECT C.Course_Name,C.Course_Code,T.Slot_Type,T.Slot_Day,T.Slot_From,T.Slot_To,T.Slot_Location FROM Enrolled_In as E, Course as C, Time_Slot as T WHERE E.Student_ID='".$Student_ID."' AND E.Semester_ID='".$Semester_ID."' AND E.Course_ID=C.Course_ID AND E.Slot_ID=T.Slot_ID ORDER BY Slot_Day,Slot_From;";
		$run_final=mysql_query($sql_final);
		echo '<table id="Timetable_Table">';
		
		while($rows_final=mysql_fetch_array($run_final))
		{			
			$Course_Code=$rows_final['Course_Code'];
			$Course_Name=$rows_final['Course_Name'];

			$Slot_Type=$rows_final['Slot_Type'];										
			$Slot_Day_Number=$rows_final['Slot_Day'];
			$Slot_Day=$Week_Days[$Slot_Day_Number];
			$Slot_From=$rows_final['Slot_From'];					
			$Slot_To=$rows_final['Slot_To'];
			$Slot_Location=$rows_final['Slot_Location'];									
			
			if($Slot_Day!=$Previous_Slot_Day)
			{
				$Previous_Slot_Day=$Slot_Day;
				echo "<tr><th align=center>".$Slot_Day."</th></tr>";
			}
									
			#Slot from and Slot to modification HH:MM rather than HH:MM:SS
			$Slot_From= substr($Slot_From,0,5);
			$Slot_To= substr($Slot_To,0,5);								
										
			echo "<tr><td>".$Course_Code.", ".$Course_Name." : ".$Slot_Type." At ".$Slot_Location." From ".$Slot_From." To ".$Slot_To."</td></tr>";														
		}
					
	}
										
	echo "</table>";			
	
?>
        	</div>
        </td>
		
	
    </tr>
   
    </table>
    
</body>
</html>
