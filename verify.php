<?php
// Special Configuration how the script should react when a user completed all available shortlinks:
// LOCK_ON_SHRT_UNAVAILABLE will tell the script whether to allow the user to do shortlinks he allready completed
// if Disabled (false): the user can repeat shorteners he has allready seen, even if they may not be monetized.
// if Enabled (true): the user will be sent back to the homepage until a shortlink is available again
$LOCK_ON_SHRT_UNAVAILABLE = false;

// optional variable to bypass shortlinks. useful if you want to troubleshoot issues or if you'd like to disable shortlinks but want to keep your config.
$USE_SHORTLINKS = true;


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

if(empty($Config)){ // check if the config exists and is ready to be used. if its not useable,return to IndexS
	redirect(DIRREC."/"); // script aint usuable so the user cant verify, back to index it is m8
}
if(@$Config['Useable'] !== true){ // check if the script is completely set up, and ready to handle users
	redirect(DIRREC."/"); // script aint usuable so the user cant verify, back to index it is m8
}

/* ==============================================================================================
 * """"" MAIN SECTION: VALIDATE USER, SEND SHORTLINK, VERIFY SHORTLINK, CREATE SESSION      =====
 * ==============================================================================================
*/
if(empty($_GET)){ // check if the user is returning from a shortink, if not, check if the user came from the index page
	if(empty($_POST)){
		$_SESSION['ErrMSG'] =  "No Form data! you need to use the form on the index page.";
		redirect(DIRREC."/"); // user didnt submit a form, back to index.
	}
	if(isset($_POST['CaptchaUsed'])){ // the form sent over captcha information (we hope)
		if(isset($Config['Captchas'][$_POST['CaptchaUsed'].'Websitekey']) && isset($Config['Captchas'][$_POST['CaptchaUsed'].'Secretkey'])){
			if($_POST['CaptchaUsed'] == "ReCaptcha"){// check if its Recaptcha
				$params = array("secret" => $Config['Captchas']['ReCaptchaSecretkey'], "response" => $_POST['g-recaptcha-response'], "remoteip" => $_SERVER['REMOTE_ADDR']);
				if(!CheckCaptcha("https://www.google.com/recaptcha/api/siteverify", $params)){
					$_SESSION['ErrMSG'] =  "Incorrect ReCaptcha! please try again.";
					redirect(DIRREC."/");
				}
			}elseif($_POST['CaptchaUsed'] == "HCaptcha"){
				$params = array("secret" => $Config['Captchas']['HCaptchaSecretkey'], "response" => $_POST['h-captcha-response'], "remoteip" => $_SERVER['REMOTE_ADDR']);
				if(!CheckCaptcha("https://hcaptcha.com/siteverify", $params)){ // check if its Hcaptcha
					$_SESSION['ErrMSG'] =  "Incorrect HCaptcha! please try again.";
					redirect(DIRREC."/");
				}
			}
			if(empty($_POST['h-captcha-response']) && empty($_POST['g-recaptcha-response'])){// if the user tried to be sneaky, let him know he actually needs to fill out a captcha.
				$_SESSION['ErrMSG'] =  "make sure to fill out the captcha before submitting the form";
				redirect(DIRREC."/");
			}
		}
	}
	//if the user got past this point, we verified that he solved the captcha correctily if the script was using captchas.
	if (!empty(@$Config['IPhubKey']) && function_exists("curl_init")) { // check if IPhub checks are enabled. and if the script supports cURL
		$IPresult = CheckIPlog($_SERVER['REMOTE_ADDR']); // check if the ip was checked before
		if(empty($IPresult)){ // if not, we query IPhub
			$che = curl_init("https://v2.api.iphub.info/ip/{$_SERVER['REMOTE_ADDR']}");
			curl_setopt_array($che, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => ["X-Key: ".$Config['IPhubKey']]
			]);
			$IPresult = json_decode(curl_exec($che), true);
			if($IPresult !== false){ // if the api call was a success, we  save the information in our own IPlog to avoid excessive calls.
				if(isset($IPresult['block']) && isset($IPresult['countryCode'])){
					SetIPlog($_SERVER['REMOTE_ADDR'], $IPresult['block'], $IPresult['countryCode']);
					if ($IPresult['block'] === 1) { // if IPhub suggests the user is a bot, we deny him further access
						$_SESSION['ErrMSG'] =  "Your IP has been flagged by IPhub as a proxy! please contact IPhub if you believe this is an error";
						redirect(DIRREC."/");
					}
				}
			}
		}elseif($IPresult['Result'] === 1){ // the user is a bot, we deny him further access
			$_SESSION['ErrMSG'] =  "Your IP has been flagged by IPhub as a proxy! please contact IPhub if you believe this is an error";
			redirect(DIRREC."/");
		}
	}
	if(!isset($_POST['address']) || !isset($_POST['currency'])){ // missing data, cant proceed
		$_SESSION['ErrMSG'] =  "Incomplete Data, make sure to fill out the whole form";
		redirect(DIRREC."/");
	}
	if(!isset($Config['Currencies'][$_POST['currency']])){ // check if the currency the user wants is a currency we offer
		$_SESSION['ErrMSG'] =  "This currency is not available, make sure to select the currency on the index page!";
		redirect(DIRREC."/");
	}
	unset($_SESSION[$_POST['currency'].'address']); // unset any values from the previous session.
	unset($_SESSION[$_POST['currency'].'SecToken']);
	unset($_SESSION[$_POST['currency'].'claimtime']);
	unset($_SESSION[$_POST['currency'].'previousTokens']);
	unset($_SESSION[$_POST['currency'].'RollOver']);
	if(!CharCheck($_POST['address'])){ // CHeck for invalid characters (if theres ever an address containing %,&",',$ and others  its likely invalid)
		$_SESSION['ErrMSG'] =  "The address you provided was faulty! please make sure to check for any characters like $, %, / etc";
		redirect(DIRREC."/");
	}
	if(isset($_POST['Referrer'])){ // optional: pass along the referrer to use later.
		if(CharCheck($_POST['Referrer'])){//check if its not invalid
			$_SESSION[$_POST['currency'].'Referrer'] = $_POST['Referrer'];
		}
	}
	//checked all the data, if the user reached this point we determine his data as valid and proceed with the shortlink managing
	$_SESSION[$_POST['currency'].'address'] = $_POST['address']; // save address for later use
	$_SESSION[$_POST['currency'].'SecToken'] = RandomString(32); // create a string to check for a valid shortlink pass :: Double acts as a token to see if the user's session expired.
	$_SESSION[$_POST['currency'].'claimtime'] = time()-3600; //claimtime set to 1 hour ago so the claim starts as soon as the user goes to the claimpage

	// Check which URL sccheme is used:
	if(isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']){
		$Scheme = "https://";
	}else{
		$Scheme = "http://";
	}
	if(isset($Config['Shortlinks']) && $USE_SHORTLINKS === true){ // check if the script has a shortlinks enabled
		if(!empty($Config['Shortlinks']) && isset($Config['Domain'])){ // check if there are actually any shortlinks it can use
			$ShortData = GetShortlinkSessions($_SERVER['REMOTE_ADDR']); // check for any previous sessions.
			$Views = array(); // Convenient array.
			if(!empty($ShortData)){// if the user had previous sessions
				foreach($ShortData as $row){ // iterate over those sessions
					$Views[$row['ShortUID']][] = $row; // Sort these sessions by Shortener ID
				}
			}
			foreach($Config['Shortlinks'] as $row){ // iterate over available shorteners
				if(isset($Views[$row['ShortUID']])){ // if there were previous sessions for this shortener
					if(count($Views[$row['ShortUID']]) >= $row['Viewcount']){ // check if the user viewed the shortener the max ammount of times.
						continue; // skip it
					}else{// othewhise set the data and break the loop
						$ShortID = $row['ShortUID'];
						$ApiLink = $row['Apilink'];
						break;
					}
				}else{ // no previous sessions, grab the first shortener data and break the loop
					$ShortID = $row['ShortUID'];
					$ApiLink = $row['Apilink'];
					break;
				}
			}
			if(!isset($ShortID)){ // if the above loop returned no data, we assume the user viewed all shorteners the max ammount of times
				if($LOCK_ON_SHRT_UNAVAILABLE){ // check if the script should deny any further claims.
					$_SESSION['ErrMSG'] =  "You've completed all Shorteners for today, check back in a few hours if a new shortener is available!";
					redirect(DIRREC."/");
				}else{ // otherwhise go with the default behaviour to wipe all data and let the user go through all shortlinks again. this may not pay the owner, but prevents spam
					WipeShortlinkSessions($_SERVER['REMOTE_ADDR']);
					foreach($Config['Shortlinks'] as $row){ // grab the first shortener's data and use it
						$ShortID = $row['ShortUID'];
						$ApiLink = $row['Apilink'];
						break;
					}
				}
			}
			$UUID = CreateShortlinkSessions($_SERVER['REMOTE_ADDR'],$ShortID); // create a session for the IP and shortener ID.
			$_SESSION['UUID'] = $UUID; // check the UUID returned for the session - this is unique to the session.
			$ValidateURL = $Scheme.$Config['Domain'].DIRREC."/verify.php?SecToken=".$_SESSION[$_POST['currency'].'SecToken']."&currency=".$_POST['currency'];
			$ShortenedResult = @json_decode(file_get_contents($ApiLink."&url=".urlencode($ValidateURL)), true); // query the shortener for an url to send the user to.
			if(isset($ShortenedResult['shortenedUrl'])){ // Set the short id, and send the user to the shortlink.
				$_SESSION['ShortID'] = $ShortID;
				redirect($ShortenedResult['shortenedUrl']);
			}else{ // the shortener returned a bad response, so we bypass it and return the user to the homepage with an error Message
				UpdateShortlinkSessions($_SERVER['REMOTE_ADDR'], $_SESSION['UUID']);
				$_SESSION['ErrMSG'] =  "Shortlink Error: please notify the owner that the shortlink '".ShortenerName($ApiLink)."' does not work anymore!";
				redirect(DIRREC."/");
			}
		}
	}

	// execute the script without a shortlink.
	$ValidateURL = DIRREC."/verify.php?SecToken=".$_SESSION[$_POST['currency'].'SecToken']."&currency=".$_POST['currency'];
	redirect($ValidateURL); // send the user to this file with the new data to simmulate a shortener completion,
}else{ // we deterimine the user came from a shortlink, so we validate it and make sure he solved it correctly:
	if(isset($Config['Shortlinks']) && $USE_SHORTLINKS === true){ // check if shortlinks are enabled
		if(!empty($Config['Shortlinks'])){ // check if the script has any active shortlinks
			$ArrayID = array_search($_SESSION['ShortID'], array_column($Config['Shortlinks'], 'ShortUID')); // check if we can find the shortID in our Array
			if($ArrayID === false){ // assume the shortlink doesnt exist anymore.
				$_SESSION['ErrMSG'] =  "Invalid Shortlink used, please try again";
				redirect(DIRREC."/");
			}
			// otherwhise we check if theres a pending session for the shortener the user came from. otherwhise we assume the session is outdated.
			if(!UpdateShortlinkSessions($_SERVER['REMOTE_ADDR'], $_SESSION['UUID'])){
				$_SESSION['ErrMSG'] =  "Shortlink Session not found, please try again!";
				redirect(DIRREC."/");
			}
		}
	}
	if(!isset($Config['Currencies'][$_GET['currency']])){ // check if the requested currency is available
		$_SESSION['ErrMSG'] =  "This currency is not available, make sure to select the currency on the index page!";
		redirect(DIRREC."/");
	}
	$Coin = $_GET['currency']; //makin life easier
	if(!CharCheck($_GET['SecToken']) || ($_SESSION[$Coin.'SecToken'] !== $_GET['SecToken'])){ // check if the token is valid.
		unset($_SESSION[$Coin.'address']); // unset address for that coin
		unset($_SESSION[$Coin.'SecToken']); // unset SecToken for that coin. we dont use session_destroy() because other coins may still be running
		$_SESSION['ErrMSG'] =  "Invalid Token, Session is Expired!";
		redirect(DIRREC."/");

	}
	setcookie($Coin."Token", $_SESSION[$Coin.'SecToken'], time()+(3600*24)); // set the token on the users machine to match against later
	 $UserData = GetUserData($Coin, $_SESSION[$Coin.'address']); // get the user's data. used to continue his progress.
	if($UserData === null){ // if there's no data, we make a blank array;
		$UserData = array();
	}
	if(!empty($UserData)){ // check if the user has previous data in the DB
		$UserData = array( // Update userdata for the session.
			"isNewUser" => false,
			"Address" => $_SESSION[$Coin.'address'],
			"Tokens" => $UserData['Tokens'],
			"SuccessfulWithdraws" => $UserData['SuccessfulWithdraws'],
			"ClaimStarted" => time(),
			"TimesUsed" => $UserData['TimesUsed'] + 1,
			"Referrer" => (@$_SESSION['Referrer'] ?: ($UserData['Referrer'] ?: null)),
			"ConfigID" => $Config['Currencies'][$_GET['currency']]['ConfigID']
		);
	}else{// mkae a new dataset for the user.
		$UserData = array(
			"isNewUser" => true,
			"Address" => $_SESSION[$Coin.'address'],
			"Tokens" => 0,
			"SuccessfulWithdraws" => 0,
			"ClaimStarted" => time(),
			"TimesUsed" => 1,
			"Referrer" => (@$_SESSION['Referrer'] ?: null),
			"ConfigID" => $Config['Currencies'][$_GET['currency']]['ConfigID']
		);
	}
	$_SESSION[$Coin.'RollOver'] = 0; // set the rollover directive to 0.
	SaveUserData($Coin, $UserData); // save the data for the claimpage to use it later.
	redirect(DIRREC."/claim.php?coin=".$Coin, 302 , 1); // the user is ready for claiming!
}




// easy commenting, not so easy developing. that shortener and captcha implementation is a miracle tbh
ob_end_flush(); // flush the output buffer and send user the page - solves issues with a few hosters.

?>
