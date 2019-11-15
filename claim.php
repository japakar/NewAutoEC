<?php

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

/* ==============================================================================================
 * """"" MAIN SECTION: CHECK IF SESSION IS VALID, INCREASE TOKEN AND PAYOUT IF READY        =====
 * ==============================================================================================
*/
if(!isset($Config['Currencies'][$_GET['coin']])){
	$_SESSION['ErrMSG'] =  $_GET['coin']." is not a Valid Coin!";
	redirect(DIRREC."/"); // not a valid coin
}
$Coin = $_GET['coin'];

if(!isset($_SESSION[$Coin.'SecToken']) || !isset($_COOKIE[$Coin.'Token'])){
	$_SESSION['ErrMSG'] =  "Session is expired";
	redirect(DIRREC."/"); // not a valid Token
}

if($_SESSION[$Coin.'SecToken'] !== $_COOKIE[$Coin.'Token']){
	unset($_SESSION[$Coin.'SecToken']); // remove sectoken as they dontr match.
	$_SESSION['ErrMSG'] =  "Session is invalid";
	redirect(DIRREC."/"); // not a valid Token²
}
// the user's session seems valid so we proceed with checking his last claimtime & add the tokens to his balance
$CConfig = $Config['Currencies'][$Coin]; // makin' life easy
if(isset($_SESSION['ErrMSG'])){ // standard error message processing
	$messages = $messages.ErrorMSG("clear", $_SESSION['ErrMSG']);
	unset($_SESSION['ErrMSG']);
}
if(isset($_SESSION['SuccessMSG'])){ // standard success message processing
	$messages = $messages.SuccessMSG("clear", $_SESSION['SuccessMSG']);
	unset($_SESSION['SuccessMSG']);
}

//loading permanent user data to use and edit
$UserData = GetUserData($Coin, $_SESSION[$Coin.'address']);
if($UserData === null){
	$_SESSION['ErrMSG'] =  "We Couldnt find your UserData, please use the form below to start a claim so your UserData is properly set";
	redirect(DIRREC."/"); // the script couldnt find the userdata, so we shove panic and abandon the claim.
}
$UserData['isNewUser'] = false; // tell the script that th user has userdata.
if($UserData['ConfigID'] != $CConfig['ConfigID']){ // check if the config has changed from when the user last reloaded the page - this is a safety feature and shouldnt be disabled even if it may inconvenience the user a bit.
	$UserData = array("Address" => $UserData['Address'], "Tokens" => 0, "SuccessfulWithdraws" => 0, "ClaimStarted" => time(),"TimesUsed" => $UserData['TimesUsed'], "Referrer" => ($UserData['Referrer'] ?: null), "ConfigID" => $UserData['ConfigID'], "isNewUser" => false);
	$messages = $messages.WarningMSG("clear","The Admin Made some changes to the ".$Coin." Configuration, so your current progress had to be reset. you can continue claiming without any issues now!");
}

if($_SESSION[$Coin.'claimtime'] < time() - $CConfig['Timer']){  // check if enough time has passed since the last claim
	$UserData['Tokens']++;
	$PayoutCycles = floor($UserData['Tokens']/$CConfig['PayoutCycle']); // set some variables to determine which payout cycle the user is on
	$NextCycle = ceil($UserData['Tokens']/$CConfig['PayoutCycle']);
	$PendingTokens = $UserData['Tokens']-($CConfig['PayoutCycle']*$UserData['SuccessfulWithdraws']); // how many pending tokens the user has incase of an error
	$Payout = round($CConfig['Ammount']*$PendingTokens, 3); // payout the user sees on the page.
	$RemainingRefreshes = intval($NextCycle * $CConfig['PayoutCycle'] - $UserData['Tokens']); // refreshes until a withdraw
	$Timer = $CConfig['Timer']; // set the remaining time to the Timer value of the currencyConfig, so that the next reload is after the user can claim again
	$_SESSION[$Coin.'claimtime'] = time(); // update the claim time to now
	if(($CConfig['PayoutCycle'] === 1 || $RemainingRefreshes === 0) && !isset($_COOKIE['CreditsEmpty'])) { // check if its time for a withdraw
		if(isset($UserData['Referrer']) && $Config['RefCommission'] !== 0){ // Check if the user has a Referrer and if Referrals are activated
			$Referrer = $UserData['Referrer'];
		}else{
			$Referrer = null;
		}
		$TotalPayout = floor($Payout + $_SESSION[$Coin.'RollOver']); // set the actual payout value (EC doesnt allow Sub-Satoshi values)
		if($TotalPayout === 0){ // check if the actual payout value isnt 0. if so we set the Rollover to the payout + previous rollover
			$_SESSION[$Coin.'RollOver'] = $_SESSION[$Coin.'RollOver']+$Payout;
		}else{ // the user has a balance over 1, so we can actually pay him!
			$Result = ProcessPayout($TotalPayout, $_SESSION[$Coin."address"], $Coin,$CConfig['Apikey'],$CConfig['UserToken'], $Referrer, $Config['RefCommission']); // wrapper function for the EC payout, handles Errors better
			if($Referrer !== null){ // if the Payout Result let us know that the Referral address was invalid, we unset the referrer to save APIcalls
				if($Result['ValidReferral']){
					$UserData['Referrer'] = null;
				}
			}
			if($Result['status'] === 200){ //withdraw was a success
				$_SESSION[$Coin.'RollOver'] = ($Payout + $_SESSION[$Coin.'RollOver']) - $TotalPayout; // set the remaining roll over value
				$messages = $messages.SuccessMSG("checked","Success! We paid out ".$TotalPayout." ".$Coin." Satoshi to ".$_SESSION[$Coin.'address']);
				$UserData['SuccessfulWithdraws'] = $PayoutCycles; // set the last SuccessfulWithdraw to the current PayoutCycle.
				$PendingTokens = 0; // set the pending tokens to 0,
				$Payout = round($CConfig['Ammount']*$PendingTokens, 3); // calculate new payout
			}elseif($Result['status'] === 404){ // the address is invalid: remove the address from the Dataset and End the Session
				DeleteUserData($Coin, $_SESSION[$Coin."address"]);
				$_SESSION['ErrMSG'] =  "ExpressCrypto didnt recognize the UserID: ".$_SESSION[$Coin.'address'];
				DestroySession($Coin); // buh-bye
			}else{ //some error thats non fatal
				$messages = $messages.ErrorMSG("clear","We encountered an error: ".$Result['message']);
			}
		}
		$RemainingRefreshes = $CConfig['PayoutCycle']; // set the refreshes to the payout cycle from the config.
	}
}else{ // no Withdraw so we just set some values for the script to display later.
	$NextCycle = ceil($UserData['Tokens']/$CConfig['PayoutCycle']);
	$PendingTokens = $UserData['Tokens']-($CConfig['PayoutCycle']*$UserData['SuccessfulWithdraws']);
	$Payout = round($CConfig['Ammount']*$PendingTokens);
	$RemainingRefreshes = $NextCycle * $CConfig['PayoutCycle'] - $UserData['Tokens'];
	$Timer = $CConfig['Timer'] - (time() - $_SESSION[$Coin.'claimtime']); // set the remaining time
}
SaveUserData($Coin, $UserData); // save userdata to preserve changes.
$HeaderRefresh = $Timer; // fallback using a metatag inside the header



// Set the Type of ads that should be used on this page.
if(isset($Config['Ads']["ClaimSet"])){
	$AdsArray = $Config['Ads']["ClaimSet"];
}

$title = "Claim $Coin - ".$Config['Sitename']; // adjust the title
include FDSR.DIRECTORY_SEPARATOR."/header.php"; // include the standard header
if($Timer > 0){ // if the timer isnt 0, we setup the reload timer via JS
 echo'
 <script type="text/javascript">
   setTimeout(function () { location.reload(true); }, '.($Timer+1) * 1000 .');
 </script>';}
?>
<div class="row justify-content-center container-fluid FirstLayer">
	<div class="col-12 col-md h-100 align-self-center"><span class="m-1 d-none d-xl-block"><div class="text-center"><?php echo base64_decode(@$AdsArray['SkyscraperLeft']); ?></div></span></div>
	<div class="col-12 col-md-8  mx-auto text-center ">
		<h1 class="my-4"><?php echo $Config['Sitename'] ?></h1>
		<div class="col-12 col-sm-12">
			<div class="m-4 d-none d-xl-block">
				<?php echo base64_decode(@$AdsArray['LeaderboardTop']); ?>
			</div>
			<div class="m-4 d-none d-lg-block d-xl-none">
				<?php echo  base64_decode(@$AdsArray['BannerTop']); ?>
			</div>
			<div class="m-4 d-none d-xs-block d-lg-none">
				<?php echo  base64_decode(@$AdsArray['SquareTop']); ?>
			</div>
		</div>
		<div class="SecondLayer card">
			<h3>Claim <?php echo $Coin ?></h3><br>
			<?php echo $messages ?><br>
			<hr>
			<p>Thats it! Its this simple. Keep this page open and your balance will increase on every pageload! Once the timer is complete, we will automatically send you a payout to your ExpressCrypto.io Account! In the case of an error, your balance will continue to increase so that you get your full balance on the next payout!</p><br>
			<div class="progress" style="height:20px;">
    			<div class="progress-bar" role="progressbar" style="width:100%" aria-valuenow="<?php echo $CConfig['Timer'] ?>" aria-valuemin="0" aria-valuemax="<?php echo $CConfig['Timer'] ?>" id="Timer-progress"></div>
			</div>
			<script>
			function countdown(time,max,element) {
    var bar = document.getElementById(element),

    int = setInterval(function() {
		let CurrentValue = Math.floor(100 * time-- / max) + '%';
        bar.style.width = CurrentValue;
		bar.innerHTML = time + " Seconds";
		bar.setAttribute("aria-valuenow", time);
        if (time - 1 == max) {
            clearInterval(int);
        }
    }, 1000);
}
			countdown('<?php echo $Timer ?>', '<?php echo $CConfig['Timer'] ?>','Timer-progress');
			</script>
			<h5>Cool Data:</h5><br>
			<div class="col-12 col-md-8 offset-md-2">
				<div class="row">
					<div class="col ThirdLayer card">Balance: <br><?php echo $Payout." ".$Coin." Satoshi" ?> <!-- <span class="Smalltext"><?php echo $_SESSION[$Coin."RollOver"] ?></span> --></div>
					<div class="col ThirdLayer card">Reward: <br><?php echo $CConfig['Ammount']." ".$Coin." Satoshi" ?></div>
  					<div class="w-100"></div>
					<div class="col ThirdLayer card">Seconds till Payout: <br><?php echo $CConfig['Timer']*($RemainingRefreshes) ?></div>
					<div class="col ThirdLayer card">Withdraw every: <br><?php echo $CConfig['PayoutCycle'] ?> Reloads</div>
				</div>
			</div>
		</div>
		<div class="col-12 col-sm-12">
			<div class="m-4 d-none d-xl-block">
				<?php echo base64_decode(@$AdsArray['LeaderboardBottom']); ?>
			</div>
			<div class="m-4 d-none d-lg-block d-xl-none">
				<?php echo base64_decode(@$AdsArray['BannerBottom']); ?>
			</div>
			<div class="m-4 d-none d-xs-block d-md-none">
				<?php echo base64_decode(@$AdsArray['SquareBottom']); ?>
			</div>
		</div>
	</div>
<div class="col-12 col-md h-100 align-self-center"><span class="m-1 d-none d-xl-block"><div class="text-center"><?php echo base64_decode(@$AdsArray['SkyscraperRight']); ?></div></span></div>
</div>
<?php
include FDSR.DIRECTORY_SEPARATOR."/footer.php"; // include the standard footer
ob_end_flush(); // flush the output buffer and send user the page - solves issues with a few hosters.

// Still less work than that Admin panel lmao.
?>
