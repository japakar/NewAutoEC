<?php
/*
 * Autofaucet Script Remake By Kulltero, Creator of Randomsatoshi.win & Qfaucet.net
 * This remake of the original Script utilizes modern and improved code making it more secure and user friendly.
 * Fully integrated into Faucethub, Admin Panel & Future Proofing by being completely functional with all future currencies to come to EC*
 * *assuming EC doesnt make any big changes out of nowhere.

*/



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
(include FDSR.DIRECTORY_SEPARATOR."config.php") OR die("Something went wrong while trying to load the config file! Please make sure your Config file is in the scripts main folder, please also check if PHP has rights to include/require files! (your hosting provider may assist you with this!)");
//Load Functions, if it fails give out an error and end the script.
(include_once FDSR.DIRECTORY_SEPARATOR."functions.php") OR die("Something went wrong while trying to load the functions file! Please make sure your functions file is in the scripts main folder, please also check if PHP has rights to include/require files! (your hosting provider may assist you with this!)");
if($Config === ""){
	$Config = array("ConfigCreated" => true);
	if(SECKEY === ""){ //checks if the security token is empty (which would be terrible security)
		UpdateConfig($Config, RandomString(12)); // updates the Security Key WARNING: the second variable WILL CHANGE the Security key, so it shouldnt be called unless intended.
		redirect(DIRREC."/",302,2);
	}else{
		UpdateConfig($Config); // updates the the config without changing the sec key
		redirect(DIRREC."/",302,2);
	}
}else{
	$Config = json_decode($Config, true);
	if($Config === null){
		die("Config file is corrupted, please make sure the config is intact!");
	}
}
if(SECKEY === ""){ //checks if the security token is empty (which would be terrible security)
	UpdateConfig($Config, RandomString(12)); // updates the Security Key WARNING: the second variable WILL CHANGE the Security key, so it shouldnt be called unless intended.
	redirect(DIRREC."/",302,2);
}
if(isset($_SESSION['ErrMSG'])){ // standard error message processing
	$messages = $messages.WarningMSG("clear", $_SESSION['ErrMSG']);
	unset($_SESSION['ErrMSG']);
}
if(isset($_SESSION['SuccessMSG'])){ // standard success message processing
	$messages = $messages.WarningMSG("clear", $_SESSION['SuccessMSG']);
	unset($_SESSION['SuccessMSG']);
}
unset($Config["ConfigCreated"]);
if(empty($Config)){ // check if the config exists and is ready to be used. if its not useable, allow the owner to configure the admin username & Password via the security key in the config.
	//display the form to set up admin credentials (just some simple html for onetime use)
	echo '
<html>
	<head>
		<title>Set up the script!</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="'.DIRREC.'/Custom.css"/>
	</head>
	<body>
		<div class="row justify-content-center container-fluid FirstLayer">
			<div class="col-12 col-md"></div>
			<div class="col-12 col-md-8  mx-auto text-center">
				<h1>Setup your Admin credentials</h1>
				'.$messages.'
				<div class="card col-12 col-sm-6 offset-sm-3 SecondLayer">
					<form method="POST" action="'.DIRREC.'/admin.php">
						<input type="text" name="Username" placeholder="Username"><br>
						<input type="password" name="Password" placeholder="Password"><br>
						<input type="text" name="SecKey" placeholder="Security Key"><br>
						<input type="hidden" name="CreateCredentials" value="true">
						<input type="submit" value="Set Credentials" class="btn">

					</form>
				</div>
				<blockquote> This Page only Shows if the script is not setup/reset! The form above is only for the site owner, if you are a user please come back at a later time once the site is set up!
				</blockquote>
			</div>
			<div class="col-12 col-md"></div>
		</div>
	</body>
</html>';
	exit;
}
if(@$Config['Useable'] !== true){ // check if the script is completely set up, and ready to handle users. if not
	echo '
<html>
	<head>
		<title>Script in Maintenance!</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="'.DIRREC.'/Custom.css"/>
	</head>
	<body>
		<div class="row justify-content-center container-fluid FirstLayer">
			<div class="col-12 col-md"></div>
			'.$messages.'
			<div class="card col-12 col-md-6 SecondLayer">
				<blockquote>
					The script is missing critical information and cannot handle users at the moment. If you are the admin of the script, visit <a href="'.DIRREC.'/admin.php">This Page!</a><br>if you are a user, check back once the script is fully set up.
				</blockquote>
			</div>
			<div class="col-12 col-md"></div>
		</div>
	</body>
</html>';
	exit;
}


//if none of the checks above match, the script assumes that its ready to handle users.

/* ==============================================================================================
 * """"" MAIN SECTION: DISPLAYS THE INDEX FILE AND LETS THE USER SELECT A CURRENCY          =====
 * ==============================================================================================
*/
if(isset($_COOKIE['Ref'])){ // checks if the user has a referral set
	if(CharCheck($_COOKIE['Ref'])){ // checks if the referral has invalid characters
		$Referrer = $_COOKIE['r'];
	}else{// othewhise delete the referral data
		setcookie("Ref", $_COOKIE['Ref'], time()-60*60*24*365);
	}
}
if(isset($_GET['r'])){ // if the user came via a referral link
	if(CharCheck($_GET['r'])){ // check if the referall is valid
		setcookie("Ref", $_GET['r'], time()+60*60*24*365); // save the referall.
		$Referrer = $_GET['r'];
	}
}
// Set the Type of ads that should be used on this page.
if(isset($Config['Ads']["MainSet"])){
	$AdsArray = $Config['Ads']["MainSet"];
}

$title = "Home - ".$Config['Sitename']; // set appropriate title
include FDSR.DIRECTORY_SEPARATOR."/header.php"; // include the standard header
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
				<div class="m-4 d-none d-xs-block d-md-none">
			        <?php echo  base64_decode(@$AdsArray['SquareTop']); ?>
	    		</div>
			</div>
		<div class="SecondLayer card">
			<h3>Get Free Coins Automatically</h3>
			<h5>NonStop* Payments, efficient payout system and more</h5>
			<?php echo $messages ?>
			<hr>
			<p><?php echo $Config['Sitename'] ?> takes the outdated but popular Autofaucet Concept and gives it both a new Facelift and a killer engine under the hood. no more errors abrupting your claims, no more loosing your progress, no more Apilimit errors ruining your day. 100% Focus on the Experience.</p><br>

			<form action="<?php echo DIRREC ?>/verify.php" method="POST">
			<h5>Step 1: Select your Currency</h5>
				<select id="currencySelect" name="currency" class="w-25 " style="height:2em"">
					<?php
					$CurrencyInfo = ''; // iterate over the currencies array and produce valid HTMl for the form and info panel
					foreach($Config['Currencies'] as $Currency => $Options){
						echo '<option value="'.$Currency.'">'.$Currency.'</option>';
						$CurrencyInfo = $CurrencyInfo.'<div class="row CurrencyInfo" id="'.$Currency.'Info">
						<div class="col-4 card ThirdLayer">Payout: <br>'.$Options['Ammount'].' <br><span class="Smalltext">satoshi per reload</span></div>
						<div class="col-4 card ThirdLayer">Timer: <br><span class="timelayout">'.$Options['Timer'].'</span><span class="Smalltext">seconds between reloads</span></div>
						<div class="col-4 card ThirdLayer">Withdraw Cycle: <br>'.$Options['PayoutCycle'].' <br><span class="Smalltext">reloads</span></div>
						</div>';
					}
					?>
				</select>
				<?php if(isset($Referrer) && $Config['RefCommission'] !== 0){ // check if the referrals are enabled and if the user was referred
					echo '<input type="hidden" name="Referrer" value="'.$Referrer.'"></input>'; // add a hidden form input to be processed later.
				} ?>
				<div class="col-12 col-md-8 offset-md-2"><?php echo $CurrencyInfo ?></div>
				<script>
				$(function(){
	   				$('#currencySelect').change(function(){
		   				$('.CurrencyInfo').hide();
		   				$('#' + $(this).val() + 'Info').show();
	   				});
   				});
				$(function() {
		   			$('.CurrencyInfo').hide();
		   			$('#' + $('#currencySelect').val() + 'Info').show();
   				});
				</script>
				<h5>Step 2: Enter your Address or your EC username</h5>
				<input type="text" placeholder="Address" name="address" class="w-50 " style="height:2em"">
				<div class="w-100"></div>
				<?php if(isset($Config['Captchas'])){ // check if the script has captchas enabled
					if(!empty($Config['Captchas'])){ // check if the captchas are actually set - duh
						$CaptchaOptions = ""; // iniatialize the variables to hold the form HTML aswell as the actual captchas.
						$CaptchaWidgets =  '';
						if(isset($Config['Captchas']['HCaptchaWebsitekey']) && isset($Config['Captchas']['HCaptchaSecretkey'])){
							// do everything needed to display Hcaptcha
							$CaptchaOptions = $CaptchaOptions.'<option value="HCaptcha">Hcaptcha</option>';
							$CaptchaWidgets =  $CaptchaWidgets.'<div class="row CaptchaInfo justify-content-center" id="HCaptchaInfo">
							<script>
							var HcaptchaLoad = function() {
							        hcaptcha.render("HCaptchaField", {
							          "sitekey" : "'.$Config['Captchas']['HCaptchaWebsitekey'].'"
							        });
							      };
							    </script>
							</script>
							<div id="HCaptchaField"></div>
							<script src="https://hcaptcha.com/1/api.js?onload=HcaptchaLoad&render=explicit" async defer></script>
							</div>';
						}
						if(isset($Config['Captchas']['ReCaptchaWebsitekey']) && isset($Config['Captchas']['ReCaptchaSecretkey'])){
							//do everything needed to display ReCaptcha
							$CaptchaOptions = $CaptchaOptions.'<option value="ReCaptcha" selected>ReCaptcha</option>';
							$CaptchaWidgets =  $CaptchaWidgets.'<div class="row CaptchaInfo justify-content-center" id="ReCaptchaInfo">
							<script>
							var RcaptchaLoad = function() {
							        grecaptcha.render("ReCaptchaField", {
							          "sitekey" : "'.$Config['Captchas']['ReCaptchaWebsitekey'].'"
							        });
							      };
							    </script>
							</script>
							<div id="ReCaptchaField"></div>
							<script src="https://www.google.com/recaptcha/api.js?onload=RcaptchaLoad&render=explicit" async defer></script>
							</div>';
						}
						// add the form input and  let the user know he needs to verify himself
						echo 'Step 3: Choose a Captcha and verify that you arent a bot:<br><select id="CaptchaUsed" name="CaptchaUsed" class="w-50 " style="height:2em"">'.$CaptchaOptions.'
					</select>';
					echo '<div class="col-12 col-md-8 offset-md-2">'.$CaptchaWidgets.'</div>'."
					<script>
					$(function(){
		   				$('#CaptchaUsed').change(function(){
			   				$('.CaptchaInfo').hide();
			   				$('#' + $(this).val() + 'Info').show();
		   				});
	   				});
					$(function() {
			   			$('.CaptchaInfo').hide();
			   			$('#' + $('#CaptchaUsed').val() + 'Info').show();
	   				});
					</script>";
					}
				} ?>
				<h5>Done! Go Get that paper!</h5>
				<input type="submit" value="Claim" class="btn">
			</form>
			<?php if(isset($Config['RefCommission'])){
				if($Config['RefCommission'] !== 0){
					if(isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']){
						$Scheme = "https://";
					}else{
						$Scheme = "http://";
					}
					echo '<hr>
					<div class="ThirdLayer card">
						<p>Referral Program: Refer users and earn: '.$Config['RefCommission'].'% on all their claims!
						'.$Scheme.$Config['Domain'].DIRREC.'/?r={Your EC-UserID}</p>
					</div>';
				}
			} ?>

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

// 10/10, commenting still better than the admin panel lol
?>
