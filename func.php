<?php

include('DB_Manager.php');

session_start();


/* ************************************************************************** */
//Some Useful Functions
/* ************************************************************************** */

function Location($url)

{
	echo("<script>window.location='".$url."'</script>");
}

function Message($msg)

{
	echo("<script>alert('".$msg."');</script>");
}

function goBack()
{
	    echo("<script>window.history.back();</script>");
}

function getID()

{
	return encrypt_decrypt('decrypt', $_SESSION['username']);	
}

function Session_ON()

{
	if(isset($_SESSION['username']) && !empty($_SESSION['username']))
		return true;
	return false;
}

function CheckLogin($redirect)
{
	if(!Session_ON())
	{
		if($redirect==true)
			Location('login.php');
		else
			return false;
	}
	return true;
}

//Check for timeout
if(Auto_Logout())
	Logout();

function Login($username, $password)
{
	//Remove Whitespaces
	$Student_Username = preg_replace('/\s+/', '', $username);
	$Student_Password = preg_replace('/\s+/', '', $password);

	//Remove Double Quotations
	$Student_Username = preg_replace('/"/', '', $Student_Username);
	$Student_Password = preg_replace('/"/', '', $Student_Password);

	//Remove Single Quotes
	$Student_Username = preg_replace("/'/", '', $Student_Username);
	$Student_Password = preg_replace("/'/", '', $Student_Password);
	$query=DB_Manager::Query("SELECT S.Student_ID FROM Login L, Student S WHERE L.Student_ID=S.Student_ID AND S.Student_Code=".$Student_Username." AND Student_Password='".$Student_Password."';");
	
	if($query)	
		$row=$query->fetch_assoc();
	if(!empty($row))
	{
		//Authenticated!		
		$_SESSION['username']=encrypt_decrypt('encrypt', $row['Student_ID']);
		$_SESSION["login_time"]=time();
		return true;
	}
	return false;
}

function Logout()
{	
	unset($_SESSION);
	session_unset();
	session_destroy();
	Location('login.php');
}

function Auto_Logout()
{
	if(Session_ON())
	{
		$TimeOut=10;	//Timeout in minutes
		$t=time();
		$t0=$_SESSION["login_time"];
		$diff=$t - $t0;
		if($diff>($TimeOut*60) || !isset($t0))
			return true;
		else
			$_SESSION["login_time"]=time();	
	}
}

function encrypt_decrypt($action, $string)
{
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'Faculty of Engineering';
    $secret_iv = 'Cairo University';
    
    // hash
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ( $action == 'encrypt' )
    {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    }
    else if( $action == 'decrypt' )
    {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}


/* ************************************************************************** */
//TimeTable Functions
/* ************************************************************************** */

function calculate_semester_credits($Student_ID,$Semester_ID)

{		
	$sql="SELECT SUM(Course_Credits) FROM Course C, Enrolled_In E
	WHERE E.Student_ID=".$Student_ID." AND E.Semester_ID=".$Semester_ID." AND C.Course_ID=E.Course_ID;";

	$run=DB_Manager::Query($sql);
	$row=$run->fetch_assoc();

	$sum=$row['SUM(Course_Credits)'];

	if(!empty($sum))
		return $sum;
	return 0;
}


/* ************************************************************************** */
//Suggested TimeTable Functions
/* ************************************************************************** */

function PrintSchedule($arr)
{
	$Week_Days=array("Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday");
	$Number_Of_Days=0;
	$Previous_Slot_Day=" ";
	
	$SelectedSlots="";
	
	foreach($arr as $record)
	{
		$SelectedSlots=$SelectedSlots.$record["Slot_ID"].",";
	}
	$SelectedSlots=substr($SelectedSlots, 0, -1);
	
	$TotalCredits=getSuggestedCredits($arr);
		
		echo('<br><div id="Suggested_Div">Schedule Credits: '.$TotalCredits.'</div>');
		
		$sql_final="SELECT C.Course_Name,C.Course_Code,T.Slot_Type,T.Slot_Day,T.Slot_From,T.Slot_To,T.Slot_Location FROM Offered_In O, Course C, Time_Slot T WHERE O.Course_ID=C.Course_ID AND O.Slot_ID=T.Slot_ID AND T.Slot_ID in (".$SelectedSlots.") ORDER BY Slot_Day,Slot_From;";
	
		$run_final=DB_Manager::Query($sql_final);
		echo '<table id="Timetable_Table">';
		
		while($rows_final=$run_final->fetch_assoc())
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


function CheckLecTut($Array, $GensArray, $SpecialsArray)
{
	$temparray=array();
	foreach($Array as $x)
	{
		$keep=false;
		foreach($Array as $y)
		{
			if($x["Course_ID"]==$y["Course_ID"] && $x["Slot_ID"]!=$y["Slot_ID"] && $x["Slot_Type"]!=$y["Slot_Type"] &&(!in_array($x['Course_ID'], $SpecialsArray) || in_array($x['Course_ID'], $SpecialsArray) && $x['Slot_Group']==$y['Slot_Group']))
				$keep=true;
		}
		if(in_array($x['Course_ID'], $GensArray) || $keep)
				array_push($temparray, array("Slot_ID"=>$x['Slot_ID'], "Slot_From"=>$x['Slot_From'], "Slot_To"=>$x['Slot_To'], "Slot_Type"=>$x["Slot_Type"], "Course_ID"=>$x["Course_ID"], "Type_ID"=>$x["Type_ID"], "Slot_Day"=>$x["Slot_Day"], "Slot_Group"=>$x["Slot_Group"]));
	}
	return $temparray;
}

function getSuggestedCredits($arr)
{
	$SelectedSlots="";
	
	foreach($arr as $record)
	{
		$SelectedSlots=$SelectedSlots.$record["Slot_ID"].",";
	}
	$SelectedSlots=substr($SelectedSlots, 0, -1);
	DB_Manager::Query("set names utf8");
	
	//Kero's timetable edited
		$sql_final="SELECT SUM(Course_Credits) AS Total_Credits FROM Course WHERE Course_ID in (SELECT DISTINCT C.Course_ID FROM Offered_In O, Course C, Time_Slot T WHERE O.Course_ID=C.Course_ID AND O.Slot_ID=T.Slot_ID AND T.Slot_ID in (".$SelectedSlots."));";
		$run_final=DB_Manager::Query($sql_final);
		$row=$run_final->fetch_assoc();
		$TotalCredits=$row['Total_Credits'];
	return $TotalCredits;	
}

function getSlots($All_Slots, $Suggested)
{
	$NewSuggested=$Suggested;
	foreach($All_Slots as $x)
	{	
		$append=true;

		foreach($NewSuggested as $z)
		{
			if($x["Course_ID"]==$z["Course_ID"] && 	$x["Slot_Type"]==$z["Slot_Type"])
			{
				$append=false;
				break;
			}

			$append=true;
		}
				
		foreach($NewSuggested as $y)
		{
			if($append && (($x["Slot_From"]<$y["Slot_From"] && $x["Slot_To"]<$y["Slot_From"]) || ($x["Slot_From"]>$y["Slot_To"] && $x["Slot_To"]>$y["Slot_To"]) || $x["Slot_Day"]!=$y["Slot_Day"]) && !in_array($x,$Suggested) && !($x['Course_ID']==$y['Course_ID'] && $x['Slot_Type']==$y['Slot_Type']))
				$append=true;
			else
				{
					$append=false;
					break;
				}
		}
		if($append==true)
		{
			array_push($NewSuggested, array("Slot_ID"=>$x['Slot_ID'], "Slot_From"=>$x['Slot_From'], "Slot_To"=>$x['Slot_To'], "Slot_Type"=>$x["Slot_Type"], "Course_ID"=>$x["Course_ID"], "Type_ID"=>$x["Type_ID"], "Slot_Day"=>$x["Slot_Day"], "Slot_Group"=>$x["Slot_Group"]));
		}
	}
	return $NewSuggested;		
}

function GenerateSuggested($All_Slots_Original, $GensArray, $SpecialsArray)
{	$All_Slots=$All_Slots_Original;
	unset($Suggested);
	$Suggested=array();
	
	$Suggested_Old="";
	while($Suggested_Old!==$Suggested)
	{
		$Suggested_Old=$Suggested;
		shuffle($All_Slots);
		$Suggested=getSlots($All_Slots, $Suggested);
		$Suggested=CheckLecTut($Suggested, $GensArray, $SpecialsArray);
	}
	return $Suggested;
}

function optimizedSchedule($CourseArr, $DayArr, $n)
//n-> number of random samples
{
	$Original_All_Slots=array();	  //Contains all available timeslots for selected courses
	$SpecialsArray=array();		   //Contains IDs of all courses that must have matching tutorial & lecture groups
	$MyArray=array();				 //Contains n number of randomly generated schedules
	$HighestArray=array();			//Contains all randomly generated schedules with equal highest number of credits
	$GensArray=array();			   //Contains IDs of GEN courses
	$last=0;
	$SelectedCourses="";
	$days="";
	
	shuffle($CourseArr);
	
	foreach($DayArr as $day)
			$days=$days.$day.",";
	$days=substr($days, 0, -1);
	
	foreach($CourseArr as $course)
			$SelectedCourses=$SelectedCourses.$course.",";
	$SelectedCourses=substr($SelectedCourses, 0, -1);
	
	$SQL="
		SELECT T.Slot_ID, T.Slot_From, T.Slot_To, T.Slot_Type, O.Course_ID, C.Type_ID, T.Slot_Day, T.Slot_Group
		FROM Time_Slot T, Offered_In O, Course C
		WHERE T.Slot_Status=1 AND O.Slot_ID=T.Slot_ID AND C.Course_ID=O.Course_ID AND O.Course_ID in(".$SelectedCourses.") AND T.Slot_Day in (".$days.")
		;";	
		
	if(!$query=DB_Manager::Query($SQL))
		return;
	while($row=$query->fetch_assoc())
		array_push($Original_All_Slots, array("Slot_ID"=>$row['Slot_ID'], "Slot_From"=>$row['Slot_From'], "Slot_To"=>$row['Slot_To'], "Slot_Type"=>$row["Slot_Type"], "Course_ID"=>$row["Course_ID"], "Type_ID"=>$row["Type_ID"], "Slot_Day"=>$row["Slot_Day"], "Slot_Group"=>$row["Slot_Group"]));
	
	$query=DB_Manager::Query("SELECT Course_ID FROM Course WHERE Course_Code LIKE 'GENN%'");
	while($row=$query->fetch_assoc())
		array_push($GensArray, $row['Course_ID']);
	
	$query=DB_Manager::Query("SELECT Course_ID FROM Course WHERE Course_Code in ('MTHN%')");
	while($row=$query->fetch_assoc())
		array_push($SpecialsArray, $row['Course_ID']);
			
	for($i=0; $i<$n; $i++)
	{
		unset($All_Slots);
		$All_Slots=$Original_All_Slots;
		shuffle($All_Slots);
		array_push($MyArray, GenerateSuggested($All_Slots, $GensArray, $SpecialsArray));
	}

	
	//Get highest number of credits among generated schedules
	foreach($MyArray as $x)
	{
		$z=getSuggestedCredits($x);
		if($z>$last)
			$last=$z;
	}
	
	
	//Get all schedules having the highest credits
	foreach($MyArray as $x)
	{
		$z=getSuggestedCredits($x);
		if($z==$last)
			array_push($HighestArray, $x);
	}
	
	$last=1000000000;
	foreach($HighestArray as $x)
	{
		$z=CalcScheduleGaps($x);
		if($z<$last)
		{
			$last=$z;
			$FinalArray=$x;
		}
	}
	PrintSchedule($FinalArray);
}

function CalcScheduleGaps($Arr)
{
	$time='00:00:00';
	for($day=0; $day<=5; $day++)
	{
		$min='20:00:00';
		$max='00:00:00';
		foreach($Arr as $x)
		{
			if($x['Slot_Day']==$day)
			{
				if($x['Slot_From']<$min)
					$min=$x['Slot_From'];
				if($x['Slot_To']>$max)
					$max=$x['Slot_To'];
			}	
		}
		sscanf(strtotime($max), "%d:%d:%d", $hours, $minutes, $seconds);
		$max_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
		
		sscanf(strtotime($min), "%d:%d:%d", $hours, $minutes, $seconds);
		$min_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;
		
		$time+=($max_seconds-$min_seconds)/3600;
	}
	return $time;
}

function Prereq($Courses)
{
	$Prerequisites=array();
	
	$query=DB_Manager::Query('SELECT Student_Credits FROM Student WHERE Student_ID='.getID());
	$row=$query->fetch_assoc();
	$Student_Credits=$row['Student_Credits'];
		foreach($Courses as $Course)
		{
			$query=DB_Manager::Query("SELECT Prerequisite_ID as pID, Prerequisite_Hours as pH FROM Prerequisite WHERE Course_ID=".$Course['pID'] );
			while($row=$query->fetch_assoc())
			{
				if($row['pH']>$Student_Credits)
					return -1;
				if($row['pID']!=0)
				 	array_push($Prerequisites, array("pID"=>$row['pID']));
			}
			
		}
		return $Prerequisites;
}

function getPrerequisites($Course_ID)
{			
	$Prerequisites=array();
	$Preq=array(array('pID'=>$Course_ID));
	while($Preq=Prereq($Preq))
	{
		if($Preq==-1)
			return false;
		foreach($Preq as $p)
			array_push($Prerequisites, $p['pID']);
	}
	$Prerequisites=array_unique($Prerequisites);
	return $Prerequisites;
}

function arePassed($Courses)
{
	foreach($Courses as $Course)
	{
		$query=DB_Manager::Query("SELECT Course_ID FROM Grades WHERE Student_ID=".getID()." AND Course_ID=".$Course);
		if(!$query->fetch_assoc())
			return false;		
	}
	
	return true;
}

function printSelectedSuggested()
{
	echo('<table id="Timetable_Table">');
	echo('<tr><th>Course Name</th><th>Course Credits</th><th>Delete</th></tr>');
	$TotalCredits=0;
	foreach($_SESSION['SuggestedSelectedCourses'] as $Course)
	{
		echo('<tr>');
			
				$query=DB_Manager::Query("SELECT Course_Name, Course_Credits FROM Course WHERE Course_ID=".$Course);
				$row=$query->fetch_assoc();
				echo('<td>');
				echo($row['Course_Name']);
				echo('</td>');
				
				echo('<td>');
				echo($row['Course_Credits']);
				echo('</td>');
				$TotalCredits+=$row['Course_Credits'];
			echo("<td><input type='radio' name='RemoveCourse' value='".$Course."' id='login_input'></td>");
			
		echo('</tr>');
	}
	
	echo('</table>');
	echo('<br><div id="Suggested_Div">Selected Courses Credits: '.$TotalCredits.'</div>');	
}


/* ************************************************************************** */
//GPA Transcript Functions
/* ************************************************************************** */

function calc_quality($Grade, $Course_Credits)
{	
	if($Grade=="A+"||$Grade=="A")
	{
		$GPA_WEIGHT="4" ; 
	}
	elseif($Grade=="A-")
	{
		$GPA_WEIGHT="3.7" ; 
	}
	elseif($Grade=="B+")
	{
		$GPA_WEIGHT="3.3" ; 
	}
	elseif($Grade=="B")
	{
		$GPA_WEIGHT="3.0" ; 
	}
	elseif($Grade=="B-")
	{
		$GPA_WEIGHT="2.7" ; 
	}
	elseif($Grade=="C+")
	{
		$GPA_WEIGHT="2.3" ; 
	}
	elseif($Grade=="C")
	{
		$GPA_WEIGHT="2.0" ; 
	}
	elseif($Grade=="C-")
	{
		$GPA_WEIGHT="1.7" ; 
	}
	elseif($Grade=="D+")
	{
		$GPA_WEIGHT="1.3" ; 
	}
	elseif($Grade=="D")
	{
		$GPA_WEIGHT="1.0" ; 
	}
	elseif($Grade=="D-")
	{
		$GPA_WEIGHT="0.7" ; 
	}
	elseif($Grade=="F+")
	{
		$GPA_WEIGHT="0.3" ; 
	}
	elseif($Grade=="F")
	{
		$GPA_WEIGHT="0" ; 
	}

	$Quality_points=$GPA_WEIGHT*$Course_Credits ; 
	
	return $Quality_points ;
} 

/* ************************************************************************** */
//Graduation Report Functions
/* ************************************************************************** */

function Type_Total_Credits($Student_ID, $Type_ID)
{
	$sql_final="
		SELECT Course.Course_ID,Course_Code,Course_Name,Course_Credits,Grade,Type_Name 
		FROM 
		Offered_For JOIN Student ON Student.Program_ID = Offered_For.Program_ID
		JOIN Course ON Offered_For.Course_ID = Course.Course_ID
		JOIN Course_Type ON Course.Type_ID = Course_Type.Type_ID AND Course_Type.Type_ID=".$Type_ID."
		LEFT JOIN Grades ON Student.Student_ID = Grades.Student_ID AND Grades.Course_ID = Course.Course_ID
		WHERE Student.Student_ID = '".$Student_ID."' ORDER BY Course.Type_ID, Grades.Grade";
		
	$query=DB_Manager::Query($sql_final);
	$Total_Credits=0;
	while($row=$query->fetch_assoc())
	{
		$Course_Credits=$row['Course_Credits'];
		$Total_Credits+=$Course_Credits;
	}
	return $Total_Credits;
}

function Type_Actual_Credits($Student_ID, $Type_ID)
{
	$sql_final="
		SELECT Course.Course_ID,Course_Code,Course_Name,Course_Credits,Grade,Type_Name 
		FROM 
		Offered_For JOIN Student ON Student.Program_ID = Offered_For.Program_ID
		JOIN Course ON Offered_For.Course_ID = Course.Course_ID
		JOIN Course_Type ON Course.Type_ID = Course_Type.Type_ID AND Course_Type.Type_ID=".$Type_ID."
		LEFT JOIN Grades ON Student.Student_ID = Grades.Student_ID AND Grades.Course_ID = Course.Course_ID
		WHERE Student.Student_ID = '".$Student_ID."' ORDER BY Course.Type_ID, Grades.Grade";
		
	$query=DB_Manager::Query($sql_final);
	$Total_Credits=0;
	while($row=$query->fetch_assoc())
	{
		$Course_Credits=$row['Course_Credits'];
		if(!empty($row['Grade']))
			$Total_Credits+=$Course_Credits;
	}
	return $Total_Credits;
}

?>
