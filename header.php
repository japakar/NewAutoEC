<html>
	<head>
		<title><?php if(isset($title)){ echo $title;}elseif(isset($Config['Sitename'])){ echo "Autofaucet - ". $Config['Sitename'];}else{ echo "Autofaucet Script";} ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<script src="https://code.jquery.com/jquery-3.2.1.min.js"  crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="<?php echo DIRREC ?>/Custom.css?<?php echo time() ?>"/>
		<style>
			<?php if(isset($Config['CustomCSS'])){
				echo base64_decode($Config['CustomCSS']);
			} ?>
		</style>
	</head>
	<body>
		<nav class="navbar navbar-expand-lg SecondLayer">
  			<a class="navbar-brand" href="<?php echo DIRREC ?>/"><?php echo @$Config['Sitename'] ?></a>
  			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    			<span class="navbar-toggler-icon"></span>
  			</button>
    		<ul class="navbar-nav mr-auto">
  				<li class="nav-item active">
    				<a class="nav-link" href="<?php echo DIRREC; ?>/">Home</a>
      			</li>
    		</ul>
			<?php
			if(@$Config['Useable'] === true){
				$ActiveSessions = "";
				if(isset($Config['Currencies'])){
					foreach($Config['Currencies'] as $currency => $options){ // display active sessions the user can continue
						if(isset($_COOKIE[$currency.'Token']) && isset($_SESSION[$currency.'SecToken'])){
							$ActiveSessions = $ActiveSessions.'<a class="dropdown-item SecondLayer" style="max-height:48px;" target="_blank" href="'.DIRREC."/claim.php?coin=".$currency.'"><img class="img-fluid" style="max-width:24px;" src="'.DIRREC.'/images/'.$currency.'.png"> '.$currency.' Session</a>';
						}else{
							setcookie($currency."Token", "expired", time()-120000);
						}
					}
					if(!empty($ActiveSessions)){ ?>


			<ul class="navbar-nav ml-auto mr-1">
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" style="border: 1px solid var(--SecondBackground)" href="#" id="ActiveSessionsLink" data-toggle="dropdown">
						Active Sessions
					</a>
					<div class="dropdown-menu SecondLayer"">
						<?php echo $ActiveSessions ?>
					</div>
				</li>
			</ul>
				<?php }
				}
			}?>
		</nav>
