<?php
// Include files, including the database connection
include('includes/dbConfig.php');
include('includes/functions.php');

//--------------------------------------------------------------------------------
// COMMENTS FOR USE OF THIS FILE (minimum ChurchRota version 2.4.2)
//--------------------------------------------------------------------------------
// DESCRIPTION
//--------------------------------------------------------------------------------
// This file is intended to be run on a daily base (best via cron job).
// For its functionality you need the following ChurchRota settings 
//   + TOKEN (empty default value; 'rotatoken' for EXAMPLES in THIS file)  
//   + DAYS_TO_ALERT (default: 5; 0 for disable)  
//
// This file can be started e.g. via browser by passing the given TOKEN via URL:
//   http://<your-domain>/<churchrota-path>/cr_daily.php?TOKEN=<your-token>
//   e.g.
//   http://yourdomain.com/churchrota/cr_daily.php?TOKEN=rotatoken
//
// It checks if there are any events scheduled in given days from today 
// ('days_to_alert'). If so, an email will be sent to everyone involved in the 
// resulting events. For sending emails, the same functionality is used as 
// pressing the correspondent button in the web interface.
//
// TOKEN:
// All other files in ChurchRota are checked for login session, except this 
// file. So, theoretically someone (lets call him SPOOFER) might get to know 
// the URL and call this file - lets say 'accidentally'.
// Therefore the TOKEN exists, it ensures that when simply calling this file 
// (without the token) nothing will happen. So the TOKEN gives you a minimum 
// security, preventing unwanted emails reminders to be sent.
// Therefore, no default value is given for the TOKEN, you need to explicitly 
// set it to a unguessable value.
//
//--------------------------------------------------------------------------------
// WGET
//--------------------------------------------------------------------------------
// Of course, you do not want to manually open a browser and call this file.
// So, the idea is to automatically call it (with TOKEN parameter) on a 
// daily base (see CRON JOB for this). It could be done e.g. via shell 'wget' 
//   WGET http://yourdomain.com/churchrota/cr_daily.php?TOKEN=rotatoken
//
// There are also wget ports for Windows systems, but I have tried none of them
//   e.g. GnuWin -> https://sourceforge.net/projects/gnuwin32/files/wget/   
//
// Functionality does not need any output files from wget, the mechanism 
// is only used to call the URL. But you can use wget paremeters for 
// monitoring purposes. Relevant wget parameters are
//   --output-document=<churchrota-installation-path>/output.html
//   --output-file=<><churchrota-installation-path>/output.log
// see documantation of wget for more details.
//
// If you do not want to monitor output you can use the dummy file /dev/null.
// An example is given in the shell script: 
//   + cr_cron.sh
// Before using, you need to adjust ChurchRota installation paths in that file
//
//--------------------------------------------------------------------------------
// CRON JOB
//--------------------------------------------------------------------------------
// For setting up a cron job you need to add a cron entry like this,  
// to run 'cr_daily.php' daily e.g at 5 after midnight: 
//     bash# crontab -e
//     5 0 * * * cd <your-churchrota-directory> ; ./cr_cron.sh
//
// For more details see your webserver's or provider's documentation on howto
// configure CRON jobs - or search the web.
//--------------------------------------------------------------------------------


// Start the session. This checks whether someone is logged in and if not redirects them
//session_start();

	$sqlSettings = "SELECT * FROM cr_settings";
	$resultSettings = mysql_query($sqlSettings) or die(mysql_error());
	$rowSettings = mysql_fetch_array($resultSettings, MYSQL_ASSOC);
	$daysAlert = $rowSettings["days_to_alert"]; //  0 => disable automatic notifications
	$token = $rowSettings["token"];
	
	if ((isset($_GET['TOKEN'])) && ($_GET['TOKEN']==$token)) {
		// Just continue the code
		
		$out = "";
		if ($daysAlert > 0)
		{
			$sqlEvents = "SELECT id,date FROM cr_events where notified=0 and date_format(date,\"%y-%m-%d\")=date_format(DATE_ADD(now(), INTERVAL ".$daysAlert." DAY),\"%y-%m-%d\")";
			$resultEvents = mysql_query($sqlEvents) or die(mysql_error());
			$i = 0;
			while($rowEvents = mysql_fetch_array($resultEvents, MYSQL_ASSOC)) {
				notifyEveryone($rowEvents["id"]);
				$out = $out . "...Automatic notifications (everyone) for event ".$rowEvents["id"]." on ".$rowEvents["date"].".<br>\r\n";
				$i=$i+1;
			}
			if ($i==0) 
				$out = $out . "No events found to automatically notify for.";
		}
		else
		{
			$out = $out . "Automatic notifications are disabled.";
		}
		
		echo "<HTML><BODY>\r\n";
		echo "ChurchRota " . date("Y-m-d H:i:s") . "<br>\r\n";
		echo $out;
		echo "</BODY></HTML>\r\n";
	}
	else
	{
		//redirect to start page
		header ( "Location: index.php");
    }
?>