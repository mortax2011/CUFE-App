<?php include('func.php'); CheckLogin(true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Suggested TimeTable</title>
<link type="text/css" rel="stylesheet" href="files/main_stylesheets.css" />
</head>
<body>
<table id="MainTable" align="center">
    <tr>
    	<td id="Header">Sugggested TimeTable</td>
    </tr>
    <tr>
    	<td height="90%">
        	<div id="Main_Body">
        		<?php 
				$AvailableCourses=array();
				$SQL="SELECT C.Course_ID FROM Course C, Offered_For O, Student S WHERE C.Course_ID=O.Course_ID AND S.Student_ID=".getID()." AND S.Program_ID=O.Program_ID AND O.Course_ID Not In (SELECT Course_ID FROM Grades WHERE Student_ID=".getID().");";
				$query=DB_Manager::Query($SQL);
				while($row=$query->fetch_assoc())
				{
					if(arePassed(getPrerequisites($row['Course_ID'])))
						array_push($AvailableCourses, $row['Course_ID']);
				}
					GenerateSuggested($AvailableCourses, array(0,1, 2, 3, 4, 5))
				#GenerateSuggested(array(57, 36, 20 , 94, 38, 25, 24, 52), array(1, 2, 3, 4, 5)); 

			?>
        	</div>
        </td>
    </tr>
    </table>
</body>
</html>
