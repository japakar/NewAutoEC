<?php
// Garbage Collection Script, Cleans up old Shortlink Sessions and userdata to keep the script snappy
// also cleans up limit logdata older than 7 days. note that this can be called by both the admin or a cronjob.
// if called by a cronjob, the SECKEY has to be supplied by adding a GET parameter called SecurityKey

/* ==============================================================================================
 * """"" SCRIPT VALIDATION & BEHAVIOUR IN CASE OF MISSING/INCOMPLETE CONFIG                 =====
 * ==============================================================================================
*/
// replace backslashes with slashes, Making the behaviour identical on Windows & Linux Systems
ob_start(); // start output buffering => this allows us to keep the behavior of the script identical on all systems.
define("DRSR", $_SERVER['DOCUMENT_ROOT']);
define("FDSR", str_replace("\\","/",dirname(__FILE__)));
// Determine if the script is inside a subfolder, if it is, isolate the subdirectory from the root path and store it in a Constant
// this allows links to be relative to the script, even if it is moved.
if(dirname(DRSR) === dirname(FDSR)){
	define("DIRREC", "");
}else{
	define("DIRREC", str_replace(DRSR,"",FDSR));
}
//allow the script to include files restricted to normal users:
define("SFR_INC_0_LKEY", true);
//set the session-related settings to avoid other scripts from messing with these sessions
if (!file_exists(session_save_path().DIRREC.DIRECTORY_SEPARATOR.'Autofaucet')){
	mkdir(session_save_path().DIRREC.DIRECTORY_SEPARATOR.'Autofaucet', 0777, true);
}
    session_save_path(session_save_path().DIRREC.DIRECTORY_SEPARATOR.'Autofaucet');
ini_set('session.gc_probability', 1);
ini_set('session.gc_maxlifetime', 48*60*60);
//start the session to save/access variables
session_start();

//Load Config, convert it to a parsable PHP array:
(include FDSR.DIRECTORY_SEPARATOR."config.php") OR die("Something went wrong while trying to load the config file! please make sure your Config file is in the scripts main folder, please also check if PHP has rights to include/require files! (your hosting provider may assist you with this!)");
//Load Functions, if it fails give out an error and end the script.
(include_once FDSR.DIRECTORY_SEPARATOR."functions.php") OR die("Something went wrong while trying to load the functions file! please make sure your functions file is in the scripts main folder, please also check if PHP has rights to include/require files! (your hosting provider may assist you with this!)");
$Config = json_decode($Config, true);

if(empty($Config)){ // check if the config exists and is ready to be used. if its not useable,return to Index
	redirect(DIRREC."/"); // script aint usuable so the user cant verify, back to index it is m8
}
if(@$Config['Useable'] !== true){ // check if the script is completely set up, and ready to handle users
	redirect(DIRREC."/"); // script aint usuable so the user cant verify, back to index it is m8
}

function EndGC(){
	if(@$_SESSION['ADM_IS_LOGGED_IN'] === true){
		$_SESSION['SuccessMSG'] =  "Succesfully cleaned up Script data!";
		if(extension_loaded('sqlite3')){
			redirect(DIRREC."/admin.php");
		}else{
			redirect(DIRREC."/admin.php",302,1);
		}
	}elseif(isset($_GET['SecurityKey'])){
	}else{
		exit;
	}
}

// check if the user is authorized by being logged in or by having a valid securityKEy
if(@$_SESSION['ADM_IS_LOGGED_IN'] !== true && !isset($_GET['SecurityKey'])){
	echo "not logged in! visit the admin page to log in";
	exit;
}
if(@$_SESSION['ADM_IS_LOGGED_IN'] === true){
}elseif(isset($_GET['SecurityKey'])){
	if($_GET['SecurityKey'] !== SECKEY){
		echo "not logged in! visit the admin page to log in";
		exit;
	}
}else{
	$_SESSION['ErrMSG'] =  "You Need to be Logged in!";
	redirect(DIRREC."/admin.php");
}
//check if the script uses SQLite3
define("START", microtime(true)); // track when the script started processing data
if(extension_loaded('sqlite3')){

	// shortlink clearing:
	$SPDO = new PDO('sqlite:'.FDSR.DIRECTORY_SEPARATOR.'Data'.DIRECTORY_SEPARATOR.'ShortlinkData.sqlite3');
	$SPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$SPDO->exec("CREATE TABLE IF NOT EXISTS ShortlinkData (
		IP TEXT NOT NULL,
		ShortUID INTEGER NOT NULL,
		Success TINYINT NOT NULL,
		timestamp INTEGER NOT NULL,
		UUID TEXT NOT NULL
	)");
	$query = $SPDO->prepare("DELETE FROM ShortlinkData WHERE timestamp < ?");
	$query->execute([time() - 3600*24]);


	foreach($Currencies as $cur){
		$UPDO = new PDO('sqlite:'.FDSR.DIRECTORY_SEPARATOR.'Data'.DIRECTORY_SEPARATOR.$cur.'Data.sqlite3');
		$UPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$UPDO->exec("CREATE TABLE IF NOT EXISTS ".$cur."Data (
			Address TEXT NOT NULL,
			Tokens INTEGER NOT NULL,
			SuccessfulWithdraws INTEGER NOT NULL,
			ClaimStarted INTEGER NOT NULL,
			TimesUsed INTEGER NOT NULL,
			Referrer TEXT,
			ConfigID INTEGER NOT NULL,
			UNIQUE(Address)
		)");
		$query = $UPDO->prepare("DELETE IGNORE FROM ".$cur."Data WHERE ClaimStarted < ?");
		$query->execute([time() - 3600*24*7]);
	}





}else{ // the script does not use SQLite3 so we hanlde the fallback json data
	$ShortData = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."Shortlinkdata.json");
	$ShortData = @json_decode($ShortData, true);
	if($ShortData === null){
		// there's no previous shortlink data, so we skip this entirely
	}else{
		$ShortData2 = $ShortData;
		foreach($ShortData2 as $IP => $row){
				foreach($row as $Key => $options){
					if($options['timestamp'] < time()- 24*60*60){
						unset($ShortData[$IP][$Key]);
					}
				}
			if(empty($ShortData[$IP])){
				unset($ShortData[$IP]);
			}
		}
		file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."Shortlinkdata.json",json_encode($ShortData)); // complete editing the dataset and save it
		unset($ShortData);
		unset($ShortData2);
	}
	if(START < (microtime(true) - 25)){EndGC();}
	$Currencies = array_keys($Config['Currencies']);
	foreach($Currencies as $cur){
		$CurrData = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR.$cur."data.json");
		$CurrData = @json_decode($CurrData, true);
		if($CurrData === null){
			// there's no previous shortlink data, so we skip this entirely
		}else{
			$CurrData2 = $CurrData;
			foreach($CurrData2 as $addr => $optioms){
				if($options['ClaimStarted'] < time() - 3600*24*7){
					unset($CurrData[$addr]);
				}
			}
			@file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR.$cur."data.json",json_encode($CurrData)); // complete editing the dataset and save it
			unset($CurrData);
			unset($CurrData2);
		}
		if(START < (microtime(true) - 25)){EndGC();}
	}
}
if(START < (microtime(true) - 25)){EndGC();} // check if the script execution is longer than 25 seconds and abandon the session if true

$APIData = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."APIlimit.json");
$APIData = @json_decode($APIData, true);
if($APIData === null){
	// there's no previous shortlink data, so we skip this entirely
}else{
	for($i = 0;$i <= 7; $i++){
		$date = date("d-m", time() - (3600*24*$i));
		$APIlimit[$date] = $LimitLog[$date];
	}
	@file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."APIlimit.json",json_encode($ApiLimit)); // complete editing the dataset and save it
	unset($LimitLog);
	unset($APIlimit);
	unset($date);
}


EndGC();
ob_end_flush(); // flush the output buffer and send user the page - solves issues with a few hosters.


 ?>
