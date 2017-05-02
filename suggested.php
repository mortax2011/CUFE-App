<?php include('func.php'); CheckLogin(true); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Suggested TimeTable</title>
<link type="text/css" rel="stylesheet" href="files/main_stylesheets.css" />
<script src="js_func.js" language="javascript"></script>
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
					
					
					$SelectedCourse;
					if(!isset($_SESSION['SuggestedSelectedCourses']))
						$_SESSION['SuggestedSelectedCourses']=array();
					if(!empty($_SESSION['SuggestedSelectedCourses']) && isset($_POST['submit']) && $_POST['submit']=="Generate")
						GenerateSuggested($_SESSION['SuggestedSelectedCourses'], array(0,1, 2, 3, 4, 5));
					else
					{	
						if(isset($_POST['SelectedCourse']))
							$SelectedCourse=$_POST['SelectedCourse'];
						if(!empty($SelectedCourse) && in_array($SelectedCourse, $AvailableCourses) && !in_array($SelectedCourse, $_SESSION['SuggestedSelectedCourses']) && $_POST['submit']=="Add")
							array_push($_SESSION['SuggestedSelectedCourses'], $SelectedCourse);
							
						if(isset($_POST['RemoveCourse']) && !empty($_POST['RemoveCourse']) && in_array($_POST['RemoveCourse'], $_SESSION['SuggestedSelectedCourses']) && $_POST['submit']=="Delete")
						{
							foreach (array_keys($_SESSION['SuggestedSelectedCourses'], $_POST['RemoveCourse']) as $key)
								unset($_SESSION['SuggestedSelectedCourses'][$key]);
						}
						if(isset($_POST['submit']) && (($_POST['submit']=="Add") || $_POST['submit']=="Delete"))
							goBack();
						
						echo('<table id="Suggested_Controls_Table"><form id="Suggested_Form" action="suggested.php" method="post">');
						echo('<tr>');
							echo('<td colspan="2"><label id="login_label">Select a Course</label></td>');
						echo('</tr>');
						
						echo('<tr>');
							echo('<td colspan="2"><select name="SelectedCourse" id="login_input">');
								foreach($AvailableCourses as $Course)
								{
									if(!in_array($Course, $_SESSION['SuggestedSelectedCourses']))
									{
										echo('<option value="'.$Course.'">'); 
										
											$query=DB_Manager::Query("SELECT Course_Name FROM Course WHERE Course_ID=".$Course);
											$row=$query->fetch_assoc();
											echo($row['Course_Name']);
										echo('</option>');
									}
								}
							echo('</select></td>');
						echo('</tr>');
						
						echo('<tr>');
						echo('<td><input type="submit" name="submit" value="Add" id="login_input"></td>');
						
						if(isset($_SESSION['SuggestedSelectedCourses'])&&!empty($_SESSION['SuggestedSelectedCourses']))
						{
							echo('<td><input type="submit" name="submit" value="Delete" id="login_input"></td>');
							echo('</tr>');
							echo('<tr>');
								echo('<td colspan="2"><input type="submit" name="submit" value="Generate" id="login_input"></td>');
							echo('</tr>');
							echo('</table>');
							printSelectedSuggested();	//Print Currently Selected Courses
						}
						else
							echo('</tr></table>');
							
						echo('</form></table><br>');
						#GenerateSuggested($AvailableCourses, array(0,1, 2, 3, 4, 5))
						#GenerateSuggested(array(57, 36, 20 , 94, 38, 25, 24, 52), array(1, 2, 3, 4, 5)); 
					}
				?>
        	</div>
        </td>
    </tr>
    </table>
</body>
</html>
