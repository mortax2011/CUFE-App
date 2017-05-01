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
	DB_Manager::Query("set names utf8");
	
	//Kero's timetable edited
		$sql_final="SELECT SUM(Course_Credits) AS Total_Credits FROM Course WHERE Course_ID in (SELECT DISTINCT C.Course_ID FROM Offered_In O, Course C, Time_Slot T WHERE O.Course_ID=C.Course_ID AND O.Slot_ID=T.Slot_ID AND T.Slot_ID in (".$SelectedSlots."));";
		$run_final=DB_Manager::Query($sql_final);
		$row=$run_final->fetch_assoc();
		$TotalCredits=$row['Total_Credits'];
		
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


function CheckLecTut(&$Array)
{
	$temparray=array();
	foreach($Array as &$x)
	{
		$keep=false;
		foreach($Array as $y)
		{
			if($x["Course_ID"]==$y["Course_ID"] && $x["Slot_ID"]!=$y["Slot_ID"] && $x["Slot_Type"]!=$y["Slot_Type"])
				$keep=true;
		}
		if($x["Type_ID"]==3 || $keep)
				array_push($temparray, array("Slot_ID"=>$x['Slot_ID'], "Slot_From"=>$x['Slot_From'], "Slot_To"=>$x['Slot_To'], "Slot_Type"=>$x["Slot_Type"], "Course_ID"=>$x["Course_ID"], "Type_ID"=>$x["Type_ID"], "Slot_Day"=>$x["Slot_Day"]));
	}
	return $temparray;
}


function GenerateSuggested($CourseArr, $DayArr)
{	
	$SelectedCourses="";
	$days="";
	
	shuffle($CourseArr);
	
	foreach($DayArr as $day)
			$days=$days.$day.",";
	$days=substr($days, 0, -1);
	
	foreach($CourseArr as $course)
			$SelectedCourses=$SelectedCourses.$course.",";
	$SelectedCourses=substr($SelectedCourses, 0, -1);
		
	unset($Suggested);
	unset($All_Slots);
	$Suggested=array();
	$All_Slots=array();
	
	$SQL="
		SELECT T.Slot_ID, T.Slot_From, T.Slot_To, T.Slot_Type, O.Course_ID, C.Type_ID, T.Slot_Day
		FROM Time_Slot T, Offered_In O, Course C
		WHERE O.Slot_ID=T.Slot_ID AND C.Course_ID=O.Course_ID AND O.Course_ID in(".$SelectedCourses.") AND T.Slot_Day in (".$days.")
		;";	
		
	if(!$query=DB_Manager::Query($SQL))
		return;
	while($row=$query->fetch_assoc())
	{
		array_push($All_Slots, array("Slot_ID"=>$row['Slot_ID'], "Slot_From"=>$row['Slot_From'], "Slot_To"=>$row['Slot_To'], "Slot_Type"=>$row["Slot_Type"], "Course_ID"=>$row["Course_ID"], "Type_ID"=>$row["Type_ID"], "Slot_Day"=>$row["Slot_Day"]));
	}		
	
	shuffle($All_Slots);
	
	foreach($All_Slots as $x)
	{	
		$append=true;

		foreach($Suggested as $z)
		{
			if($x["Course_ID"]==$z["Course_ID"] && 	$x["Slot_Type"]==$z["Slot_Type"])
			{
				$append=false;
				break;
			}

			$append=true;
		}
				
		foreach($Suggested as $y)
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
			array_push($Suggested, array("Slot_ID"=>$x['Slot_ID'], "Slot_From"=>$x['Slot_From'], "Slot_To"=>$x['Slot_To'], "Slot_Type"=>$x["Slot_Type"], "Course_ID"=>$x["Course_ID"], "Type_ID"=>$x["Type_ID"], "Slot_Day"=>$x["Slot_Day"]));
		}
	}		

	$Suggested=CheckLecTut($Suggested);
	PrintSchedule($Suggested);
	
}

function Prereq($Courses)
{
	$Prerequisites=array();
	
	
		foreach($Courses as $Course)
		{
			$query=DB_Manager::Query("SELECT Prerequisite_ID as pID FROM Prerequisite WHERE Course_ID=".$Course['pID']);
			
			while($row=$query->fetch_assoc())
				 array_push($Prerequisites, array("pID"=>$row['pID']));
			
		}
		return $Prerequisites;
}

function getPrerequisites($Course_ID)
{			
	$Prerequisites=array();
	$Preq=array(array('pID'=>$Course_ID));
	while($Preq=Prereq($Preq))
	{
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
?>
