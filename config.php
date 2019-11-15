<?php
	/* this config file is a special file that stores all your settings of the script. by default it looks empty and only has 1 option you can set.
	 * the script will be configured via a complete admin panel located at (your domain)/admin.php or (your domain)/(folder)/admin.php if the script is in a folder
	*/

	//secure this file by checking if the user tried to acess it durectly
	if(!defined("SFR_INC_0_LKEY")){
		http_response_code(404);
		exit;
	}


	// The Security Key: this is essential for the script's security. this key is needed to set your admin credentials or to retrieve them incase you forget them
	// this key is also needed incase you wish to reset the script via the admin panel.
	// set the key to whatever you like. Example:
	// define("SECKEY", "ExampleKey");
	define("SECKEY", "");

	// below this part is the config, this shouldnt be touched by humans because the script might get a Panic Attack if the values are any different from what it expects.
	// there is no reason to change anything by hand. if you're feeling risky atleast make a backup before you ruin everything and dissapoint your family.
	// dont say i didnt warn you!
	$Config = "";

?>
