<?php

/*
 * The admin panel is used to configure the script aswell as watch out for any warnings
 * The Script lets you Easily Manage Currencies, Shortlinks, Ads and other functionalities
 * i'd reccomend only going beyond this point if you're confident in your skills! (or make a backup of this file)
*/

//until EC adds the endpoint to fetch currencies, please update this Array manually when EC adds a new coin:
$Currencies = array("BTC","BCH","BCN","DASH","DGB","DOGE","ETH","LSK","LTC","XMR","NEO","PPC","POT","XRP","STRAT","TRX","WAVES","ZEC");

/* ==============================================================================================
 * """"" SCRIPT VALIDATION & FORM HANDLING                                                  =====
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
if($Config === ""){
	$Config = array();
}else{
	$Config = json_decode($Config, true);
	if($Config === null){
		die("Config file is corrupted, please make sure the config is intact");<?php

/*
 * The admin panel is used to configure the script aswell as watch out for any warnings
 * The Script lets you Easily Manage Currencies, Shortlinks, Ads and other functionalities
 * i'd reccomend only going beyond this point if you're confident in your skills! (or make a backup of this file)
*/

//until EC adds the endpoint to fetch currencies, please update this Array manually when EC adds a new coin:
$Currencies = array("BTC","BCH","BCN","DASH","DGB","DOGE","ETH","LSK","LTC","XMR","NEO","PPC","POT","XRP","STRAT","TRX","WAVES","ZEC");

/* ==============================================================================================
 * """"" SCRIPT VALIDATION & FORM HANDLING                                                  =====
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
if($Config === ""){
	$Config = array();
}else{
	$Config = json_decode($Config, true);
	if($Config === null){
		die("Config file is corrupted, please make sure the config is intact");
	}
}

if(!empty($_POST)){ // check if a form was submitted. all changes are made via forms, so most of the logic on this page does exactly that.
	// this if block only Handles the login process and initial Admin Credential process
	if(isset($_POST['CreateCredentials'])){ // check if its the "CreateCredentials" Form
		if(@$Config['Useable'] === true){
			redirect(DIRREC."/"); //script isnt in the setup stage, so we exit the form to prevent brute-forcing the securitykey.
		}
		if(@$_SESSION['ADM_MAX_TRIES'] > 19 && @$_SESSION['ADM_LIMIT_SECKEY'] === SECKEY){ // check if the user spammed the admin login form and deny access if thats the case
			$_SESSION['ErrMSG'] =  "You've used the incorrect credentials too many times. you can reset this restriction by setting a different Security Key";
			redirect(DIRREC."/admin.php");
		}
		if(@$_SESSION['ADM_LIMIT_SECKEY'] !== SECKEY){ // if the admin changed the SecKey, reset limits
			$_SESSION['ADM_MAX_TRIES'] = 0;
			$_SESSION['ADM_LIMIT_SECKEY'] = SECKEY;
		}
		if(@$_POST['SecKey'] !== SECKEY){ //Check for Security key
			$_SESSION['ErrMSG'] =  "Wrong Security Key! this form is only for the admin. you can find and set your security key in the config.php file";
			redirect(DIRREC."/"); // notify the user if the key was wrong and redirect him back to the index page.
		}elseif(isset($_POST['Username']) && isset($_POST['Password'])){
			if(empty($Config)){ // set the new details in the config
				$Config = array("AdminCredentials" => array("Username" => $_POST['Username'], "Password" => $_POST['Password']), "Useable" => false);
			}else{
				$Config['AdminCredentials']['Username'] = $_POST['Username'];
				$Config['AdminCredentials']['Password'] = $_POST['Password'];
			}

			UpdateConfig($Config); // Update the config file to reflect the changes
			$_SESSION['ADM_IS_LOGGED_IN'] = true; // automatically login the user
		}else{
			$_SESSION['ErrMSG'] =  "Incomplete Details, make sure to fill out both the Username & Password";
			redirect(DIRREC."/"); // notify the user if the details were incomplete and redirect back to index
		}
	}elseif(isset($_POST['AdminLogin'])){
		if(@$_SESSION['ADM_MAX_TRIES'] > 19 && @$_SESSION['ADM_LIMIT_SECKEY'] === SECKEY){ // check if the user spammed the admin login form and deny access if thats the case
			$_SESSION['ErrMSG'] =  "You've used the incorrect credentials too many times. you can reset this restriction by setting a different Security Key";
			redirect(DIRREC."/admin.php");
		}
		if(@$_SESSION['ADM_LIMIT_SECKEY'] !== SECKEY){ // if the admin changed the SecKey, reset limits
			$_SESSION['ADM_MAX_TRIES'] = 0;
			$_SESSION['ADM_LIMIT_SECKEY'] = SECKEY;
		}
		if(@$_POST['Username'] === $Config['AdminCredentials']['Username'] && @$_POST['Password'] === $Config['AdminCredentials']['Password']){
			$_SESSION['ADM_IS_LOGGED_IN'] = true; // the user gave the correct credentials, so we let him in.
			$_SESSION['ADM_MAX_TRIES'] = 0;
			redirect(DIRREC."/admin.php"); // refresh the page so that the page uses the new config
		}else{ // the user gave invalid credentials, so we increment the ammount of failed tries.
			if(isset($_SESSION['ADM_MAX_TRIES'])){
				$_SESSION['ADM_MAX_TRIES']++;
			}else{
				$_SESSION['ADM_MAX_TRIES'] = 1;
				$_SESSION['ADM_LIMIT_SECKEY'] = SECKEY;
			}
		}
	}
}
if(empty($Config)){ // check if the config exists and is ready to be used. if its not useable, return the user to the index file
	redirect(DIRREC."/");
}
/* ==============================================================================================
 * """"" MAIN SECTION: DISPLAYS LOGIN FORM OR ADMIN PANEL IF LOGGED IN                      =====
 * ==============================================================================================
*/

if(@$_SESSION['ADM_IS_LOGGED_IN'] !== true){
include FDSR.DIRECTORY_SEPARATOR."/header.php"; // include the standard header
	echo '
		<div class="row justify-content-center container-fluid FirstLayer">
			<div class="col-12 col-md"></div>
			<div class="col-12 col-md-8  mx-auto text-center">
				<h1>Login to the admin panel</h1>
				<div class="card col-12 col-sm-6 offset-sm-3 SecondLayer">
					<form method="POST" action="'.DIRREC.'/admin.php">
						<input type="text" name="Username" placeholder="Username"><br>
						<input type="password" name="Password" placeholder="Password"><br>
						<input type="hidden" name="AdminLogin" value=true>
						<input type="submit" value="Login" class="btn">

					</form>
				</div>
				<blockquote> if you ever forget your admin credentials, you can find them in the config.php file!
				</blockquote>
			</div>
			<div class="col-12 col-md"></div>
		</div>';
		include FDSR.DIRECTORY_SEPARATOR."/footer.php"; // include the standard footer
		exit;
}

$Date = date("d-m"); // get the date in a day-month format
$LimitLog = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."APIlimit.json");
$LimitLog = @json_decode($LimitLog, true); // load the limitlog and make it parseable by php
if($LimitLog === null){ //if the limit log is empty, we  assume theres no data about any limits being reached
	$LimitLog = array();
}
//big form processing code,
if(!empty($_POST)){ // check if a form was submitted. all changes i made via forms, so this if block is BIG. alot of code all relevant to updating the config
	$_POST = r_array_map("intifyArray", $_POST); // give integers and floats its correct type to check against later. HTTP POST requeest send all data as Strings by default. so this is required to check for numbers.
	if(isset($_POST['MainForm'])){ // check if its the "MainAForm" Form
		unset($_POST['MainForm']); //remove the key so it doesnt get added to the config, its useless beyond this point anyways
		$ChangesMade = array(); // initiallize the array to keep track of all changes.
		foreach($_POST as $key => $row){
			if($key === "Currencies"){ // fetch all changes in regards to currencies
				foreach($row as $currency => $options){ //itterate over all currencies in the form
					if(!isset($Config['Currencies'][$currency])){continue;} //if the currency submitted is not an active currency, we skip it.
					$CurrInfo = $Config['Currencies'][$currency]; // duplicate the current configuration to check against later
					if(isset($options['Apikey']) && isset($options['Ammount']) && isset($options['Timer']) && isset($options['PayoutCycle'])){
						//check if all the required values are there, otherwhise we skip this currency
						if((is_int($options['PayoutCycle']) && is_int($options['Timer']) && (is_int($options['Ammount']) || is_float($options['Ammount'])) && CharCheck($options['Apikey']))){
							//Check if all values are in a valid format
							if($options['Ammount'] !== $CurrInfo['Ammount'] || $options['Timer'] !== $CurrInfo['Timer'] || $options['Apikey'] !== $CurrInfo['Apikey'] || $options['PayoutCycle'] !== $CurrInfo['PayoutCycle']){
								//Check if any of the values changed compared to the last config
								if($options['Ammount'] !== $CurrInfo['Ammount'] || $options['PayoutCycle'] !== $CurrInfo['PayoutCycle']){
									$CurrInfo['ConfigID']++;
								} // explicitly change the ConfigID if the ammount and/or payout cycle change. this causes user progress to reset as it would otherwhise massively screw up payouts.
								$Config['Currencies'][$currency] = array("Apikey" => $options['Apikey'],"UserToken" => $options['UserToken'], "Timer" => $options['Timer'], "Ammount" => $options['Ammount'], "PayoutCycle" => $options['PayoutCycle'], "ConfigID" => $CurrInfo['ConfigID']); // make the change
								foreach($options as $key => $val){ // go over all the changes. and log everything that changed.
									if($val !== $CurrInfo[$key]){
										$ChangesMade[] = '<b>'.$currency.'</b>: '.$key.' changed from <b>"'.$CurrInfo[$key].'"</b> to <b>"'.$val.'"</b>';
									}
								}
							}
						}
					}
				}
			}elseif($key === "Shortlinks"){ // fetch all changes regarding shortlinks
				foreach($row as $Shortener => $options){ // iterate over all shorteners.
					if(!isset($Config['Shortlinks'][$Shortener])){continue;} // skip shorteners that dont exist
					$ShortInfo = $Config['Shortlinks'][$Shortener]; //duplicate the current configuration to check against later
					if(isset($options['Apilink']) && isset($options['Viewcount']) && isset($options['Rank'])){
						// check if all required fiels are there
						if((is_int($options['Viewcount']) && is_int($options['Rank']))){ // check if RANK and VIEWCOUNT are integers
							if($options['Apilink'] !== $ShortInfo['Apilink'] || $options['Viewcount'] !== $ShortInfo['Viewcount'] || $options['Rank'] !== $ShortInfo['Rank']) {
								// check if anything changed from the last config
								$Config['Shortlinks'][$Shortener] = array("Apilink" => $options['Apilink'], "Viewcount" => $options['Viewcount'], "Rank" => $options['Rank'], "ShortUID" => $ShortInfo['ShortUID']); // make the change
								foreach($options as $key => $val){// go over all the changes, and log everything that changed
									if($val !== $ShortInfo[$key]){
										$ChangesMade[] = '<b>'.$Shortener.'</b>: '.$key.' changed from <b>"'.$CurrInfo[$key].'"</b> to <b>"'.$val.'"</b>';
									}
								}
							}
						}
					}
				}
				uasort($Config['Shortlinks'], function($a, $b){//Re-align Shorteners based on rank
					if ($a['Rank'] == $b['Rank']) {
						return 0;
					}
					return ($a['Rank'] < $b['Rank']) ? -1 : 1;
				});
			}elseif($key === "Ads"){ // fetch all changes regarding ads
				if(!isset($Config['Ads'])){
					$Config['Ads'] = array("MainSet" => array(), "ClaimSet" => array());
				}
				foreach($row as $AdKey => $AdVal){ // iterate over all adspaces
					$AdVal = base64_encode($AdVal);
					if(contains("Main", $AdKey)){ // check if adspot is in the MainSet
						$MainAdKey = str_replace("Main","",$AdKey); //Modify the Key
						if(@$Config['Ads']['MainSet'][$MainAdKey] !== $AdVal){ // check if the value changed
						$ChangesMade[] = '<b>Main Ads Set</b>: '.$MainAdKey.' changed from <b>"'.base64_decode(@$Config['Ads']['MainSet'][$MainAdKey]).'"</b> to <b>"'.base64_decode($AdVal).'"</b>';
							$Config['Ads']['MainSet'][$MainAdKey] = $AdVal; //change it and log it
						}
					}elseif(contains("Claim", $AdKey)){// check if adspot is in the ClaimSet
						$ClaimAdKey = str_replace("Claim","",$AdKey);//Modify the key
						if(@$Config['Ads']['ClaimSet'][$ClaimAdKey] !== $AdVal){// check if the value changed
						$ChangesMade[] = '<b>Claim Ads Set</b>: '.$ClaimAdKey.' changed from <b>"'.base64_decode(@$Config['Ads']['ClaimSet'][$ClaimAdKey]).'"</b> to <b>"'.base64_decode($AdVal).'"</b>';
							$Config['Ads']['ClaimSet'][$ClaimAdKey] = $AdVal; // change it and log it
						}
					}
				}
			}elseif($key === "Captchas"){ // fetch all changes regarding captchas.
				foreach($row as $Ckey => $Cval){ // itterate over all captcha keys
					if($Cval !== @$Config['Captchas'][$Ckey] && (CharCheck($Cval) || empty($Cval))){ // check if the values are legit and different
						$ChangesMade[] = '<b>Captchas</b>: '.$Ckey.' changed from <b>"'.@$Config['Captchas'][$Ckey].'"</b> to <b>"'.$Cval.'"</b>';
						$Config['Captchas'][$Ckey] = $Cval; // change it and log it
					}
				}
				if(!empty(@$Config['Captchas'])){
					if(empty(@$Config['Captchas']['ReCaptchaWebsitekey']) && empty(@$Config['Captchas']['ReCaptchaSecretkey']) && empty(@$Config['Captchas']['HCaptchaWebsitekey"']) && empty(@$Config['Captchas']['HCaptchaSecretkey'])){// if no values for captchas are set, remove the key completely to disable captchas.
						unset($Config['Captchas']);
					}
				}

			}else{
				if(!in_array($key, array("Sitename","Domain","CustomCSS","IPhubKey"))){ // check for any key not in that array
					if(!CharCheck($row)){continue;} // validate it and skip it if it contains bad characters
				}
				if($key === "RefCommission" && !is_int($row) && ($row <= 100 && $row >= 0)){// make sure RefCommission is an integer and between 0 and 100
					$row = 0;
				}
				if($key === "CustomCSS"){
					$row = base64_encode($row); // base64 encode the CSS becuase it has characters JSON doesnt handle great - at all
				}
				if($key === "Sitename"){
					if($row == ""){
						$row = "Autofaucet";
					}
				}
				if($key === "Domain"){
					$UrlArray = parse_url("http://".$row); // convert the url into an array containing each part of the url
					if(!empty($UrlArray['host'])){
						$row = $UrlArray['host'];// split the domain into various parts and only use the host part
					}else{
						continue;// the value supplied was invalid, so we dont make a change.
					}
				}

				if($key === "IPhubKey" && !function_exists("curl_init")){ // check if PHP curl is enabled, otherwhise skip the IPhubKey
					$ChangesMade[] = '<b>NOTE:</b>: Couldnt activate IPhub because your server has cURL disabled! please enable the PHP curl extension and try again</b>';
					continue;
				}
				if($row !== @$Config[$key]){// if value changed
					if($key === "CustomCSS"){
						$ChangesMade[] = '<b>General info</b>: '.$key.' changed from <b>"'.@base64_decode($Config[$key]).'"</b> to <b>"'.base64_decode($row).'"</b>';
					}else{
						$ChangesMade[] = '<b>General info</b>: '.$key.' changed from <b>"'.@$Config[$key].'"</b> to <b>"'.$row.'"</b>';
					}
					$Config[$key] = $row; // change it and log it
				}
			}
		}
	//Create message displaying Changes
	$_SESSION['SuccessMSG'] = " Success! you've made the following changes:<br>";
	foreach($ChangesMade as $change){//iterate over changes
		$_SESSION['SuccessMSG'] = $_SESSION['SuccessMSG'].$change."<br>"; // add Changes to the message.
	}
	UpdateConfig($Config);//update Config
}elseif(isset($_POST['AddCurrency'])){ // check if its the "addCurrency" form
		if(isset($_POST['PayoutCycle']) && isset($_POST['Timer']) && isset($_POST['Ammount']) && isset($_POST['Apikey']) && isset($_POST['UserToken']) && isset($_POST['currency'])){
			//check of all needed values are there
			if(is_int($_POST['PayoutCycle']) && is_int($_POST['Timer']) && (is_int($_POST['Ammount']) || is_float($_POST['Ammount'])) && CharCheck($_POST['Apikey'])){
				//check if all values are vaid types
				$res = CheckCurrencies($_POST['Apikey'], $_POST['UserToken'], $_POST['currency']); // Check if EC Supports the currency
				if ($res['success'] === true) { // check if the call worked.
		           	if($_POST['currency']){
						if(@in_array($_POST['currency'], $Config['Currencies'])){ // check if hte currency is allready active in the script
							$_SESSION['ErrMSG'] = 'Allready active. you wont need to add a currency more than once.';
							redirect(DIRREC."/admin.php");
						}
					}
					$Config['Currencies'][$_POST['currency']] = array("PayoutCycle" => $_POST['PayoutCycle'], "Timer" => $_POST['Timer'], "Ammount" => $_POST['Ammount'], "Apikey" => $_POST['Apikey'], "UserToken" => $_POST['UserToken'], "ConfigID" => 1); // make the change and notify the admin
					$_SESSION['SuccessMSG'] = 'Success! your currency has been added and can now be used by users!';
					if(@$Config['Useable'] === false){ // if the script was previously unuseable, set it to useable so it can handle users
						$Config['Useable'] = true;
						$_SESSION['SuccessMSG'] = $_SESSION['SuccessMSG']." your script can now handle users!";
					}
					$image = @file_get_contents('https://expresscrypto.io/images/'.strtolower($_POST['currency']).'.png');
					if($image !== false){ // get the image from ECs Server and put it into the images folder so the script can use it
                    	@file_put_contents(FDSR.DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR.$_POST['currency'].".png", $image);
					}
					UpdateConfig($Config); // update the config
		        } else {
					if($res['data']['status'] === 403){ // the API call returned with an invalid key error, so we notify the admin
						$_SESSION['ErrMSG'] = 'Invalid Apikey. please make sure to copy the key correctly. if you have IP access limits please add the IPaddress of this server to your account';
						redirect(DIRREC."/admin.php");
					}elseif($res['data']['status'] === 415){ // some other Error happened, so we let the admin know
						$_SESSION['ErrMSG'] = 'Invalid Currency. please make sure to use the Shorthand of the currency (Bitcoin = BTC etc)';
						redirect(DIRREC."/admin.php");
					}else{
						$_SESSION['ErrMSG'] = 'EC Responded with an error: '. $res['data']['message'];
						redirect(DIRREC."/admin.php");
					}
		        }
			}else{ // let the admin know his data was invalid
				$_SESSION['ErrMSG'] = 'Invalid Data, please make sure to use the form inside the modal';
				redirect(DIRREC."/admin.php");
			}
		}else{ // let the admin know he forgot to fill out one or more fields
			$_SESSION['ErrMSG'] = 'Incomplete Data. please make sure to fill out all forms';
			redirect(DIRREC."/admin.php");
		}
	}elseif(isset($_POST['AddShortlink'])){ // check if its the "addShortlink" form
		if(isset($_POST['Apilink']) && isset($_POST['Viewcount'])){ // check if Apilink and Viewcount are sent
			$UrlArray = parse_url($_POST['Apilink']); // convert the url into an array containing each part of the url
			if(!empty($UrlArray['scheme']) && !empty($UrlArray['host']) && !empty($UrlArray['path']) && !empty($UrlArray['query'])){
				//check if there are any parts of the url missing
				if(isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']){ // determine if the script uses HTTPS
					$Scheme = "https://";
				}else{
					$Scheme = "http://";
				}
				$ShortenedResult = @json_decode(file_get_contents($_POST['Apilink']."&url=".urlencode($Scheme.$Config['Domain'].DIRREC)), true);
				if(isset($ShortenedResult['shortenedUrl'])){// try the API url and check if it responds with a valid json response
					if(isset($Config['Shortlinks'])){// check if there are previous shorteners or if this is a new shortener.
						$NextRank = @max(array_column($Config['Shortlinks'], 'Rank'))+1; // get the highest rank to give to the new shortener
						$Config['Shortlinks'][ShortenerName($_POST['Apilink'])] = array("Apilink" => stripslashes($_POST['Apilink']), "Viewcount" => (is_int($_POST['Viewcount']) ? $_POST['Viewcount'] : 1), "Rank" => $NextRank, "ShortUID" => RandomString(16));
						//add the new shortener to the script. this automatically overridesany previous
					}else{
						$Config['Shortlinks'] = array(ShortenerName($_POST['Apilink']) => array("Apilink" => $_POST['Apilink'], "Viewcount" => (is_int($_POST['Viewcount']) ? $_POST['Viewcount'] : 1), "Rank" => 1, "ShortUID" => RandomString(16)));
						// add the new shortener to the script.
					}
					$_SESSION['SuccessMSG'] = 'Succesfully added '.ShortenerName($_POST['Apilink']).' to your shortlink list. if you want to test it click <a href="'.$ShortenedResult['shortenedUrl'].'"> here</a>';
					UpdateConfig($Config); // notify the user and make the changes.
				}else{
					$_SESSION['ErrMSG'] = "the Shortlink Url was invalid, please make sure to copy it properly";
					redirect(DIRREC."/admin.php"); // got a bad response from the shortener, so we discard the shortener
				}
			}else{
				$_SESSION['ErrMSG'] = "the shortlink Url is invalid(Malformed), please make sure to copy it properly!";
				redirect(DIRREC."/admin.php");// the shortlink url was missing parts
			}
		}else{
			$_SESSION['ErrMSG'] = "Invalid data, please make sure to fill out all fields that arent disabled!";
			redirect(DIRREC."/admin.php");// the admin forgot to fill out some fields
		}
	}else{

	}
}



if(!empty($_GET)){ // get form block. used to delete shorteners and Currencies, as we barely need any data to delete a shortener
	$_GET = r_array_map("intifyArray", $_GET);// assign numbers and floats their correct type
	if(isset($_GET['DeleteCurrency'])){ // check if the user wanted to delete a currency
		if(isset($Config['Currencies'][$_GET['DeleteCurrency']])){ // check if its an active currency and delete it
			unset($Config['Currencies'][$_GET['DeleteCurrency']]);
			if(empty($Config['Currencies'])){ // check if the script has no active currencies, and let the user know that the script cant server users until he adds a new one
				$Config['Useable'] = false;
				$_SESSION['SuccessMSG'] = 'Succesfully Deleted '. $_GET['DeleteCurrency'].'! note that because the script now has no Active currencies the script state has been set to maintanance. to remove the script from maintanance add a currency.';
			}else{
				$_SESSION['SuccessMSG'] = 'Succesfully Deleted '. $_GET['DeleteCurrency'].'!';
			}

			UpdateConfig($Config); // update the config to save the changes.
		}else{
			$_SESSION['ErrMSG'] = 'the currency you tried to select is not an active currency.';
			redirect(DIRREC."/admin.php"); // notify the user that the currency was not active in the script.
		}
	}elseif(isset($_GET['DeleteShortener'])){ // check if the user wanted to delete a shortener.
		if(!isset($Config['Shortlinks'][$_GET['DeleteShortener']])){ // check if its an active shortener in the script.
			$_SESSION['ErrMSG'] = 'this is not an active shortener!';
			redirect(DIRREC."/admin.php");// deny the form and let the user know its not a valid shortener
		}
		unset($Config['Shortlinks'][$_GET['DeleteShortener']]);
		if(empty($Config['Shortlinks'])){ // unset the shortener. if the script has no active shorteners we remove the key entirely and let the user know that the shortlink function is disabled until he adds a new shortener.
			$_SESSION['SuccessMSG'] = 'Succesfully Deleted the shortener! this shortener was the only shortener configured in the script, so we disabled shortlinks for now. you can re-enable them once you add a new shortener';
		}else{
			$_SESSION['SuccessMSG'] = 'Succesfully Deleted the shortener!';
		}

		UpdateConfig($Config); // submit the changes.
	}
}



// the code below fetches data for the admin panel.


if(isset($_SESSION['ErrMSG'])){ // general Error message processing code
	$messages = $messages.ErrorMSG("clear", $_SESSION['ErrMSG']);
	unset($_SESSION['ErrMSG']);
}
if(isset($_SESSION['SuccessMSG'])){ // general sucess message processing code.
	$messages = $messages.SuccessMSG("clear", $_SESSION['SuccessMSG']);
	unset($_SESSION['SuccessMSG']);
}
$APIissues = 0; // initiate the ApiIssues variable to be used later.
if(isset($LimitLog[$Date])){ // check if there is an entry for today
	if($Limitlog[$Date]['LimitReached'] > 0){ // check if the script reached any api limits today.
		$APIissues = $Limitlog[$Date]['LimitReached'];
		if(isset($Limitlog[$Date]['CreditsEmpty'])){ // check if the error in question was related to api credits, if so we let the user know
			$messages = $messages.ErrorMSG("clear", "WARNING: the script has reached your API limits! <b>".$Limitlog[$Date]['LimitReached']."</b> APIcalls failed due to this limit today ;/ we reccomend to upgrade your plan to a higher plan or to extend the payout cycle for your currencies<br>NOTICE: you ran out of API credits on EC, this may lead to all further apicalls failing, so we reccomend you to buy more credits as soon as possible.");
		}else{ // otherwhise we simply notify him of the limits.
			$messages = $messages.ErrorMSG("clear", "WARNING: the script has reached your API limits! <b>".$Limitlog[$Date]['LimitReached']."</b> APIcalls failed due to this limit today ;/ we reccomend to upgrade your plan to a higher plan or to extend the payout cycle for your currencies");
		}
	}
}



//determine at which stage of script setup the owner is at
$SetupStage = 1;// initialize the variable with 1 to indicate that the script is not yet ready.
if(isset($Config['Currencies'])){
	$SetupStage = 2; // change the setup Stage if there is atleast one currency added.
}

include FDSR.DIRECTORY_SEPARATOR."/header.php"; // include the standard header
?>
<style>
Select ,input{
	font-size:1.6rem;
	border-radius:5px;
	padding:0px;
}
</style>
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>
		<div class="row justify-content-center container-fluid FirstLayer">
			<div class="col-12 col-md"></div>
			<div class="col-12 col-md-8  mx-auto text-center">
				<h1>Admin Panel!</h1>
				<div class="card col-12 SecondLayer">
					<h3>Script overview</h3>
					<?php echo $messages ?>
					<div class="row">
						<div class="col-4 card ThirdLayer"><h5>Currencies: <br><?php echo @count($Config['Currencies']) ?><br><span class="Smalltext">active</span></h5></div>
						<div class="col-4 card ThirdLayer"><h5>Shortlinks: <br><?php echo @count($Config['Shortlinks']) ?><br><span class="Smalltext">active</span></h5></div>
						<div class="col-4 card ThirdLayer"><h5>Issues: <br><?php if($APIissues !== 0){echo '<span class="text-danger">'.$APIissues.'</span>';}else{echo "0";} ?><br><span class="Smalltext">today</span></h5></div>
					</div>
					<h3>Configuration & Customization</h3>
					<div class="col-12 col-sm-12">
						<ul class="nav nav-tabs nav-justified">
							<li class="nav-item">
								<a class="nav-link active" data-toggle="tab" data-target="#GeneralInfo">General Info</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" data-target="#Currencies">Currencies</a>
							</li>
						<?php if($SetupStage === 2){ // if the user has set up atleast 1 currency (required for the script to work) we display additional config options ?>
							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" data-target="#Shortlinks">Shortlinks</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" data-target="#Advertisements">Advertisements</a>
							</li>
						<?php }else{//hasnt set up a currency so we gray out the buttons and make them unuseable ?>
							<li class="nav-item">
								<a class="nav-link disabled" >Shortlinks<</a>
							</li>
							<li class="nav-item">
								<a class="nav-link disabled" >Advertisements</a>
							</li>
						<?php } ?>
						</ul>
					</div>
					<form action="admin.php" method="POST">
					<input type="hidden" name="MainForm" value="1">
					<div id="settings" class="row card SecondLayer justify-content-center tab-content">
						<div id="GeneralInfo" class="tab-pane active" data-parent="#settings">
							<div class="col-12 justify-content-left">
								<h4>General Settings</h4>
								<hr>
								<p>these are basic Settings that you can save below. you can set your script name, refferal percentage aswell as define a few things the script neeeds to function!</p>
								<div class="row">
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Sitename is purely for display, you may find it in the nav bar or on the start page, aswell as inside descriptive texts">help_outline</i>
										<label for="Sitename">Sitename</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="Sitename" type="text" name="Sitename" required <?php echo (isset($Config['Sitename']) ? 'value="'.$Config['Sitename'].'"': "")?>>
									</div>
								</div><hr>
								<div class="row">
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="the domain this script is used on. this is required as the script cant reliably find out the domain by itself. so it needs to be defined for shortlinks to work">help_outline</i>
										<label for="Domain">Domain</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="Domain" type="text" name="Domain" required <?php echo (isset($Config['Domain']) ? 'value="'.$Config['Domain'].'"': "")?>>
										<small>The Domain should follow this pattern: domain.tld or sub.domain.tld NO Slashes, no http:// or https:// and no folder needs to be added to the domain. simply (sub.)domain.tld - this is required to use the shortlink functionality</small>
									</div>

								</div><hr>
								<div class="row">
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Referral Commission rewards users for inviting frieds and other people to your site. if enabled, the users will get a link they can give to people to start earning referral rewards. you can set it to 0 to disabled referrals">help_outline</i>
										<label for="RefCommission">Referral commission (in %)</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-25 float-left" id="RefCommission" type="number" name="RefCommission" min=0 max=100 required <?php echo (isset($Config['RefCommission']) ? 'value="'.$Config['RefCommission'].'"': "")?>>
									</div>
								</div><hr>
								<div class="row">
									<div class="col-12 col-sm-12">
										<h5>Custom CSS</h5>
										<small>This Textfield allows you to override any default CSS defined by Bootstrap or the script. please make sure to only put CSS here, as the browser will parse all the information in this field as CSS</small>
									</div>
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Custom CSS can be used to easily change the colors of the site or change smaller things on the scripts layout! feel free to mess arround with it">help_outline</i>
										<label for="CustomCSS">CustomCSS</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<textarea class="w-100 float-left" id="CustomCSS" name="CustomCSS"><?php echo (isset($Config['CustomCSS']) ? base64_decode($Config['CustomCSS']): "")?></textarea>
									</div>
								</div><hr>
								<div class="row">
									<div class="col-12 col-sm-12">
										<h5>Captchas (optional)</h5>
										<small>You can choose to add ReCaptcha or Hcaptcha to your site to increase security. you can set up your Captcha Keys on <a href="https://www.google.com/recaptcha">ReCaptcha</a> and <a href="https://hcaptcha.com/?r=e5ad8e8181da">Solvemedia</a><br>NOTE: these captchas may result in some users leaving! so you may see less claims from valid users when activating a captcha. its reccomended to disable captchas when using a shortlink as they use captchas too resulting in the user having to solve a difficult captcha twice<br><br> leave the fields below blank to use no captchas!</small>
									</div>
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="if you include valid ReCaptcha Details you can add security to your script this website key is a public key thats used to load the captcha on the user's page">help_outline</i>
										<label for="ReCaptchaWebsitekey">ReCaptcha Websitekey</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="ReCaptchaWebsitekey" type="text" name="Captchas[ReCaptchaWebsitekey]" <?php echo (isset($Config['Captchas']['ReCaptchaWebsitekey']) ? 'value="'.$Config['Captchas']['ReCaptchaWebsitekey'].'"': "")?>>
									</div>
								</div>
								<div class="row">
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="the SecretKey is used to validate if a user completed a captcha. this should never be shown to the user">help_outline</i>
										<label for="ReCaptchaSecretkey">ReCaptcha Secretkey</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="ReCaptchaSecretkey" type="text" name="Captchas[ReCaptchaSecretkey]" <?php echo (isset($Config['Captchas']['ReCaptchaSecretkey']) ? 'value="'.$Config['Captchas']['ReCaptchaSecretkey'].'"': "")?>>
									</div>
								</div><hr>
								<div class="row">
									<div class="col-12 col-sm-12">
										<small>Hcaptcha is an alternative to Recaptcha that helps companies with AIresearch. the upside is that they pay for some traffic!</small>
									</div>
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="if you include valid HCaptcha Details you can add security to your script this website key is a public key thats used to load the captcha on the user's page">help_outline</i>
										<label for="HCaptchaWebsitekey">HCaptcha Websitekey</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="HCaptchaWebsitekey" type="text" name="Captchas[HCaptchaWebsitekey]" <?php echo (isset($Config['Captchas']['HCaptchaWebsitekey']) ? 'value="'.$Config['Captchas']['HCaptchaWebsitekey'].'"': "")?>>
									</div>
								</div>
								<div class="row">
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="the SecretKey is used to validate if a user completed a captcha. this should never be shown to the user">help_outline</i>
										<label for="HCaptchaSecretkey">HCaptcha Secretkey</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="HCaptchaSecretkey" type="text" name="Captchas[HCaptchaSecretkey]" <?php echo (isset($Config['Captchas']['HCaptchaSecretkey']) ? 'value="'.$Config['Captchas']['HCaptchaSecretkey'].'"': "")?>>
									</div>
								</div><hr>
								<div class="row">
									<div class="col-12 col-sm-12">
										<h5>IPhub (Optional)(Requires cURL)</h5>
										<small>IPhub is a Proxy Detection service offerign a free method of detecting bots. you can create an apikey <a href="https://iphub.info/" target="_blank">here!</a></small>
									</div>
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The IPhub key is used to Communicate with IPhub to help identify Proxies and Bots we reccomend to use IPhub when using Shortlinks!">help_outline</i>
										<label for="IPhubKey">IPHub Key</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="IPhubKey" type="text" name="IPhubKey" <?php echo (isset($Config['IPhubKey']) ? 'value="'.$Config['IPhubKey'].'"': "")?>>
									</div>
								</div><hr>
							</div>
						</div>
						<div id="Currencies" class="tab-pane fade" data-parent="#settings">
							<div class="col-12">
								<h4>Currency Settings</h4>
								<hr>
								<p>these are your currency settings, here you can edit your currencies or add new currencies. there are a couple things to watch out for which are outlined below.<br>NOTE: when adding New currencies your other changes WONT BE SAVED. so if you've made any changes, submit them and then add a new currency.<br> ADVICE: dont set your payout for currencies like btc too low. set a higher timer and ammount if you have to. but if the user's balance doesnt reach 1 satoshi before payout the user experience will be terrible. (the script allows for sub-satoshi values to roll over, but doesnt notify the user on this so expect heaps of complains if you choose to set the value too low).</p>
								<button type="button" class="btn" data-toggle="modal" data-target="#AddCurrencies">Add Currency</button><hr>
								<?php
								if(!empty($Config['Currencies'])){ // iterate over currencies and create the form for each currency.
								foreach ($Config['Currencies'] as $row => $options) {
								         echo'
										<h4>'.$row.'</h4>
										<div class="row">
											<div class="col-12 col-sm-4">
												<div class="ImageContainer">
													<img alt="'.$row.' Icon" src="'.DIRREC.'/images/'.$row.'.png" class="img-fluid">
												</div>
											</div>
											<div class="col-12 col-sm-8">
												<div class="row">
													<div class="col-3 col-sm-3">
														<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The UserToken is used for tracking in excesscrypto. We recommend to atleast set this to some value">help_outline</i>
														<label for="'.$row.'Key">'.$row.' UserToken</label>
													</div>
													<div class="col-8 col-sm-8">
														<input class="w-100 float-left" id="'.$row.'UserToken" type="text" name="Currencies['.$row.'][UserToken]" '.(isset($options['UserToken']) ? 'value="'.$options['UserToken'].'"': "").'>
													</div>
												</div>
												<div class="row">
													<div class="col-3 col-sm-3">
														<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The ApiKey is Used in order to Communicate with ExpressCrypto. therefore its required to use this currency. you can set each currency to a different APIkey or use the same for all of them, we reccomend to use more than one">help_outline</i>
														<label for="'.$row.'Key">'.$row.' Apikey</label>
													</div>
													<div class="col-8 col-sm-8">
														<input class="w-100 float-left" id="'.$row.'Key" type="text" name="Currencies['.$row.'][Apikey]" '.(isset($options['Apikey']) ? 'value="'.$options['Apikey'].'"': "").'>
													</div>
												</div>
											</div>
											<div class="form-group col-md-4">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Ammount of '.$row.' in satoshi theu ser gets per reload.">help_outline</i>
												<label for="'.$row.'Ammount">'.$row.'Ammount (in satoshi)</label>
												<input id="'.$row.'Ammount" type="Number" step="0.001" name="Currencies['.$row.'][Ammount]" value="'.$options['Ammount'].'">
											</div>
											<div class="form-group col-md-4">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Timer betweem reloads for '.$row.' ">help_outline</i>
												<label for="'.$row.'Timer">'.$row.'Timer (in seconds)</label>
												<input id="'.$row.'Timer" type="Number" step="1" name="Currencies['.$row.'][Timer]" value="'.$options['Timer'].'">
											</div>
											<div class="form-group col-md-4">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="PayoutCycle of '.$row.'. (how many reloads between withdraws)">help_outline</i>
												<label for="'.$row.'Payout Cycle">'.$row.' Payout Cycle</label>
												<input id="'.$row.'Payout Cycle" type="Number" step="1" name="Currencies['.$row.'][PayoutCycle]" value="'.$options['PayoutCycle'].'">
											</div>
											<div class="col-12">
												<a href="'.DIRREC.'/admin.php?DeleteCurrency='.$row.'" class="btn">Delete '.$row.'</a>
												<hr>
											</div>
										</div>';
								}
							}else{ // let the user know there arent any currencies
								echo 'No Currencies Added.<br><button type="button" class="btn" data-toggle="modal" data-target="#AddCurrencies">Add Currency</button>';
							}
								 ?>
							</div>
						</div>
						<?php if($SetupStage === 2){ // if the user has set up atleast 1 currency (required for the script to work) we display additional config options ?><div id="Shortlinks" class="tab-pane fade" data-parent="#settings">
							<div class="col-12">
								<h4>Shortlink Settings</h4>
								<hr>
								<p>Shortlinks are a great Revenue soruce that allow you to pay your users more while earning more yourself! these services usually show the user a few ads before letting them pass through, after which they pay you for sendign that user. the Script Fully Supports Shortlinks and Allows you to offer them for a higher payout. you can add new shortlinks, or edit their details below<hr>New to shortlinks? you can find a list of reliable shortlinks <a href="https://randomsatoshi.win/Guides/Shortlinks.php" target="_blank">here</a><br><button type="button" class="btn" data-toggle="modal" data-target="#AddShortlinks">Add Shortlink</button></p>
								<?php
								if(!empty($Config['Shortlinks'])){ // iterate over Shortlinks
								foreach ($Config['Shortlinks'] as $row => $options) {
									$ShortenerName = ShortenerName($options['Apilink']); // Use the shortener name function to make things easier.
								         echo'
										<div class="row">
											<div class="col-12 col-sm-12">
												<div class="row">
													<div class="col-3 col-sm-3">
														<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="the Api url is used to create new shortlinks for this shortener. without it the script cannot work.">help_outline</i>
														<label for="'.$ShortenerName.'Link">'.$ShortenerName.' APIlink</label>
													</div>
													<div class="col-8 col-sm-8">
														<input id="'.$ShortenerName.'Link" type="text" name="Shortlinks['.$ShortenerName.'][Apilink]" '.(isset($options['Apilink']) ? 'value="'.$options['Apilink'].'"': "").'>
													</div>
												</div>
											</div>
											<div class="form-group col-md-6">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Ammount of Views '.$ShortenerName.'  counts per IP">help_outline</i>
												<label for="'.$ShortenerName.'Viewcount">'.$ShortenerName.' Viewcount</label>
												<input id="'.$ShortenerName.'Viewcount" type="Number" step="1" name="Shortlinks['.$ShortenerName.'][Viewcount]" value="'.$options['Viewcount'].'">
											</div>
											<div class="form-group col-md-6">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="the Ranking place of '.$ShortenerName.' ">help_outline</i>
												<label for="'.$ShortenerName.'Rank">'.$ShortenerName.' Rank</label>
												<input id="'.$ShortenerName.'Rank" type="Number" step="1" name="Shortlinks['.$ShortenerName.'][Rank]" value="'.$options['Rank'].'">
											</div>
											<div class="col-12">
												<a href="'.DIRREC.'/admin.php?DeleteShortener='.$ShortenerName.'" class="btn">Delete '.$ShortenerName.'</a>
												<hr>
											</div>
										</div>';
								}
							}else{
								echo 'No Shortlinks Added.';
							}
								 ?>
							</div>
						</div>
						<div id="Advertisements" class="tab-pane fade" data-parent="#settings">
							<div class="col-12">
								<h4>Advertisements</h4>
								<hr>
								<p>Advertisements can be a great way to fund your site, the script has the ability to control your ads from the admin panel. so switching out ads is as easy as it gets! if you're new to advertisements, <a href="http://ads.japakar.com/">here</a>'s a list of adnetworks you can try out<hr> the script lets you configure 2 sets of advertisements: one for normal pages, and a special set just for the claimpage. this is because most advertisers will dislike auto reload pages. so we reccomend filling this second set with referral banners instead.</p>
								<ul class="nav nav-tabs nav-justified">
									<li class="nav-item">
										<a class="nav-link active" data-toggle="tab" href="#MainSet">Main Ads</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" data-toggle="tab" href="#ClaimSet">Claim Ads</a>
									</li>
								</ul>
								<div class="tab-content">
									<div class="tab-pane container active" id="MainSet">
										<div class="row">
											<p>The main Set is displayed on all pages aside from the claim page. so we reccomend putting ads from advertisers here that shouldnt be automatically reloaded.</p>
										</div>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Leaderboard ads are displayed near the top and bottom of the page, Leaderboard Ads are typically sized 728x90, therefore they're only displayed on larger screens">help_outline</i>
												<label for="MainLeaderboardTop">Leaderboard Top</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainLeaderboardTop" type="text" name="Ads[MainLeaderboardTop]" ><?php echo (isset($Config['Ads']['MainSet']['LeaderboardTop']) ?  base64_decode($Config['Ads']['MainSet']['LeaderboardTop']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Leaderboard ads are displayed near the top and bottom of the page, Leaderboard Ads are typically sized 728x90, therefore they're only displayed on larger screens">help_outline</i>
												<label for="MainLeaderboardBottom">Leaderboard Bottom</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainLeaderboardBottom" type="text" name="Ads[MainLeaderboardBottom]" ><?php echo (isset($Config['Ads']['MainSet']['LeaderboardBottom']) ?  base64_decode($Config['Ads']['MainSet']['LeaderboardBottom']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Banner ads are displayed near the top and bottom of the page, Banner Ads are typically sized 468x60, therefore they're only displayed on medium screens in the place of Leaderboard Ads">help_outline</i>
												<label for="MainBannerTop">Banner Top</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainBannerTop" type="text" name="Ads[MainBannerTop]" ><?php echo (isset($Config['Ads']['MainSet']['BannerTop']) ?  base64_decode($Config['Ads']['MainSet']['BannerTop']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Banner ads are displayed near the top and bottom of the page, Banner Ads are typically sized 468x60, therefore they're only displayed on medium screens in the place of Leaderboard Ads">help_outline</i>
												<label for="MainBannerBottom">Banner Bottom</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainBannerBottom" type="text" name="Ads[MainBannerBottom]" ><?php echo (isset($Config['Ads']['MainSet']['BannerBottom']) ?  base64_decode($Config['Ads']['MainSet']['BannerBottom']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Square ads are displayed near the top and bottom of the page, Square Ads are typically sized 250x250 or 300x250, therefore they're only displayed on small screens in the place of Leaderboard and Square Ads">help_outline</i>
												<label for="MainSquareTop">Square Top</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainSquareTop" type="text" name="Ads[MainSquareTop]" ><?php echo (isset($Config['Ads']['MainSet']['SquareTop']) ?  base64_decode($Config['Ads']['MainSet']['SquareTop']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Square ads are displayed near the top and bottom of the page, Square Ads are typically sized 250x250 or 300x250, therefore they're only displayed on small screens in the place of Leaderboard and Square Ads">help_outline</i>
												<label for="MainSquareBottom">Square Bottom</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainSquareBottom" type="text" name="Ads[MainSquareBottom]" ><?php echo (isset($Config['Ads']['MainSet']['SquareBottom']) ?  base64_decode($Config['Ads']['MainSet']['SquareBottom']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Skyscraper Ads are typically placed on the side of the pages. Skyscraper ads are typically Sized 120x600 or 160x600 and wont be displayed on small screens.">help_outline</i>
												<label for="MainSkyscraperTop">Skyscraper Left</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainSkyscraperLeft" type="text" name="Ads[MainSkyscraperLeft]" ><?php echo (isset($Config['Ads']['MainSet']['SkyscraperLeft']) ?  base64_decode($Config['Ads']['MainSet']['SkyscraperLeft']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Skyscraper Ads are typically placed on the side of the pages. Skyscraper ads are typically Sized 120x600 or 160x600 and wont be displayed on small screens.">help_outline</i>
												<label for="MainSkyscraperRight">Skyscraper Right</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainSkyscraperRight" type="text" name="Ads[MainSkyscraperRight]" ><?php echo (isset($Config['Ads']['MainSet']['SkyscraperRight']) ?  base64_decode($Config['Ads']['MainSet']['SkyscraperRight']) : "")?></textarea>
											</div>
										</div><hr>
									</div>
									<div class="tab-pane fade container " id="ClaimSet">
										<div class="row">
											<p>The Claim Set is displayed only on the Claimpage.. so we reccomend putting Referral banners instead of Advertisements here as alot of advertisers may disable your account for using an automatic reload on your site.</p>
										</div>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Leaderboard ads are displayed near the top and bottom of the page, Leaderboard Ads are typically sized 728x90, therefore they're only displayed on larger screens">help_outline</i>
												<label for="ClaimLeaderboardTop">Leaderboard Top</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimLeaderboardTop" type="text" name="Ads[ClaimLeaderboardTop]" ><?php echo (isset($Config['Ads']['ClaimSet']['LeaderboardTop']) ?  base64_decode($Config['Ads']['ClaimSet']['LeaderboardTop']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Leaderboard ads are displayed near the top and bottom of the page, Leaderboard Ads are typically sized 728x90, therefore they're only displayed on larger screens">help_outline</i>
												<label for="ClaimLeaderboardBottom">Leaderboard Bottom</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimLeaderboardBottom" type="text" name="Ads[ClaimLeaderboardBottom]" ><?php echo (isset($Config['Ads']['ClaimSet']['LeaderboardBottom']) ?  base64_decode($Config['Ads']['ClaimSet']['LeaderboardBottom']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Banner ads are displayed near the top and bottom of the page, Banner Ads are typically sized 468x60, therefore they're only displayed on medium screens in the place of Leaderboard Ads">help_outline</i>
												<label for="ClaimBannerTop">Banner Top</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimBannerTop" type="text" name="Ads[ClaimBannerTop]" ><?php echo (isset($Config['Ads']['ClaimSet']['BannerTop']) ?  base64_decode($Config['Ads']['ClaimSet']['BannerTop']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Banner ads are displayed near the top and bottom of the page, Banner Ads are typically sized 468x60, therefore they're only displayed on medium screens in the place of Leaderboard Ads">help_outline</i>
												<label for="ClaimBannerBottom">Banner Bottom</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimBannerBottom" type="text" name="Ads[ClaimBannerBottom]" ><?php echo (isset($Config['Ads']['ClaimSet']['BannerBottom']) ?  base64_decode($Config['Ads']['ClaimSet']['BannerBottom']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Square ads are displayed near the top and bottom of the page, Square Ads are typically sized 250x250 or 300x250, therefore they're only displayed on small screens in the place of Leaderboard and Square Ads">help_outline</i>
												<label for="ClaimSquareTop">Square Top</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimSquareTop" type="text" name="Ads[ClaimSquareTop]" ><?php echo (isset($Config['Ads']['ClaimSet']['SquareTop']) ?  base64_decode($Config['Ads']['ClaimSet']['SquareTop']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Square ads are displayed near the top and bottom of the page, Square Ads are typically sized 250x250 or 300x250, therefore they're only displayed on small screens in the place of Leaderboard and Square Ads">help_outline</i>
												<label for="ClaimSquareBottom">Square Bottom</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimSquareBottom" type="text" name="Ads[ClaimSquareBottom]" ><?php echo (isset($Config['Ads']['ClaimSet']['SquareBottom']) ?  base64_decode($Config['Ads']['ClaimSet']['SquareBottom']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Skyscraper Ads are typically placed on the side of the pages. Skyscraper ads are typically Sized 120x600 or 160x600 and wont be displayed on small screens.">help_outline</i>
												<label for="ClaimSkyscraperTop">Skyscraper Left</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimSkyscraperLeft" type="text" name="Ads[ClaimSkyscraperLeft]" ><?php echo (isset($Config['Ads']['ClaimSet']['SkyscraperLeft']) ?  base64_decode($Config['Ads']['ClaimSet']['SkyscraperLeft']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Skyscraper Ads are typically placed on the side of the pages. Skyscraper ads are typically Sized 120x600 or 160x600 and wont be displayed on small screens.">help_outline</i>
												<label for="ClaimSkyscraperRight">Skyscraper Right</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimSkyscraperRight" type="text" name="Ads[ClaimSkyscraperRight]" ><?php echo (isset($Config['Ads']['ClaimSet']['SkyscraperRight']) ?  base64_decode($Config['Ads']['ClaimSet']['SkyscraperRight']) : "")?></textarea>
											</div>
										</div><hr>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					<div class="col-12 col-sm-12">
						<input type="submit" class="btn w-100" value="Submit Form">
					</div>
					</div>
					</form>

					<div class="col-12 col-sm-12 SecondLayer">
						<h4>Keeping the script Snappy</h4>
						<p>the more users use your site, the more data the script collects. it saves progress for the user and saves additional data for specific functions of the script (shortlinks, IPhub Checking, Apilimit logging). the more data the script saves the longer it takes to look through - so its reccomended to frequently clean out any unused/outdated data. the script gives an automated option to do this for you and it can be triggered manually by the button below or by setting up a cronjob.</p><a class="btn" href="<?php echo DIRREC ?>/GC_SCRIPT.php"> Clean up Data</a><hr> <code class="ThirdLayer">setting up a cronjob to clean up script data?<br>
						simply call this file in your cronjob: <?php echo DIRREC."/GC_SCRIPT.php?SecurityKey=".SECKEY; ?><br>
						if you're not sure how to set up cronjobs, you can contact your hosting provider for more infos
						</code>
					</div>

					<div class="modal fade" id="AddCurrencies">
						<div class="FirstLayer modal-dialog modal-dialog-center modal-lg">
							<div class="modal-content card SecondLayer">
								<form action="admin.php" method="POST">
								<h4>Add A Currency</h4>
								<p>add a new currency and configure it below! remember that submitting this form wont save any changes outside of it.<br>NOTE: if you havent added a single currency yet you may see all currencies at first. once you add a currency with a valid APIkey you will see all Currencies EC offers</p>
								<div class="row">
									<div class="col-12 col-sm-12">
										<select id="currencySelect" name="currency" class="w-75">
											<?php
												foreach($Currencies as $Currency){ // iterate over all currencies EC supports, skip those active in the script
													if(isset($Config['Currencies'][$Currency])){
														continue;
													} // make all other currencies an option to include later.
													echo '<option value="'.$Currency.'">'.$Currency.'</option>';
												}
											?>
										</select>
									</div><hr>
									<input type="hidden" name="AddCurrency" value="1">
									<div class="col-12 col-sm-12">
										<div class="row">
											<div class="col-3 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Apikey for your currency NOTE: make sure this is a valid key, as any invalid APIkey will cause the form to be rejected.">help_outline</i>
												<label for="Apikey">Apikey</label>
											</div>
											<div class="col-3 col-sm-8">
												<input class="w-100 float-left" id="Apikey" type="Text" name="Apikey" required>
											</div>
										</div><hr>
									</div>
									<div class="col-12 col-sm-12">
										<div class="row">
											<div class="col-3 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The UserToken for your Faucet. Propably used for Tracking or something">help_outline</i>
												<label for="UserToken">UserToken</label>
											</div>
											<div class="col-3 col-sm-8">
												<input class="w-100 float-left" id="UserToken" type="Text" name="UserToken" required>
											</div>
										</div><hr>
									</div>
									<div class="form-group col-md-4">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Ammount of satoshi the user gets per reload.">help_outline</i>
										<label for="Ammount">Ammount (in satoshi)</label>
										<input id="Ammount" type="Number" step="0.001" name="Ammount">
									</div>
									<div class="form-group col-md-4">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Timer betweem reloads">help_outline</i>
										<label for="Timer">Timer (in seconds)</label>
										<input id="Timer" type="Number" step="1" name="Timer">
									</div>
									<div class="form-group col-md-4">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Payout Cycle  (how many reloads between withdraws)">help_outline</i>
										<label for="Payout Cycle">Payout Cycle</label>
										<input id="Payout Cycle" type="Number" step="1" name="PayoutCycle">
									</div>
									<div class="col-md-12">
										<input type="submit" class="btn" value="Submit form">
									</div>
								</div>
							</form>
							</div>
						</div>
					</div>
					<div class="modal fade" id="AddShortlinks">
						<div class="FirstLayer modal-dialog modal-dialog-center modal-lg">
							<div class="modal-content card SecondLayer">
								<form action="admin.php" method="POST">
								<h4>Add A Shortlink</h4>
								<p>Adding a Shortlink is as simple as it gets! simply go to the Developer API tab of your shortlink provider (Tools > Developer API) and select the link that looks something like this:<br> <code>https://domain.tld/api?api=dc6688c6402af072b2c879d85624i6j1ad</code>. paste it into the field below and configure the amount of views for this shortener! </p>
								<div class="row">
									<div class="col-12 col-sm-12">
										<div class="row">
											<div class="col-3 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The API Link for this shortener. NOTE: make sure to insert it properly as this will be validated! if we cant validate this shortener this form will be denied ">help_outline</i>
												<label for="Apilink">Apilink</label>
											</div>
											<div class="col-3 col-sm-8">
												<input class="w-100 float-left" id="Apilink" type="Text" name="Apilink" required>
											</div>
										</div><hr>
									</div>
									<input type="hidden" name="AddShortlink" value="1">
									<div class="col-12 col-sm-6">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Amount of views per IP per 24 hours the shortener accepts. if you're not sure simply set this to 1">help_outline</i>
										<label for="Viewcount">Viewcount</label>
										<input class="w-50" id="Viewcount" type="Number" step="1" name="Viewcount" value="1">
									</div>
									<div class="col-12 col-sm-6">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The place in the order of how your shortlinks will appear. this cannot be set when adding a shortener.">help_outline</i>
										<label for="Rank">Shortener Rank</label>
										<input class="w-50" disabled id="Rank" type="Number" step="1" name="Rank">
									</div>

									<div class="col-md-12">
										<input type="submit" class="btn" value="Submit form">
									</div>
								</div>
							</form>
							</div>
						</div>
					</div>




				</div>
			</div>
			<div class="col-12 col-md"></div>
		</div>
<?php
	include FDSR.DIRECTORY_SEPARATOR."/footer.php"; // include the standard footer
	ob_end_flush(); // flush the output buffer and send user the page - solves issues with a few hosters.

	//finally done commenting this. good lucky making any changes to this future kull lmao
 ?>

	}
}

if(!empty($_POST)){ // check if a form was submitted. all changes are made via forms, so most of the logic on this page does exactly that.
	// this if block only Handles the login process and initial Admin Credential process
	if(isset($_POST['CreateCredentials'])){ // check if its the "CreateCredentials" Form
		if(@$Config['Useable'] === true){
			redirect(DIRREC."/"); //script isnt in the setup stage, so we exit the form to prevent brute-forcing the securitykey.
		}
		if(@$_SESSION['ADM_MAX_TRIES'] > 19 && @$_SESSION['ADM_LIMIT_SECKEY'] === SECKEY){ // check if the user spammed the admin login form and deny access if thats the case
			$_SESSION['ErrMSG'] =  "You've used the incorrect credentials too many times. you can reset this restriction by setting a different Security Key";
			redirect(DIRREC."/admin.php");
		}
		if(@$_SESSION['ADM_LIMIT_SECKEY'] !== SECKEY){ // if the admin changed the SecKey, reset limits
			$_SESSION['ADM_MAX_TRIES'] = 0;
			$_SESSION['ADM_LIMIT_SECKEY'] = SECKEY;
		}
		if(@$_POST['SecKey'] !== SECKEY){ //Check for Security key
			$_SESSION['ErrMSG'] =  "Wrong Security Key! this form is only for the admin. you can find and set your security key in the config.php file";
			redirect(DIRREC."/"); // notify the user if the key was wrong and redirect him back to the index page.
		}elseif(isset($_POST['Username']) && isset($_POST['Password'])){
			if(empty($Config)){ // set the new details in the config
				$Config = array("AdminCredentials" => array("Username" => $_POST['Username'], "Password" => $_POST['Password']), "Useable" => false);
			}else{
				$Config['AdminCredentials']['Username'] = $_POST['Username'];
				$Config['AdminCredentials']['Password'] = $_POST['Password'];
			}

			UpdateConfig($Config); // Update the config file to reflect the changes
			$_SESSION['ADM_IS_LOGGED_IN'] = true; // automatically login the user
		}else{
			$_SESSION['ErrMSG'] =  "Incomplete Details, make sure to fill out both the Username & Password";
			redirect(DIRREC."/"); // notify the user if the details were incomplete and redirect back to index
		}
	}elseif(isset($_POST['AdminLogin'])){
		if(@$_SESSION['ADM_MAX_TRIES'] > 19 && @$_SESSION['ADM_LIMIT_SECKEY'] === SECKEY){ // check if the user spammed the admin login form and deny access if thats the case
			$_SESSION['ErrMSG'] =  "You've used the incorrect credentials too many times. you can reset this restriction by setting a different Security Key";
			redirect(DIRREC."/admin.php");
		}
		if(@$_SESSION['ADM_LIMIT_SECKEY'] !== SECKEY){ // if the admin changed the SecKey, reset limits
			$_SESSION['ADM_MAX_TRIES'] = 0;
			$_SESSION['ADM_LIMIT_SECKEY'] = SECKEY;
		}
		if(@$_POST['Username'] === $Config['AdminCredentials']['Username'] && @$_POST['Password'] === $Config['AdminCredentials']['Password']){
			$_SESSION['ADM_IS_LOGGED_IN'] = true; // the user gave the correct credentials, so we let him in.
			$_SESSION['ADM_MAX_TRIES'] = 0;
			redirect(DIRREC."/admin.php"); // refresh the page so that the page uses the new config
		}else{ // the user gave invalid credentials, so we increment the ammount of failed tries.
			if(isset($_SESSION['ADM_MAX_TRIES'])){
				$_SESSION['ADM_MAX_TRIES']++;
			}else{
				$_SESSION['ADM_MAX_TRIES'] = 1;
				$_SESSION['ADM_LIMIT_SECKEY'] = SECKEY;
			}
		}
	}
}
if(empty($Config)){ // check if the config exists and is ready to be used. if its not useable, return the user to the index file
	redirect(DIRREC."/");
}
/* ==============================================================================================
 * """"" MAIN SECTION: DISPLAYS LOGIN FORM OR ADMIN PANEL IF LOGGED IN                      =====
 * ==============================================================================================
*/

if(@$_SESSION['ADM_IS_LOGGED_IN'] !== true){
include FDSR.DIRECTORY_SEPARATOR."/header.php"; // include the standard header
	echo '
		<div class="row justify-content-center container-fluid FirstLayer">
			<div class="col-12 col-md"></div>
			<div class="col-12 col-md-8  mx-auto text-center">
				<h1>Login to the admin panel</h1>
				<div class="card col-12 col-sm-6 offset-sm-3 SecondLayer">
					<form method="POST" action="'.DIRREC.'/admin.php">
						<input type="text" name="Username" placeholder="Username"><br>
						<input type="password" name="Password" placeholder="Password"><br>
						<input type="hidden" name="AdminLogin" value=true>
						<input type="submit" value="Login" class="btn">

					</form>
				</div>
				<blockquote> if you ever forget your admin credentials, you can find them in the config.php file!
				</blockquote>
			</div>
			<div class="col-12 col-md"></div>
		</div>';
		include FDSR.DIRECTORY_SEPARATOR."/footer.php"; // include the standard footer
		exit;
}

$Date = date("d-m"); // get the date in a day-month format
$LimitLog = @file_get_contents(FDSR.DIRECTORY_SEPARATOR."Data".DIRECTORY_SEPARATOR."APIlimit.json");
$LimitLog = @json_decode($LimitLog, true); // load the limitlog and make it parseable by php
if($LimitLog === null){ //if the limit log is empty, we  assume theres no data about any limits being reached
	$LimitLog = array();
}
//big form processing code,
if(!empty($_POST)){ // check if a form was submitted. all changes i made via forms, so this if block is BIG. alot of code all relevant to updating the config
	$_POST = r_array_map("intifyArray", $_POST); // give integers and floats its correct type to check against later. HTTP POST requeest send all data as Strings by default. so this is required to check for numbers.
	if(isset($_POST['MainForm'])){ // check if its the "MainAForm" Form
		unset($_POST['MainForm']); //remove the key so it doesnt get added to the config, its useless beyond this point anyways
		$ChangesMade = array(); // initiallize the array to keep track of all changes.
		foreach($_POST as $key => $row){
			if($key === "Currencies"){ // fetch all changes in regards to currencies
				foreach($row as $currency => $options){ //itterate over all currencies in the form
					if(!isset($Config['Currencies'][$currency])){continue;} //if the currency submitted is not an active currency, we skip it.
					$CurrInfo = $Config['Currencies'][$currency]; // duplicate the current configuration to check against later
					if(isset($options['Apikey']) && isset($options['Ammount']) && isset($options['Timer']) && isset($options['PayoutCycle'])){
						//check if all the required values are there, otherwhise we skip this currency
						if((is_int($options['PayoutCycle']) && is_int($options['Timer']) && (is_int($options['Ammount']) || is_float($options['Ammount'])) && CharCheck($options['Apikey']))){
							//Check if all values are in a valid format
							if($options['Ammount'] !== $CurrInfo['Ammount'] || $options['Timer'] !== $CurrInfo['Timer'] || $options['Apikey'] !== $CurrInfo['Apikey'] || $options['PayoutCycle'] !== $CurrInfo['PayoutCycle']){
								//Check if any of the values changed compared to the last config
								if($options['Ammount'] !== $CurrInfo['Ammount'] || $options['PayoutCycle'] !== $CurrInfo['PayoutCycle']){
									$CurrInfo['ConfigID']++;
								} // explicitly change the ConfigID if the ammount and/or payout cycle change. this causes user progress to reset as it would otherwhise massively screw up payouts.
								$Config['Currencies'][$currency] = array("Apikey" => $options['Apikey'],"UserToken" => $options['UserToken'], "Timer" => $options['Timer'], "Ammount" => $options['Ammount'], "PayoutCycle" => $options['PayoutCycle'], "ConfigID" => $CurrInfo['ConfigID']); // make the change
								foreach($options as $key => $val){ // go over all the changes. and log everything that changed.
									if($val !== $CurrInfo[$key]){
										$ChangesMade[] = '<b>'.$currency.'</b>: '.$key.' changed from <b>"'.$CurrInfo[$key].'"</b> to <b>"'.$val.'"</b>';
									}
								}
							}
						}
					}
				}
			}elseif($key === "Shortlinks"){ // fetch all changes regarding shortlinks
				foreach($row as $Shortener => $options){ // iterate over all shorteners.
					if(!isset($Config['Shortlinks'][$Shortener])){continue;} // skip shorteners that dont exist
					$ShortInfo = $Config['Shortlinks'][$Shortener]; //duplicate the current configuration to check against later
					if(isset($options['Apilink']) && isset($options['Viewcount']) && isset($options['Rank'])){
						// check if all required fiels are there
						if((is_int($options['Viewcount']) && is_int($options['Rank']))){ // check if RANK and VIEWCOUNT are integers
							if($options['Apilink'] !== $ShortInfo['Apilink'] || $options['Viewcount'] !== $ShortInfo['Viewcount'] || $options['Rank'] !== $ShortInfo['Rank']) {
								// check if anything changed from the last config
								$Config['Shortlinks'][$Shortener] = array("Apilink" => $options['Apilink'], "Viewcount" => $options['Viewcount'], "Rank" => $options['Rank'], "ShortUID" => $ShortInfo['ShortUID']); // make the change
								foreach($options as $key => $val){// go over all the changes, and log everything that changed
									if($val !== $ShortInfo[$key]){
										$ChangesMade[] = '<b>'.$Shortener.'</b>: '.$key.' changed from <b>"'.$CurrInfo[$key].'"</b> to <b>"'.$val.'"</b>';
									}
								}
							}
						}
					}
				}
				uasort($Config['Shortlinks'], function($a, $b){//Re-align Shorteners based on rank
					if ($a['Rank'] == $b['Rank']) {
						return 0;
					}
					return ($a['Rank'] < $b['Rank']) ? -1 : 1;
				});
			}elseif($key === "Ads"){ // fetch all changes regarding ads
				if(!isset($Config['Ads'])){
					$Config['Ads'] = array("MainSet" => array(), "ClaimSet" => array());
				}
				foreach($row as $AdKey => $AdVal){ // iterate over all adspaces
					$AdVal = base64_encode($AdVal);
					if(contains("Main", $AdKey)){ // check if adspot is in the MainSet
						$MainAdKey = str_replace("Main","",$AdKey); //Modify the Key
						if(@$Config['Ads']['MainSet'][$MainAdKey] !== $AdVal){ // check if the value changed
						$ChangesMade[] = '<b>Main Ads Set</b>: '.$MainAdKey.' changed from <b>"'.base64_decode(@$Config['Ads']['MainSet'][$MainAdKey]).'"</b> to <b>"'.base64_decode($AdVal).'"</b>';
							$Config['Ads']['MainSet'][$MainAdKey] = $AdVal; //change it and log it
						}
					}elseif(contains("Claim", $AdKey)){// check if adspot is in the ClaimSet
						$ClaimAdKey = str_replace("Claim","",$AdKey);//Modify the key
						if(@$Config['Ads']['ClaimSet'][$ClaimAdKey] !== $AdVal){// check if the value changed
						$ChangesMade[] = '<b>Claim Ads Set</b>: '.$ClaimAdKey.' changed from <b>"'.base64_decode(@$Config['Ads']['ClaimSet'][$ClaimAdKey]).'"</b> to <b>"'.base64_decode($AdVal).'"</b>';
							$Config['Ads']['ClaimSet'][$ClaimAdKey] = $AdVal; // change it and log it
						}
					}
				}
			}elseif($key === "Captchas"){ // fetch all changes regarding captchas.
				foreach($row as $Ckey => $Cval){ // itterate over all captcha keys
					if($Cval !== @$Config['Captchas'][$Ckey] && (CharCheck($Cval) || empty($Cval))){ // check if the values are legit and different
						$ChangesMade[] = '<b>Captchas</b>: '.$Ckey.' changed from <b>"'.@$Config['Captchas'][$Ckey].'"</b> to <b>"'.$Cval.'"</b>';
						$Config['Captchas'][$Ckey] = $Cval; // change it and log it
					}
				}
				if(!empty(@$Config['Captchas'])){
					if(empty(@$Config['Captchas']['ReCaptchaWebsitekey']) && empty(@$Config['Captchas']['ReCaptchaSecretkey']) && empty(@$Config['Captchas']['HCaptchaWebsitekey"']) && empty(@$Config['Captchas']['HCaptchaSecretkey'])){// if no values for captchas are set, remove the key completely to disable captchas.
						unset($Config['Captchas']);
					}
				}

			}else{
				if(!in_array($key, array("Sitename","Domain","CustomCSS","IPhubKey"))){ // check for any key not in that array
					if(!CharCheck($row)){continue;} // validate it and skip it if it contains bad characters
				}
				if($key === "RefCommission" && !is_int($row) && ($row <= 100 && $row >= 0)){// make sure RefCommission is an integer and between 0 and 100
					$row = 0;
				}
				if($key === "CustomCSS"){
					$row = base64_encode($row); // base64 encode the CSS becuase it has characters JSON doesnt handle great - at all
				}
				if($key === "Sitename"){
					if($row == ""){
						$row = "Autofaucet";
					}
				}
				if($key === "Domain"){
					$UrlArray = parse_url("http://".$row); // convert the url into an array containing each part of the url
					if(!empty($UrlArray['host'])){
						$row = $UrlArray['host'];// split the domain into various parts and only use the host part
					}else{
						continue;// the value supplied was invalid, so we dont make a change.
					}
				}

				if($key === "IPhubKey" && !function_exists("curl_init")){ // check if PHP curl is enabled, otherwhise skip the IPhubKey
					$ChangesMade[] = '<b>NOTE:</b>: Couldnt activate IPhub because your server has cURL disabled! please enable the PHP curl extension and try again</b>';
					continue;
				}
				if($row !== @$Config[$key]){// if value changed
					if($key === "CustomCSS"){
						$ChangesMade[] = '<b>General info</b>: '.$key.' changed from <b>"'.@base64_decode($Config[$key]).'"</b> to <b>"'.base64_decode($row).'"</b>';
					}else{
						$ChangesMade[] = '<b>General info</b>: '.$key.' changed from <b>"'.@$Config[$key].'"</b> to <b>"'.$row.'"</b>';
					}
					$Config[$key] = $row; // change it and log it
				}
			}
		}
	//Create message displaying Changes
	$_SESSION['SuccessMSG'] = " Success! you've made the following changes:<br>";
	foreach($ChangesMade as $change){//iterate over changes
		$_SESSION['SuccessMSG'] = $_SESSION['SuccessMSG'].$change."<br>"; // add Changes to the message.
	}
	UpdateConfig($Config);//update Config
}elseif(isset($_POST['AddCurrency'])){ // check if its the "addCurrency" form
		if(isset($_POST['PayoutCycle']) && isset($_POST['Timer']) && isset($_POST['Ammount']) && isset($_POST['Apikey']) && isset($_POST['UserToken']) && isset($_POST['currency'])){
			//check of all needed values are there
			if(is_int($_POST['PayoutCycle']) && is_int($_POST['Timer']) && (is_int($_POST['Ammount']) || is_float($_POST['Ammount'])) && CharCheck($_POST['Apikey'])){
				//check if all values are vaid types
				$res = CheckCurrencies($_POST['Apikey'], $_POST['UserToken'], $_POST['currency']); // Check if EC Supports the currency
				if ($res['success'] === true) { // check if the call worked.
		           	if($_POST['currency']){
						if(in_array($_POST['currency'], $Config['Currencies'])){ // check if hte currency is allready active in the script
							$_SESSION['ErrMSG'] = 'Allready active. you wont need to add a currency more than once.';
							redirect(DIRREC."/admin.php");
						}
					}
					$Config['Currencies'][$_POST['currency']] = array("PayoutCycle" => $_POST['PayoutCycle'], "Timer" => $_POST['Timer'], "Ammount" => $_POST['Ammount'], "Apikey" => $_POST['Apikey'], "UserToken" => $_POST['UserToken'], "ConfigID" => 1); // make the change and notify the admin
					$_SESSION['SuccessMSG'] = 'Success! your currency has been added and can now be used by users!';
					if($Config['Useable'] === false){ // if the script was previously unuseable, set it to useable so it can handle users
						$Config['Useable'] = true;
						$_SESSION['SuccessMSG'] = $_SESSION['SuccessMSG']." your script can now handle users!";
					}
					$image = @file_get_contents('https://expresscrypto.io/images/'.strtolower($_POST['currency']).'.png');
					if($image !== false){ // get the image from ECs Server and put it into the images folder so the script can use it
                    	@file_put_contents(FDSR.DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR.$_POST['currency'].".png", $image);
					}
					UpdateConfig($Config); // update the config
		        } else {
					if($res['data']['status'] === 403){ // the API call returned with an invalid key error, so we notify the admin
						$_SESSION['ErrMSG'] = 'Invalid Apikey. please make sure to copy the key correctly. if you have IP access limits please add the IPaddress of this server to your account';
						redirect(DIRREC."/admin.php");
					}elseif($res['data']['status'] === 415){ // some other Error happened, so we let the admin know
						$_SESSION['ErrMSG'] = 'Invalid Currency. please make sure to use the Shorthand of the currency (Bitcoin = BTC etc)';
						redirect(DIRREC."/admin.php");
					}else{
						$_SESSION['ErrMSG'] = 'EC Responded with an error: '. $res['data']['message'];
						redirect(DIRREC."/admin.php");
					}
		        }
			}else{ // let the admin know his data was invalid
				$_SESSION['ErrMSG'] = 'Invalid Data, please make sure to use the form inside the modal';
				redirect(DIRREC."/admin.php");
			}
		}else{ // let the admin know he forgot to fill out one or more fields
			$_SESSION['ErrMSG'] = 'Incomplete Data. please make sure to fill out all forms';
			redirect(DIRREC."/admin.php");
		}
	}elseif(isset($_POST['AddShortlink'])){ // check if its the "addShortlink" form
		if(isset($_POST['Apilink']) && isset($_POST['Viewcount'])){ // check if Apilink and Viewcount are sent
			$UrlArray = parse_url($_POST['Apilink']); // convert the url into an array containing each part of the url
			if(!empty($UrlArray['scheme']) && !empty($UrlArray['host']) && !empty($UrlArray['path']) && !empty($UrlArray['query'])){
				//check if there are any parts of the url missing
				if(isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']){ // determine if the script uses HTTPS
					$Scheme = "https://";
				}else{
					$Scheme = "http://";
				}
				$ShortenedResult = @json_decode(file_get_contents($_POST['Apilink']."&url=".urlencode($Scheme.$Config['Domain'].DIRREC)), true);
				if(isset($ShortenedResult['shortenedUrl'])){// try the API url and check if it responds with a valid json response
					if(isset($Config['Shortlinks'])){// check if there are previous shorteners or if this is a new shortener.
						$NextRank = @max(array_column($Config['Shortlinks'], 'Rank'))+1; // get the highest rank to give to the new shortener
						$Config['Shortlinks'][ShortenerName($_POST['Apilink'])] = array("Apilink" => stripslashes($_POST['Apilink']), "Viewcount" => (is_int($_POST['Viewcount']) ? $_POST['Viewcount'] : 1), "Rank" => $NextRank, "ShortUID" => RandomString(16));
						//add the new shortener to the script. this automatically overridesany previous
					}else{
						$Config['Shortlinks'] = array(ShortenerName($_POST['Apilink']) => array("Apilink" => $_POST['Apilink'], "Viewcount" => (is_int($_POST['Viewcount']) ? $_POST['Viewcount'] : 1), "Rank" => 1, "ShortUID" => RandomString(16)));
						// add the new shortener to the script.
					}
					$_SESSION['SuccessMSG'] = 'Succesfully added '.ShortenerName($_POST['Apilink']).' to your shortlink list. if you want to test it click <a href="'.$ShortenedResult['shortenedUrl'].'"> here</a>';
					UpdateConfig($Config); // notify the user and make the changes.
				}else{
					$_SESSION['ErrMSG'] = "the Shortlink Url was invalid, please make sure to copy it properly";
					redirect(DIRREC."/admin.php"); // got a bad response from the shortener, so we discard the shortener
				}
			}else{
				$_SESSION['ErrMSG'] = "the shortlink Url is invalid(Malformed), please make sure to copy it properly!";
				redirect(DIRREC."/admin.php");// the shortlink url was missing parts
			}
		}else{
			$_SESSION['ErrMSG'] = "Invalid data, please make sure to fill out all fields that arent disabled!";
			redirect(DIRREC."/admin.php");// the admin forgot to fill out some fields
		}
	}else{

	}
}



if(!empty($_GET)){ // get form block. used to delete shorteners and Currencies, as we barely need any data to delete a shortener
	$_GET = r_array_map("intifyArray", $_GET);// assign numbers and floats their correct type
	if(isset($_GET['DeleteCurrency'])){ // check if the user wanted to delete a currency
		if(isset($Config['Currencies'][$_GET['DeleteCurrency']])){ // check if its an active currency and delete it
			unset($Config['Currencies'][$_GET['DeleteCurrency']]);
			if(empty($Config['Currencies'])){ // check if the script has no active currencies, and let the user know that the script cant server users until he adds a new one
				$Config['Useable'] = false;
				$_SESSION['SuccessMSG'] = 'Succesfully Deleted '. $_GET['DeleteCurrency'].'! note that because the script now has no Active currencies the script state has been set to maintanance. to remove the script from maintanance add a currency.';
			}else{
				$_SESSION['SuccessMSG'] = 'Succesfully Deleted '. $_GET['DeleteCurrency'].'!';
			}

			UpdateConfig($Config); // update the config to save the changes.
		}else{
			$_SESSION['ErrMSG'] = 'the currency you tried to select is not an active currency.';
			redirect(DIRREC."/admin.php"); // notify the user that the currency was not active in the script.
		}
	}elseif(isset($_GET['DeleteShortener'])){ // check if the user wanted to delete a shortener.
		if(!isset($Config['Shortlinks'][$_GET['DeleteShortener']])){ // check if its an active shortener in the script.
			$_SESSION['ErrMSG'] = 'this is not an active shortener!';
			redirect(DIRREC."/admin.php");// deny the form and let the user know its not a valid shortener
		}
		unset($Config['Shortlinks'][$_GET['DeleteShortener']]);
		if(empty($Config['Shortlinks'])){ // unset the shortener. if the script has no active shorteners we remove the key entirely and let the user know that the shortlink function is disabled until he adds a new shortener.
			$_SESSION['SuccessMSG'] = 'Succesfully Deleted the shortener! this shortener was the only shortener configured in the script, so we disabled shortlinks for now. you can re-enable them once you add a new shortener';
		}else{
			$_SESSION['SuccessMSG'] = 'Succesfully Deleted the shortener!';
		}

		UpdateConfig($Config); // submit the changes.
	}
}



// the code below fetches data for the admin panel.


if(isset($_SESSION['ErrMSG'])){ // general Error message processing code
	$messages = $messages.ErrorMSG("clear", $_SESSION['ErrMSG']);
	unset($_SESSION['ErrMSG']);
}
if(isset($_SESSION['SuccessMSG'])){ // general sucess message processing code.
	$messages = $messages.SuccessMSG("clear", $_SESSION['SuccessMSG']);
	unset($_SESSION['SuccessMSG']);
}
$APIissues = 0; // initiate the ApiIssues variable to be used later.
if(isset($LimitLog[$Date])){ // check if there is an entry for today
	if($Limitlog[$Date]['LimitReached'] > 0){ // check if the script reached any api limits today.
		$APIissues = $Limitlog[$Date]['LimitReached'];
		if(isset($Limitlog[$Date]['CreditsEmpty'])){ // check if the error in question was related to api credits, if so we let the user know
			$messages = $messages.ErrorMSG("clear", "WARNING: the script has reached your API limits! <b>".$Limitlog[$Date]['LimitReached']."</b> APIcalls failed due to this limit today ;/ we reccomend to upgrade your plan to a higher plan or to extend the payout cycle for your currencies<br>NOTICE: you ran out of API credits on EC, this may lead to all further apicalls failing, so we reccomend you to buy more credits as soon as possible.");
		}else{ // otherwhise we simply notify him of the limits.
			$messages = $messages.ErrorMSG("clear", "WARNING: the script has reached your API limits! <b>".$Limitlog[$Date]['LimitReached']."</b> APIcalls failed due to this limit today ;/ we reccomend to upgrade your plan to a higher plan or to extend the payout cycle for your currencies");
		}
	}
}



//determine at which stage of script setup the owner is at
$SetupStage = 1;// initialize the variable with 1 to indicate that the script is not yet ready.
if(isset($Config['Currencies'])){
	$SetupStage = 2; // change the setup Stage if there is atleast one currency added.
}

include FDSR.DIRECTORY_SEPARATOR."/header.php"; // include the standard header
?>
<style>
Select ,input{
	font-size:1.6rem;
	border-radius:5px;
	padding:0px;
}
</style>
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>
		<div class="row justify-content-center container-fluid FirstLayer">
			<div class="col-12 col-md"></div>
			<div class="col-12 col-md-8  mx-auto text-center">
				<h1>Admin Panel!</h1>
				<div class="card col-12 SecondLayer">
					<h3>Script overview</h3>
					<?php echo $messages ?>
					<div class="row">
						<div class="col-4 card ThirdLayer"><h5>Currencies: <br><?php echo @count($Config['Currencies']) ?><br><span class="Smalltext">active</span></h5></div>
						<div class="col-4 card ThirdLayer"><h5>Shortlinks: <br><?php echo @count($Config['Shortlinks']) ?><br><span class="Smalltext">active</span></h5></div>
						<div class="col-4 card ThirdLayer"><h5>Issues: <br><?php if($APIissues !== 0){echo '<span class="text-danger">'.$APIissues.'</span>';}else{echo "0";} ?><br><span class="Smalltext">today</span></h5></div>
					</div>
					<h3>Configuration & Customization</h3>
					<div class="col-12 col-sm-12">
						<ul class="nav nav-tabs nav-justified">
							<li class="nav-item">
								<a class="nav-link active" data-toggle="tab" data-target="#GeneralInfo">General Info</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" data-target="#Currencies">Currencies</a>
							</li>
						<?php if($SetupStage === 2){ // if the user has set up atleast 1 currency (required for the script to work) we display additional config options ?>
							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" data-target="#Shortlinks">Shortlinks</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-toggle="tab" data-target="#Advertisements">Advertisements</a>
							</li>
						<?php }else{//hasnt set up a currency so we gray out the buttons and make them unuseable ?>
							<li class="nav-item">
								<a class="nav-link disabled" >Shortlinks<</a>
							</li>
							<li class="nav-item">
								<a class="nav-link disabled" >Advertisements</a>
							</li>
						<?php } ?>
						</ul>
					</div>
					<form action="admin.php" method="POST">
					<input type="hidden" name="MainForm" value="1">
					<div id="settings" class="row card SecondLayer justify-content-center tab-content">
						<div id="GeneralInfo" class="tab-pane active" data-parent="#settings">
							<div class="col-12 justify-content-left">
								<h4>General Settings</h4>
								<hr>
								<p>these are basic Settings that you can save below. you can set your script name, refferal percentage aswell as define a few things the script neeeds to function!</p>
								<div class="row">
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Sitename is purely for display, you may find it in the nav bar or on the start page, aswell as inside descriptive texts">help_outline</i>
										<label for="Sitename">Sitename</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="Sitename" type="text" name="Sitename" required <?php echo (isset($Config['Sitename']) ? 'value="'.$Config['Sitename'].'"': "")?>>
									</div>
								</div><hr>
								<div class="row">
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="the domain this script is used on. this is required as the script cant reliably find out the domain by itself. so it needs to be defined for shortlinks to work">help_outline</i>
										<label for="Domain">Domain</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="Domain" type="text" name="Domain" required <?php echo (isset($Config['Domain']) ? 'value="'.$Config['Domain'].'"': "")?>>
										<small>The Domain should follow this pattern: domain.tld or sub.domain.tld NO Slashes, no http:// or https:// and no folder needs to be added to the domain. simply (sub.)domain.tld - this is required to use the shortlink functionality</small>
									</div>

								</div><hr>
								<div class="row">
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Referral Commission rewards users for inviting frieds and other people to your site. if enabled, the users will get a link they can give to people to start earning referral rewards. you can set it to 0 to disabled referrals">help_outline</i>
										<label for="RefCommission">Referral commission (in %)</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-25 float-left" id="RefCommission" type="number" name="RefCommission" min=0 max=100 required <?php echo (isset($Config['RefCommission']) ? 'value="'.$Config['RefCommission'].'"': "")?>>
									</div>
								</div><hr>
								<div class="row">
									<div class="col-12 col-sm-12">
										<h5>Custom CSS</h5>
										<small>This Textfield allows you to override any default CSS defined by Bootstrap or the script. please make sure to only put CSS here, as the browser will parse all the information in this field as CSS</small>
									</div>
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Custom CSS can be used to easily change the colors of the site or change smaller things on the scripts layout! feel free to mess arround with it">help_outline</i>
										<label for="CustomCSS">CustomCSS</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<textarea class="w-100 float-left" id="CustomCSS" name="CustomCSS"><?php echo (isset($Config['CustomCSS']) ? base64_decode($Config['CustomCSS']): "")?></textarea>
									</div>
								</div><hr>
								<div class="row">
									<div class="col-12 col-sm-12">
										<h5>Captchas (optional)</h5>
										<small>You can choose to add ReCaptcha or Hcaptcha to your site to increase security. you can set up your Captcha Keys on <a href="https://www.google.com/recaptcha">ReCaptcha</a> and <a href="https://hcaptcha.com/?r=e5ad8e8181da">Solvemedia</a><br>NOTE: these captchas may result in some users leaving! so you may see less claims from valid users when activating a captcha. its reccomended to disable captchas when using a shortlink as they use captchas too resulting in the user having to solve a difficult captcha twice<br><br> leave the fields below blank to use no captchas!</small>
									</div>
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="if you include valid ReCaptcha Details you can add security to your script this website key is a public key thats used to load the captcha on the user's page">help_outline</i>
										<label for="ReCaptchaWebsitekey">ReCaptcha Websitekey</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="ReCaptchaWebsitekey" type="text" name="Captchas[ReCaptchaWebsitekey]" <?php echo (isset($Config['Captchas']['ReCaptchaWebsitekey']) ? 'value="'.$Config['Captchas']['ReCaptchaWebsitekey'].'"': "")?>>
									</div>
								</div>
								<div class="row">
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="the SecretKey is used to validate if a user completed a captcha. this should never be shown to the user">help_outline</i>
										<label for="ReCaptchaSecretkey">ReCaptcha Secretkey</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="ReCaptchaSecretkey" type="text" name="Captchas[ReCaptchaSecretkey]" <?php echo (isset($Config['Captchas']['ReCaptchaSecretkey']) ? 'value="'.$Config['Captchas']['ReCaptchaSecretkey'].'"': "")?>>
									</div>
								</div><hr>
								<div class="row">
									<div class="col-12 col-sm-12">
										<small>Hcaptcha is an alternative to Recaptcha that helps companies with AIresearch. the upside is that they pay for some traffic!</small>
									</div>
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="if you include valid HCaptcha Details you can add security to your script this website key is a public key thats used to load the captcha on the user's page">help_outline</i>
										<label for="HCaptchaWebsitekey">HCaptcha Websitekey</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="HCaptchaWebsitekey" type="text" name="Captchas[HCaptchaWebsitekey]" <?php echo (isset($Config['Captchas']['HCaptchaWebsitekey']) ? 'value="'.$Config['Captchas']['HCaptchaWebsitekey'].'"': "")?>>
									</div>
								</div>
								<div class="row">
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="the SecretKey is used to validate if a user completed a captcha. this should never be shown to the user">help_outline</i>
										<label for="HCaptchaSecretkey">HCaptcha Secretkey</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="HCaptchaSecretkey" type="text" name="Captchas[HCaptchaSecretkey]" <?php echo (isset($Config['Captchas']['HCaptchaSecretkey']) ? 'value="'.$Config['Captchas']['HCaptchaSecretkey'].'"': "")?>>
									</div>
								</div><hr>
								<div class="row">
									<div class="col-12 col-sm-12">
										<h5>IPhub (Optional)(Requires cURL)</h5>
										<small>IPhub is a Proxy Detection service offerign a free method of detecting bots. you can create an apikey <a href="https://iphub.info/" target="_blank">here!</a></small>
									</div>
									<div class="col-6 col-sm-3">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The IPhub key is used to Communicate with IPhub to help identify Proxies and Bots we reccomend to use IPhub when using Shortlinks!">help_outline</i>
										<label for="IPhubKey">IPHub Key</label>
									</div>
									<div class="col-6 col-sm-8 float-left">
										<input class="w-100 float-left" id="IPhubKey" type="text" name="IPhubKey" <?php echo (isset($Config['IPhubKey']) ? 'value="'.$Config['IPhubKey'].'"': "")?>>
									</div>
								</div><hr>
							</div>
						</div>
						<div id="Currencies" class="tab-pane fade" data-parent="#settings">
							<div class="col-12">
								<h4>Currency Settings</h4>
								<hr>
								<p>these are your currency settings, here you can edit your currencies or add new currencies. there are a couple things to watch out for which are outlined below.<br>NOTE: when adding New currencies your other changes WONT BE SAVED. so if you've made any changes, submit them and then add a new currency.<br> ADVICE: dont set your payout for currencies like btc too low. set a higher timer and ammount if you have to. but if the user's balance doesnt reach 1 satoshi before payout the user experience will be terrible. (the script allows for sub-satoshi values to roll over, but doesnt notify the user on this so expect heaps of complains if you choose to set the value too low).</p>
								<button type="button" class="btn" data-toggle="modal" data-target="#AddCurrencies">Add Currency</button><hr>
								<?php
								if(!empty($Config['Currencies'])){ // iterate over currencies and create the form for each currency.
								foreach ($Config['Currencies'] as $row => $options) {
								         echo'
										<h4>'.$row.'</h4>
										<div class="row">
											<div class="col-12 col-sm-4">
												<div class="ImageContainer">
													<img alt="'.$row.' Icon" src="'.DIRREC.'/images/'.$row.'.png" class="img-fluid">
												</div>
											</div>
											<div class="col-12 col-sm-8">
												<div class="row">
													<div class="col-3 col-sm-3">
														<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The UserToken is used for tracking in excesscrypto. We recommend to atleast set this to some value">help_outline</i>
														<label for="'.$row.'Key">'.$row.' UserToken</label>
													</div>
													<div class="col-8 col-sm-8">
														<input class="w-100 float-left" id="'.$row.'UserToken" type="text" name="Currencies['.$row.'][UserToken]" '.(isset($options['UserToken']) ? 'value="'.$options['UserToken'].'"': "").'>
													</div>
												</div>
												<div class="row">
													<div class="col-3 col-sm-3">
														<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The ApiKey is Used in order to Communicate with ExpressCrypto. therefore its required to use this currency. you can set each currency to a different APIkey or use the same for all of them, we reccomend to use more than one">help_outline</i>
														<label for="'.$row.'Key">'.$row.' Apikey</label>
													</div>
													<div class="col-8 col-sm-8">
														<input class="w-100 float-left" id="'.$row.'Key" type="text" name="Currencies['.$row.'][Apikey]" '.(isset($options['Apikey']) ? 'value="'.$options['Apikey'].'"': "").'>
													</div>
												</div>
											</div>
											<div class="form-group col-md-4">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Ammount of '.$row.' in satoshi theu ser gets per reload.">help_outline</i>
												<label for="'.$row.'Ammount">'.$row.'Ammount (in satoshi)</label>
												<input id="'.$row.'Ammount" type="Number" step="0.001" name="Currencies['.$row.'][Ammount]" value="'.$options['Ammount'].'">
											</div>
											<div class="form-group col-md-4">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Timer betweem reloads for '.$row.' ">help_outline</i>
												<label for="'.$row.'Timer">'.$row.'Timer (in seconds)</label>
												<input id="'.$row.'Timer" type="Number" step="1" name="Currencies['.$row.'][Timer]" value="'.$options['Timer'].'">
											</div>
											<div class="form-group col-md-4">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="PayoutCycle of '.$row.'. (how many reloads between withdraws)">help_outline</i>
												<label for="'.$row.'Payout Cycle">'.$row.' Payout Cycle</label>
												<input id="'.$row.'Payout Cycle" type="Number" step="1" name="Currencies['.$row.'][PayoutCycle]" value="'.$options['PayoutCycle'].'">
											</div>
											<div class="col-12">
												<a href="'.DIRREC.'/admin.php?DeleteCurrency='.$row.'" class="btn">Delete '.$row.'</a>
												<hr>
											</div>
										</div>';
								}
							}else{ // let the user know there arent any currencies
								echo 'No Currencies Added.<br><button type="button" class="btn" data-toggle="modal" data-target="#AddCurrencies">Add Currency</button>';
							}
								 ?>
							</div>
						</div>
						<?php if($SetupStage === 2){ // if the user has set up atleast 1 currency (required for the script to work) we display additional config options ?><div id="Shortlinks" class="tab-pane fade" data-parent="#settings">
							<div class="col-12">
								<h4>Shortlink Settings</h4>
								<hr>
								<p>Shortlinks are a great Revenue soruce that allow you to pay your users more while earning more yourself! these services usually show the user a few ads before letting them pass through, after which they pay you for sendign that user. the Script Fully Supports Shortlinks and Allows you to offer them for a higher payout. you can add new shortlinks, or edit their details below<hr>New to shortlinks? you can find a list of reliable shortlinks <a href="https://randomsatoshi.win/Guides/Shortlinks.php" target="_blank">here</a><br><button type="button" class="btn" data-toggle="modal" data-target="#AddShortlinks">Add Shortlink</button></p>
								<?php
								if(!empty($Config['Shortlinks'])){ // iterate over Shortlinks
								foreach ($Config['Shortlinks'] as $row => $options) {
									$ShortenerName = ShortenerName($options['Apilink']); // Use the shortener name function to make things easier.
								         echo'
										<div class="row">
											<div class="col-12 col-sm-12">
												<div class="row">
													<div class="col-3 col-sm-3">
														<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="the Api url is used to create new shortlinks for this shortener. without it the script cannot work.">help_outline</i>
														<label for="'.$ShortenerName.'Link">'.$ShortenerName.' APIlink</label>
													</div>
													<div class="col-8 col-sm-8">
														<input id="'.$ShortenerName.'Link" type="text" name="Shortlinks['.$ShortenerName.'][Apilink]" '.(isset($options['Apilink']) ? 'value="'.$options['Apilink'].'"': "").'>
													</div>
												</div>
											</div>
											<div class="form-group col-md-6">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Ammount of Views '.$ShortenerName.'  counts per IP">help_outline</i>
												<label for="'.$ShortenerName.'Viewcount">'.$ShortenerName.' Viewcount</label>
												<input id="'.$ShortenerName.'Viewcount" type="Number" step="1" name="Shortlinks['.$ShortenerName.'][Viewcount]" value="'.$options['Viewcount'].'">
											</div>
											<div class="form-group col-md-6">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="the Ranking place of '.$ShortenerName.' ">help_outline</i>
												<label for="'.$ShortenerName.'Rank">'.$ShortenerName.' Rank</label>
												<input id="'.$ShortenerName.'Rank" type="Number" step="1" name="Shortlinks['.$ShortenerName.'][Rank]" value="'.$options['Rank'].'">
											</div>
											<div class="col-12">
												<a href="'.DIRREC.'/admin.php?DeleteShortener='.$ShortenerName.'" class="btn">Delete '.$ShortenerName.'</a>
												<hr>
											</div>
										</div>';
								}
							}else{
								echo 'No Shortlinks Added.';
							}
								 ?>
							</div>
						</div>
						<div id="Advertisements" class="tab-pane fade" data-parent="#settings">
							<div class="col-12">
								<h4>Advertisements</h4>
								<hr>
								<p>Advertisements can be a great way to fund your site, the script has the ability to control your ads from the admin panel. so switching out ads is as easy as it gets! if you're new to advertisements, <a href="http://ads.japakar.com/">here</a>'s a list of adnetworks you can try out<hr> the script lets you configure 2 sets of advertisements: one for normal pages, and a special set just for the claimpage. this is because most advertisers will dislike auto reload pages. so we reccomend filling this second set with referral banners instead.</p>
								<ul class="nav nav-tabs nav-justified">
									<li class="nav-item">
										<a class="nav-link active" data-toggle="tab" href="#MainSet">Main Ads</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" data-toggle="tab" href="#ClaimSet">Claim Ads</a>
									</li>
								</ul>
								<div class="tab-content">
									<div class="tab-pane container active" id="MainSet">
										<div class="row">
											<p>The main Set is displayed on all pages aside from the claim page. so we reccomend putting ads from advertisers here that shouldnt be automatically reloaded.</p>
										</div>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Leaderboard ads are displayed near the top and bottom of the page, Leaderboard Ads are typically sized 728x90, therefore they're only displayed on larger screens">help_outline</i>
												<label for="MainLeaderboardTop">Leaderboard Top</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainLeaderboardTop" type="text" name="Ads[MainLeaderboardTop]" ><?php echo (isset($Config['Ads']['MainSet']['LeaderboardTop']) ?  base64_decode($Config['Ads']['MainSet']['LeaderboardTop']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Leaderboard ads are displayed near the top and bottom of the page, Leaderboard Ads are typically sized 728x90, therefore they're only displayed on larger screens">help_outline</i>
												<label for="MainLeaderboardBottom">Leaderboard Bottom</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainLeaderboardBottom" type="text" name="Ads[MainLeaderboardBottom]" ><?php echo (isset($Config['Ads']['MainSet']['LeaderboardBottom']) ?  base64_decode($Config['Ads']['MainSet']['LeaderboardBottom']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Banner ads are displayed near the top and bottom of the page, Banner Ads are typically sized 468x60, therefore they're only displayed on medium screens in the place of Leaderboard Ads">help_outline</i>
												<label for="MainBannerTop">Banner Top</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainBannerTop" type="text" name="Ads[MainBannerTop]" ><?php echo (isset($Config['Ads']['MainSet']['BannerTop']) ?  base64_decode($Config['Ads']['MainSet']['BannerTop']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Banner ads are displayed near the top and bottom of the page, Banner Ads are typically sized 468x60, therefore they're only displayed on medium screens in the place of Leaderboard Ads">help_outline</i>
												<label for="MainBannerBottom">Banner Bottom</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainBannerBottom" type="text" name="Ads[MainBannerBottom]" ><?php echo (isset($Config['Ads']['MainSet']['BannerBottom']) ?  base64_decode($Config['Ads']['MainSet']['BannerBottom']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Square ads are displayed near the top and bottom of the page, Square Ads are typically sized 250x250 or 300x250, therefore they're only displayed on small screens in the place of Leaderboard and Square Ads">help_outline</i>
												<label for="MainSquareTop">Square Top</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainSquareTop" type="text" name="Ads[MainSquareTop]" ><?php echo (isset($Config['Ads']['MainSet']['SquareTop']) ?  base64_decode($Config['Ads']['MainSet']['SquareTop']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Square ads are displayed near the top and bottom of the page, Square Ads are typically sized 250x250 or 300x250, therefore they're only displayed on small screens in the place of Leaderboard and Square Ads">help_outline</i>
												<label for="MainSquareBottom">Square Bottom</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainSquareBottom" type="text" name="Ads[MainSquareBottom]" ><?php echo (isset($Config['Ads']['MainSet']['SquareBottom']) ?  base64_decode($Config['Ads']['MainSet']['SquareBottom']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Skyscraper Ads are typically placed on the side of the pages. Skyscraper ads are typically Sized 120x600 or 160x600 and wont be displayed on small screens.">help_outline</i>
												<label for="MainSkyscraperTop">Skyscraper Left</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainSkyscraperLeft" type="text" name="Ads[MainSkyscraperLeft]" ><?php echo (isset($Config['Ads']['MainSet']['SkyscraperLeft']) ?  base64_decode($Config['Ads']['MainSet']['SkyscraperLeft']): "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Skyscraper Ads are typically placed on the side of the pages. Skyscraper ads are typically Sized 120x600 or 160x600 and wont be displayed on small screens.">help_outline</i>
												<label for="MainSkyscraperRight">Skyscraper Right</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="MainSkyscraperRight" type="text" name="Ads[MainSkyscraperRight]" ><?php echo (isset($Config['Ads']['MainSet']['SkyscraperRight']) ?  base64_decode($Config['Ads']['MainSet']['SkyscraperRight']) : "")?></textarea>
											</div>
										</div><hr>
									</div>
									<div class="tab-pane fade container " id="ClaimSet">
										<div class="row">
											<p>The Claim Set is displayed only on the Claimpage.. so we reccomend putting Referral banners instead of Advertisements here as alot of advertisers may disable your account for using an automatic reload on your site.</p>
										</div>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Leaderboard ads are displayed near the top and bottom of the page, Leaderboard Ads are typically sized 728x90, therefore they're only displayed on larger screens">help_outline</i>
												<label for="ClaimLeaderboardTop">Leaderboard Top</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimLeaderboardTop" type="text" name="Ads[ClaimLeaderboardTop]" ><?php echo (isset($Config['Ads']['ClaimSet']['LeaderboardTop']) ?  base64_decode($Config['Ads']['ClaimSet']['LeaderboardTop']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Leaderboard ads are displayed near the top and bottom of the page, Leaderboard Ads are typically sized 728x90, therefore they're only displayed on larger screens">help_outline</i>
												<label for="ClaimLeaderboardBottom">Leaderboard Bottom</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimLeaderboardBottom" type="text" name="Ads[ClaimLeaderboardBottom]" ><?php echo (isset($Config['Ads']['ClaimSet']['LeaderboardBottom']) ?  base64_decode($Config['Ads']['ClaimSet']['LeaderboardBottom']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Banner ads are displayed near the top and bottom of the page, Banner Ads are typically sized 468x60, therefore they're only displayed on medium screens in the place of Leaderboard Ads">help_outline</i>
												<label for="ClaimBannerTop">Banner Top</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimBannerTop" type="text" name="Ads[ClaimBannerTop]" ><?php echo (isset($Config['Ads']['ClaimSet']['BannerTop']) ?  base64_decode($Config['Ads']['ClaimSet']['BannerTop']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Banner ads are displayed near the top and bottom of the page, Banner Ads are typically sized 468x60, therefore they're only displayed on medium screens in the place of Leaderboard Ads">help_outline</i>
												<label for="ClaimBannerBottom">Banner Bottom</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimBannerBottom" type="text" name="Ads[ClaimBannerBottom]" ><?php echo (isset($Config['Ads']['ClaimSet']['BannerBottom']) ?  base64_decode($Config['Ads']['ClaimSet']['BannerBottom']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Square ads are displayed near the top and bottom of the page, Square Ads are typically sized 250x250 or 300x250, therefore they're only displayed on small screens in the place of Leaderboard and Square Ads">help_outline</i>
												<label for="ClaimSquareTop">Square Top</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimSquareTop" type="text" name="Ads[ClaimSquareTop]" ><?php echo (isset($Config['Ads']['ClaimSet']['SquareTop']) ?  base64_decode($Config['Ads']['ClaimSet']['SquareTop']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Square ads are displayed near the top and bottom of the page, Square Ads are typically sized 250x250 or 300x250, therefore they're only displayed on small screens in the place of Leaderboard and Square Ads">help_outline</i>
												<label for="ClaimSquareBottom">Square Bottom</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimSquareBottom" type="text" name="Ads[ClaimSquareBottom]" ><?php echo (isset($Config['Ads']['ClaimSet']['SquareBottom']) ?  base64_decode($Config['Ads']['ClaimSet']['SquareBottom']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Skyscraper Ads are typically placed on the side of the pages. Skyscraper ads are typically Sized 120x600 or 160x600 and wont be displayed on small screens.">help_outline</i>
												<label for="ClaimSkyscraperTop">Skyscraper Left</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimSkyscraperLeft" type="text" name="Ads[ClaimSkyscraperLeft]" ><?php echo (isset($Config['Ads']['ClaimSet']['SkyscraperLeft']) ?  base64_decode($Config['Ads']['ClaimSet']['SkyscraperLeft']) : "")?></textarea>
											</div>
										</div><hr>
										<div class="row">
											<div class="col-12 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Skyscraper Ads are typically placed on the side of the pages. Skyscraper ads are typically Sized 120x600 or 160x600 and wont be displayed on small screens.">help_outline</i>
												<label for="ClaimSkyscraperRight">Skyscraper Right</label>
											</div>
											<div class="col-12 col-sm-8 float-left">
												<textarea class="w-100 float-left" id="ClaimSkyscraperRight" type="text" name="Ads[ClaimSkyscraperRight]" ><?php echo (isset($Config['Ads']['ClaimSet']['SkyscraperRight']) ?  base64_decode($Config['Ads']['ClaimSet']['SkyscraperRight']) : "")?></textarea>
											</div>
										</div><hr>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					<div class="col-12 col-sm-12">
						<input type="submit" class="btn w-100" value="Submit Form">
					</div>
					</div>
					</form>

					<div class="col-12 col-sm-12 SecondLayer">
						<h4>Keeping the script Snappy</h4>
						<p>the more users use your site, the more data the script collects. it saves progress for the user and saves additional data for specific functions of the script (shortlinks, IPhub Checking, Apilimit logging). the more data the script saves the longer it takes to look through - so its reccomended to frequently clean out any unused/outdated data. the script gives an automated option to do this for you and it can be triggered manually by the button below or by setting up a cronjob.</p><a class="btn" href="<?php echo DIRREC ?>/GC_SCRIPT.php"> Clean up Data</a><hr> <code class="ThirdLayer">setting up a cronjob to clean up script data?<br>
						simply call this file in your cronjob: <?php echo DIRREC."/GC_SCRIPT.php?SecurityKey=".SECKEY; ?><br>
						if you're not sure how to set up cronjobs, you can contact your hosting provider for more infos
						</code>
					</div>

					<div class="modal fade" id="AddCurrencies">
						<div class="FirstLayer modal-dialog modal-dialog-center modal-lg">
							<div class="modal-content card SecondLayer">
								<form action="admin.php" method="POST">
								<h4>Add A Currency</h4>
								<p>add a new currency and configure it below! remember that submitting this form wont save any changes outside of it.<br>NOTE: if you havent added a single currency yet you may see all currencies at first. once you add a currency with a valid APIkey you will see all Currencies EC offers</p>
								<div class="row">
									<div class="col-12 col-sm-12">
										<select id="currencySelect" name="currency" class="w-75">
											<?php
												foreach($Currencies as $Currency){ // iterate over all currencies EC supports, skip those active in the script
													if(isset($Config['Currencies'][$Currency])){
														continue;
													} // make all other currencies an option to include later.
													echo '<option value="'.$Currency.'">'.$Currency.'</option>';
												}
											?>
										</select>
									</div><hr>
									<input type="hidden" name="AddCurrency" value="1">
									<div class="col-12 col-sm-12">
										<div class="row">
											<div class="col-3 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Apikey for your currency NOTE: make sure this is a valid key, as any invalid APIkey will cause the form to be rejected.">help_outline</i>
												<label for="Apikey">Apikey</label>
											</div>
											<div class="col-3 col-sm-8">
												<input class="w-100 float-left" id="Apikey" type="Text" name="Apikey" required>
											</div>
										</div><hr>
									</div>
									<div class="col-12 col-sm-12">
										<div class="row">
											<div class="col-3 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The UserToken for your Faucet. Propably used for Tracking or something">help_outline</i>
												<label for="UserToken">UserToken</label>
											</div>
											<div class="col-3 col-sm-8">
												<input class="w-100 float-left" id="UserToken" type="Text" name="UserToken" required>
											</div>
										</div><hr>
									</div>
									<div class="form-group col-md-4">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Ammount of satoshi the user gets per reload.">help_outline</i>
										<label for="Ammount">Ammount (in satoshi)</label>
										<input id="Ammount" type="Number" step="0.001" name="Ammount">
									</div>
									<div class="form-group col-md-4">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Timer betweem reloads">help_outline</i>
										<label for="Timer">Timer (in seconds)</label>
										<input id="Timer" type="Number" step="1" name="Timer">
									</div>
									<div class="form-group col-md-4">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="Payout Cycle  (how many reloads between withdraws)">help_outline</i>
										<label for="Payout Cycle">Payout Cycle</label>
										<input id="Payout Cycle" type="Number" step="1" name="PayoutCycle">
									</div>
									<div class="col-md-12">
										<input type="submit" class="btn" value="Submit form">
									</div>
								</div>
							</form>
							</div>
						</div>
					</div>
					<div class="modal fade" id="AddShortlinks">
						<div class="FirstLayer modal-dialog modal-dialog-center modal-lg">
							<div class="modal-content card SecondLayer">
								<form action="admin.php" method="POST">
								<h4>Add A Shortlink</h4>
								<p>Adding a Shortlink is as simple as it gets! simply go to the Developer API tab of your shortlink provider (Tools > Developer API) and select the link that looks something like this:<br> <code>https://domain.tld/api?api=dc6688c6402af072b2c879d85624i6j1ad</code>. paste it into the field below and configure the amount of views for this shortener! </p>
								<div class="row">
									<div class="col-12 col-sm-12">
										<div class="row">
											<div class="col-3 col-sm-3">
												<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The API Link for this shortener. NOTE: make sure to insert it properly as this will be validated! if we cant validate this shortener this form will be denied ">help_outline</i>
												<label for="Apilink">Apilink</label>
											</div>
											<div class="col-3 col-sm-8">
												<input class="w-100 float-left" id="Apilink" type="Text" name="Apilink" required>
											</div>
										</div><hr>
									</div>
									<input type="hidden" name="AddShortlink" value="1">
									<div class="col-12 col-sm-6">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The Amount of views per IP per 24 hours the shortener accepts. if you're not sure simply set this to 1">help_outline</i>
										<label for="Viewcount">Viewcount</label>
										<input class="w-50" id="Viewcount" type="Number" step="1" name="Viewcount" value="1">
									</div>
									<div class="col-12 col-sm-6">
										<i class="material-icons " data-toggle="tooltip" data-placement="bottom" title="The place in the order of how your shortlinks will appear. this cannot be set when adding a shortener.">help_outline</i>
										<label for="Rank">Shortener Rank</label>
										<input class="w-50" disabled id="Rank" type="Number" step="1" name="Rank">
									</div>

									<div class="col-md-12">
										<input type="submit" class="btn" value="Submit form">
									</div>
								</div>
							</form>
							</div>
						</div>
					</div>




				</div>
			</div>
			<div class="col-12 col-md"></div>
		</div>
<?php
	include FDSR.DIRECTORY_SEPARATOR."/footer.php"; // include the standard footer
	ob_end_flush(); // flush the output buffer and send user the page - solves issues with a few hosters.

	//finally done commenting this. good lucky making any changes to this future kull lmao
 ?>
