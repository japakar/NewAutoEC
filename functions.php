<?php
/* This is the script's main function file. this has helper functions aswell as functions to make script customization alot more easier
 * this also means that this function file is CRITICAL for the script, please make sure to not delete any functions or code unless you are aware of the results.
 *
*/


//technically not a function, but it serves an important function to be used later in the script;
$messages = "";

function redirect($Path, $Statuscode = 302, $wait = 0){ // litterally just a helper function because i'm lazy
	if($wait !== 0){
		header("refresh: ".$wait."; url=".$Path);
		include FDSR.DIRECTORY_SEPARATOR."/header.php"; // include the standard heade
		echo '<div class="row justify-content-center container-fluid FirstLayer">
			<div class="col-12 col-md"></div>
			<div class="card col-12 col-md-6 SecondLayer">
				<h2>redirecting you in '.$wait.' Seconds</h2>
			</div>
			<div class="col-12 col-md"></div>
		</div>';
		include FDSR.DIRECTORY_SEPARATOR."/footer.php";
		exit;
	}
	header("location: ".$Path, TRUE, $Statuscode);
	exit;
}
function RandomString($length = 6) { // pretty much the function that uphold's the entire script security. if you wish to replace it. remember that the script expects a string containing only A-Z, a-z & 0-9
	$str = "";
	$characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
	$max = count($characters) - 1;
	for ($i = 0; $i < $length; $i++) {
		$rand = mt_rand(0, $max);
		$str .= $characters[$rand];
	}
	return $str;
}
function ShortenerName($url){ // helpful function to return a shorteners domain name based on the API url supplied.
	$UrlArray = parse_url($url); // slice url into an array
	if(isset($UrlArray['host']) && isset($UrlArray['path'])){ // check if host and path are set
		return $UrlArray['host'].str_replace( "/api","",$UrlArray['path']); // return the name
	}
	//fallback if the parse_url function somehow failed
    $sub = substr($url, strpos($url,"://")+strlen("://"),strlen($url)); // may fail if the shortener doesnt use a the default shortener script.
    return substr($sub,0,strpos($sub,"/api")); // this is voodoo magic to me even though i wrote it. dont expect me to comment this properly lol
}
function CharCheck($string){// check if the string has any bad characters.
    if (!preg_match("#^[a-zA-Z0-9-_.]+$#", $string)) { return false;} // do soem regex magic.
    return true;
}
function DestroySession($Coin){ // Convenience function Designed to be added by anyone - makes script customization easier.
	unset($_SESSION[$Coin.'address']); // unset address for that coin
	unset($_SESSION[$Coin.'claimtime']); // unset address for that coin
	unset($_SESSION[$Coin.'SecToken']); // unset SecToken for that coin we dont use session_destroy() because other coins may still be running
	setcookie($Coin."Token", "Rip Dis Session Dead", time()-10000); // remove Cookie
	redirect(DIRREC."/");
}

function ErrorMSG($icon = "", $text = ""){ // Convenience function
    $result = '<div class="alert alert-danger"><i style="font-size:25px;" class="material-icons">'.$icon.'</i> <span>'.$text.'</span> </div>';
    return $result;
}
function WarningMSG($icon = "", $text = ""){// Convenience function
    $result = '<div class="alert alert-warning"><i style="font-size:25px;" class="material-icons">'.$icon.'</i> <span>'.$text.'</span> </div>';
    return $result;
}
function infoMSG($icon = "", $text = ""){// Convenience function
    $result = '<div class="alert alert-primary"><i style="font-size:25px;" class="material-icons">'.$icon.'</i> <span>'.$text.'</span> </div>';
    return $result;
}
function SuccessMSG($icon = "", $text = ""){// Convenience function
  $result = '<div class="alert alert-success"><i style="font-size:25px;" class="material-icons">'.$icon.'</i> <span>'.$text.'</span> </div>';
  return $result;
}
function contains($needle, $haystack){// Convenience function
    return strpos($haystack, $needle) !== false;
}
function UpdateConfig($Config = array(), $SecurityKey = SECKEY){ // the main function to preserve most of the file's layout and security
	if(empty($Config)){
		return false;
	}
	$TextFile = "<?php
	/* this config file is a special file that stores all your settings of the script. by default it looks empty and only has 1 option you can set.
	 * the script will be configured via a complete admin panel located at (your domain)/admin.php or (your domain)/(folder)/admin.php if the script is in a folder
	*/

	//secure this file by checking if the user tried to acess it durectly
	if(!defined(".'"'."SFR_INC_0_LKEY".'"'.")){
		http_response_code(404);
		exit;
	}


	// The Security Key: this is essential for the script's security. this key is needed to set your admin credentials or to retrieve them incase you forget them
	// this key is also needed incase you wish to reset the script via the admin panel.
	// set the key to whatever you like. Example:
	// define(".'"'."SECKEY".'"'.", ".'"'."ExampleKey".'"'.");
	define(".'"'."SECKEY".'"'.", ".'"'.$SecurityKey.'"'.");

	// below this part is the config, this shouldnt be touched by humans because the script might get a Panic Attack if the values are any different from what it expects.
	// there is no reason to change anything by hand. if you're feeling risky atleast make a backup before you ruin everything and dissapoint your family.
	// dont say i didnt warn you!
	".'$Config'." = <<<CONFIG
".json_encode($Config)."
CONFIG;

?>
";
$FPCret = file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."config.php", $TextFile);
if($FPCret === false){
	echo "Something went wrong while trying to Save the config file! please check if PHP has rights to include/require files and if fopen is enabled! (your hosting provider may assist you with this!)";
	exit;
}
return $FPCret; // pzt tgat stzff ubti the file
}
function intifyArray($val){ // sets the correct types for values in a $_POST or $_GET array. even though the name says int, it also handles floats
	return is_numeric($val) ? $val*1 : $val;
}
function r_array_map($callback, $data){ // recursive array_map function.
	$output= Array(); // prepare the output array.
	foreach ($data as $key => $adata) { //iterate the array.
		if (is_array($adata)) { // check if the value is an array
			$output[$key] = r_array_map($callback, $adata); // recursify the crap out of it
		} else {
			$output[$key] = $callback($adata); //otherwhise just use the callback function on the value.
		}
	}
	return $output;
}

function CheckCurrencies($ApiKey, $UserToken, $Currency){ // wrapper function only used in the admin panel, still making life easier though
	include_once FDSR.DIRECTORY_SEPARATOR."ExpressCrypto.php"; // include EC class
	$EC = New ExpressCryptoV2($ApiKey,$UserToken); // Load the Faucethub library and specify the currency (doesnt matter which we sepcify)
	$response = $EC->getBalance($Currency); // do the call vie the library.
	if($response['status'] === 200){ // check if it worked.
		return array("success" => true, "currency" => $Currency, "data" => $response);
	}else{ // if it did, great. if it didnt, crap. set the success value accordingly and send it back
		return array("success" => false, "currency" => $Currency, "data" => $response);
	}
}
function CheckCaptcha($Url, $params, $method = "POST"){ // Convenience function to Allow the script to work with both Curl and File_get_contents
	$cURLsuccess = false;
	if(function_exists("curl_init")){ // if curl is supported, use curl to make the call
		$ch = curl_init($Url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$response = curl_exec($ch);
		$r = json_decode($response, true);
		if($r !== null){
			if($r['success'] == true){
				return true;
			}elseif($r['success'] !== true){
				return false;
			}
		}
	}
	if(!$cURlsuccess){ //otherwhise, if curl failed or isnt supported, we use fopen
		$opts = array(
			"http" => array(
				"method" => "POST",
				"header" => "Content-type: application/x-www-form-urlencoded\r\n",
				"content" => http_build_query($params),
				"timeout" => 30,
			),
        );
		$ctx = stream_context_create($opts);
        $fp = fopen($Url , false, $ctx);

        if (!$fp) { // panic attack if the script doesnt work with any of the 2.
            die("FATAL ERROR: Script Unable to fetch Results from Captcha! please make sure that the script can use fOpen or has cURL installed! you may also disabled Captchas until then");
        }

        $response = stream_get_contents($fp);
		$r = json_decode($response, true);
		if($r !== null){
			if($r['success'] == true){
				fclose($fp);
				return true;
			}elseif($r['success'] !== true){
				fclose($fp);
				return false;
			}
		}
        fclose($fp); // panic attack because fopen failed anyways.
		die("FATAL ERROR: Script Unable to fetch Results from Captcha! please make sure that the script can use fOpen or has cURL installed! you may also disabled Captchas until then");
	}
}
function ProcessPayout($amount, $to,$currency,$Apikey,$UserToken, $referrer = null, $Commission = 0){ //Convenience Wrapper Function.
	include_once FDSR.DIRECTORY_SEPARATOR."ExpressCrypto.php"; // include EC class
	$EC = New ExpressCryptoV2($Apikey,$UserToken); // Load the Faucethub library
	$r = $EC->sendPayment($to,$currency, $amount); // Send the Payout Request to EC: Result will be processed later.
	if($referrer !== null){ // check if we need to send a referral payout
		$Reffamount = floor($amount * ($Commission/100)); // get that ammount
		if($Reffamount !== 0){ // if its 0 we dont need to send a payout.
			$r['ValidReferral'] = true; // asume the Referral is valid until proven otherwhise.
			$ReffR = $EC->sendReferralCommission($referrer,$currency,$Reffamount); // send the Referral payout and process it below.
			if(@$ReffR['status'] === 429){ // API limit reached, log this to let the admin know
				$Date = date("d-m"); //get the day and month
				$LimitLog = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."APIlimit.json");
				$LimitLog = @json_decode($LimitLog, true);
				if($LimitLog === null){ // open the limit log and turn it into a parsable array.
					$LimitLog = array();
				}
				if(isset($LimitLog[$Date])){ // increase the limitsReached counter, set the time of LastReached to the current time, and if true, set the Credits empty value to true.
					$LimitLog[$Date] = array("LimitReached" => $LimitLog[$Date]['LimitReached'] + 1, "LastReached" => date("H:i:s"), "CreditsEmpty" => false, "CreditsEmptyTime" => date("H:i:s"));
				}else{
					$LimitLog[$Date] = array("LimitReached" => 1, "LastReached" => date("H:i:s"), "CreditsEmpty" => false, "CreditsEmptyTime" => date("H:i:s"));
				}
				file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."APIlimit.json",json_encode($LimitLog)); // complete editing the dataset and save it
				setcookie("CreditsEmpty",true,time()+3540); //disable processpayout for 59 minutes
			}elseif(@$ReffR['status'] === 404){
				$r['ValidReferral'] = false;
			}
		}
	}
	if(@$r['status'] === 200){ // process the main payout response
		return $r; // payout success
	}elseif(@$r['status'] === 429){ // API limit reached, log this to let the admin know
		$Date = date("d-m"); //get the day and month
		$LimitLog = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."APIlimit.json");
		$LimitLog = @json_decode($LimitLog, true);
		if($LimitLog === null){ // open the limit log and turn it into a parsable array.
			$LimitLog = array();
		}
		if(isset($LimitLog[$Date])){ // increase the limitsReached counter, set the time of LastReached to the current time, and if true, set the Credits empty value to true.
			$LimitLog[$Date] = array("LimitReached" => $LimitLog[$Date]['LimitReached'] + 1, "LastReached" => date("H:i:s"), "CreditsEmpty" => false, "CreditsEmptyTime" => date("H:i:s"));
		}else{
			$LimitLog[$Date] = array("LimitReached" => 1, "LastReached" => date("H:i:s"), "CreditsEmpty" => false, "CreditsEmptyTime" => date("H:i:s"));
		}
		file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."APIlimit.json",json_encode($LimitLog)); // complete editing the dataset and save it
		setcookie("CreditsEmpty",true,time()+3540); //disable processpayout for 59 minutes
		return $r;
	}elseif(isset($r['status'])){
			return $r;
	}else{
		return array("status" => 911, "success" => false, "message" => "Couldnt connect to ExpressCrypto. your payout will be sent next payout cycle");
	}
}
function file_put_contents_atom($filename, $data, $flags = 0, $context = null) {
	if (!is_dir(dirname($filename))) {
		echo "couldnt write to directory: ".dirname($filename)."<br>";
		echo "Invalid Directory, make sure your script has a 'Data' Directory";
		exit;
	}
	if (!is_writable(dirname($filename))) {
		echo "Directory is not writeable";
		exit;
	}
    if (file_put_contents($filename."~", $data, $flags, $context) === strlen($data)) {
		if(rename($filename."~",$filename)){
			return true;
		}else{
			system("mv ".$filename."~ ".$filename, $successss);
			if($successss !== 0){
				echo "Could not Save Userdata! please make sure that the script has write permissions!";
				exit;
			}else{
			return true;
			}
		}
    }

	echo "Unable to save file. please try again";
    @unlink($filename."~", $context);
	exit;
}
// the functions below are all widely the same but for different purposes.
// therefore the Commenting on them is mostly obsolete.
function CreateCoinTable($Coin){
	global $UPDO;

	$UPDO = new PDO('sqlite:'.FDSR.DIRECTORY_SEPARATOR.'Data'.DIRECTORY_SEPARATOR.$Coin.'Data.sqlite3');
	$UPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$UPDO->exec("CREATE TABLE IF NOT EXISTS ".$Coin."Data (
		Address TEXT NOT NULL,
		Tokens INTEGER NOT NULL,
		SuccessfulWithdraws INTEGER NOT NULL,
		ClaimStarted INTEGER NOT NULL,
		TimesUsed INTEGER NOT NULL,
		Referrer TEXT,
		ConfigID INTEGER NOT NULL,
		UNIQUE(Address)
	)");
}
function CreateShortlinkTable(){
	global $SPDO;
	$SPDO = new PDO('sqlite:'.FDSR.DIRECTORY_SEPARATOR.'Data'.DIRECTORY_SEPARATOR.'ShortlinkData.sqlite3');
	$SPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$SPDO->exec("CREATE TABLE IF NOT EXISTS ShortlinkData (
		IP TEXT NOT NULL,
		ShortUID INTEGER NOT NULL,
		Success TINYINT NOT NULL,
		timestamp INTEGER NOT NULL,
		UUID TEXT NOT NULL
	)");
}
function GetUserData($Coin, $Address){
	global $UPDO;
	if(extension_loaded('sqlite3')){
		if(!isset($UPDO)){
			if(!CharCheck($Coin)){
				return null;
			}
			CreateCoinTable($Coin);
		}
		$query = $UPDO->prepare("SELECT * FROM ".$Coin."Data WHERE Address = ?");
		$query->execute([$Address]);
		$result = $query->fetch(PDO::FETCH_ASSOC);

		if(empty($result)){
			return null;
		}
		return $result;
	}else{
		$UserData = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR.$Coin."data.json");
		$UserData = @json_decode($UserData, true);
		if($UserData !== null){
			if(isset($UserData[$Address])){
				$UserData[$Address]['Address'] = $Address;
				return $UserData[$Address];
			}
			return null;
		}
		return null;
	}
}
function SaveUserData($Coin, $Data){
	global $UPDO;
	if(extension_loaded('sqlite3')){
		if(!isset($UPDO)){
			if(!CharCheck($Coin)){
				return array();
			}
			CreateCoinTable($Coin);
		}
		if($Data['isNewUser'] == true){
			$query = $UPDO->prepare("INSERT INTO ".$Coin."Data (Address,Tokens,SuccessfulWithdraws,ClaimStarted,TimesUsed,Referrer,ConfigID) VALUES(:Address,:Tokens , :SuccessfulWithdraws , :ClaimStarted , :TimesUsed , :Referrer , :ConfigID)");
		}else{
			$query = $UPDO->prepare("UPDATE ".$Coin."Data SET Tokens = :Tokens , SuccessfulWithdraws = :SuccessfulWithdraws , ClaimStarted = :ClaimStarted , TimesUsed = :TimesUsed , Referrer = :Referrer , ConfigID = :ConfigID WHERE Address = :Address");
		}
		$QueryValues = array(':Address' => $Data['Address'],
		':Tokens' => $Data['Tokens'],
		':SuccessfulWithdraws' => $Data['SuccessfulWithdraws'],
		':ClaimStarted' => $Data['ClaimStarted'],
		':TimesUsed' => $Data['TimesUsed'],
		':Referrer' => $Data['Referrer'],
		':ConfigID' => $Data['ConfigID']
	);
		$result = $query->execute($QueryValues);
	}else{
		$UserData = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR.$Coin."data.json");
		$UserData = @json_decode($UserData, true);
		if($UserData !== null){
			$UserData[$Data['Address']] = $Data;
			unset($UserData[$Data['Address']]['Address']);
			@file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR.$Coin."data.json",json_encode($UserData)); // complete editing the dataset and save it
		}else{
			$UserData = array();
			$UserData[$Data['Address']] = $Data;
			unset($UserData[$Data['Address']]['Address']);
			@file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR.$Coin."data.json",json_encode($UserData)); // complete editing the dataset and save it
		}
	}
}
function DeleteUserData($Coin, $Address){
	global $UPDO;
	if(extension_loaded('sqlite3')){
		if(!isset($UPDO)){
			if(!CharCheck($Coin)){
				return array();
			}
			CreateCoinTable($Coin);
		}
		$query = $UPDO->prepare("DELETE FROM ".$Coin."Data WHERE Address = ?");
		$query->execute([$Address]);
	}else{
		$UserData = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR.$Coin."data.json");
		$UserData = @json_decode($UserData, true);
		if($UserData !== null){
			unset($UserData[$Address]);
			@file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR.$Coin."data.json",json_encode($UserData)); // complete editing the dataset and save it
		}
	}
}
function GetShortlinkSessions($IP){
	global $SPDO;
	if(extension_loaded('sqlite3')){
		if(!isset($SPDO)){
			CreateShortlinkTable();
		}
		$query = $SPDO->prepare("SELECT * FROM ShortlinkData WHERE IP = ?");
		$query->execute([$IP]);
		$result = array();
		while($row = $query->fetch(PDO::FETCH_ASSOC)){
			$result[] = $row;
		}
		$DeleteQuery = $SPDO->prepare("DELETE FROM ShortlinkData WHERE UUID = ?");
		$returnArray = array();
		if(empty($result)){
			return array();
		}
		foreach($result as $row){
			if($row['timestamp'] < time()- 24*60*60 || $row['Success'] != 1){
				$DeleteQuery->execute([$row['UUID']]);
				continue;
			}
			$returnArray[] = $row;
		}
		return $returnArray;
	}else{
		$ShortlinkData = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."Shortlinkdata.json");
		$ShortlinkData = @json_decode($ShortlinkData, true);
		if($ShortlinkData !== null){
			if(isset($ShortlinkData[$IP])){
				$returnArray = array();
				foreach($ShortlinkData[$IP] as $row){
					if($row['timestamp'] < time()- 24*60*60 || $row['Success'] !== 1){
						continue;
					}
					$returnArray[] = $row;
				}
				@file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."Shortlinkdata.json",json_encode($ShortlinkData));
				return $returnArray;
			}
			return array();
		}
	}
}
function WipeShortlinkSessions($IP){
	global $SPDO;
	if(extension_loaded('sqlite3')){
		if(!isset($SPDO)){
			CreateShortlinkTable();
		}
		$query = $SPDO->prepare("DELETE FROM ShortlinkData WHERE IP = ?");
		$query->execute([$IP]);
	}else{
		$ShortlinkData = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."Shortlinkdata.json");
		$ShortlinkData = @json_decode($ShortlinkData, true);
		if($ShortlinkData !== null){
			if(isset($ShortlinkData[$IP])){
				unlink($ShortlinkData[$IP]);
				@file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."Shortlinkdata.json",json_encode($ShortlinkData));
			}
		}
	}
}
function CreateShortlinkSessions($IP, $ShortUID){
	global $SPDO;
	if(extension_loaded('sqlite3')){
		if(!isset($SPDO)){
			CreateShortlinkTable();
		}
		$UUID = RandomString(24);
		$query = $SPDO->prepare("INSERT INTO ShortlinkData (IP,ShortUID,Success,timestamp,UUID) VALUES(?,?,?,?,?)");
		$QueryValues = array($IP,$ShortUID,0,time(),$UUID);
		$result = $query->execute($QueryValues);
		return $UUID;
	}else{
		$ShortlinkData = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."Shortlinkdata.json");
		$ShortlinkData = @json_decode($ShortlinkData, true);
		$UUID = RandomString(24);
		if($ShortlinkData !== null){
			if(isset($ShortlinkData[$IP])){
				$ShortlinkData[$IP][] = array("IP" => $IP, "ShortUID" => $ShortUID, "Success" => 0, "timestamp" => time(), "UUID" => $UUID);
			}else{
				$ShortlinkData[$IP] = array();
				$ShortlinkData[$IP][] = array("IP" => $IP, "ShortUID" => $ShortUID, "Success" => 0, "timestamp" => time(), "UUID" => $UUID);
			}
		}else{
			$ShortlinkData = array();
			$ShortlinkData[$IP] = array();
			$ShortlinkData[$IP][] = array("IP" => $IP, "ShortUID" => $ShortUID, "Success" => 0, "timestamp" => time(), "UUID" => $UUID);
		}
		@file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."Shortlinkdata.json",json_encode($ShortlinkData));
		return $UUID;
	}
}

function UpdateShortlinkSessions($IP, $UUID){
	global $SPDO;
	if(extension_loaded('sqlite3')){
		if(!isset($SPDO)){
			CreateShortlinkTable();
		}
		$query = $SPDO->prepare("UPDATE ShortlinkData SET Success = 1 WHERE UUID = ?");

		$QueryValues = array($UUID);
		$result = $query->execute($QueryValues);
		if($query->rowCount() === 0){return false;}
		return true;
	}else{
		$ShortlinkData = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."Shortlinkdata.json");
		$ShortlinkData = @json_decode($ShortlinkData, true);
		if($ShortlinkData !== null){
			if(isset($ShortlinkData[$IP])){
				$ArrayID = array_search($UUID, array_column($ShortlinkData[$IP], 'UUID'));
				if($ArrayID === false){
					echo "not Found";
					exit;
				}
				$ShortlinkData[$IP][$ArrayID]['Success'] = 1;
				file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."Shortlinkdata.json",json_encode($ShortlinkData));
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}

	}
}
function CheckIPlog($IP){
	global $IPDO;
	if(extension_loaded('sqlite3')){
		if(!isset($IPDO)){
			$IPDO = new PDO('sqlite:'.FDSR.DIRECTORY_SEPARATOR.'Data'.DIRECTORY_SEPARATOR.'IPlog.sqlite3');
			$IPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$IPDO->exec("CREATE TABLE IF NOT EXISTS IPlog (
				IP TEXT NOT NULL,
				Result INTEGER NOT NULL,
				Country TINYINT NOT NULL,
				timestamp INTEGER NOT NULL
			)");
		}
		$query = $IPDO->prepare("SELECT * FROM IPlog WHERE IP = ?");
		$query->execute([$IP]);
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if(empty($result)){return array();}
		return $result;
	}else{
		$IPlogData = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."IPlog.json");
		$IPlogData = @json_decode($IPlogData, true);
		if($IPlogData !== null){
			if(isset($IPlogData[$IP])){
				return $IPlogData[$IP];
			}else{
				return array();
			}
		}else{
			return array();
		}
	}
}
function SetIPlog($IP,$Result,$Country){
	global $IPDO;
	if(extension_loaded('sqlite3')){
		if(!isset($IPDO)){
			$IPDO = new PDO('sqlite:'.FDSR.DIRECTORY_SEPARATOR.'Data'.DIRECTORY_SEPARATOR.'IPlog.sqlite3');
			$IPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$IPDO->exec("CREATE TABLE IF NOT EXISTS IPlog (
				IP TEXT NOT NULL,
				Result INTEGER NOT NULL,
				Country TINYINT NOT NULL,
				timestamp INTEGER NOT NULL
			)");
		}
		$query = $IPDO->prepare("INSERT INTO IPlog (IP,Result,Country,timestamp) VALUES(?,?,?,?)");
		$QueryValues = array($IP,$Result,$Country,time());
		$result = $query->execute($QueryValues);
	}else{
		$IPlogData = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."IPlog.json");
		$IPlogData = @json_decode($IPlogData, true);
		if($IPlogData !== null){
			if(isset($IPlogData[$IP])){
				$IPlogData[$IP] = array("IP" => $IP, "Result" => $Result, "Country" => $Country, "timestamp" => time());
			}else{
				$IPlogData[$IP] = array("IP" => $IP, "Result" => $Result, "Country" => $Country, "timestamp" => time());
			}
		}else{
			$IPlogData = array();
			$IPlogData[$IP] = array("IP" => $IP, "Result" => $Result, "Country" => $Country, "timestamp" => time());
		}
		@file_put_contents_atom(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."IPlog.json",json_encode($IPlogData));
	}
}

 ?>
