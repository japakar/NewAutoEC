# Autofaucet Script Name

### a complete rewrite of the popular but mostly outdated Floodgate Script


| Table of Contents |
| ----------------- |
| Features |
| Requirements |
| Installation |



### Features

a list of notable features of the script

- New: ExpressCrypto implemented!
- Revised Claim Process

  - Progress is saved even between sessions

  - Payouts Can be sent Every X claims

  - internal tracking of claims to allow easy customization.

- Simplified Layout

  - a new bootstrap based design

  - easily change layout and colors via the admin panel

  - Responsive & Mobile Friendly

- Powerful Admin Panel

  - No more Editing Configs with a fully integrated Admin Panel

  - Manage Currencies, Shortlinks & Banner Ads Conveniently.

  - Easily add Captchas and Proxy Detection

- Future Proof

  - ~~No Hardcoded currencies by dynamically fetching Currencies from EC~~ (removed until EC adds this endpoint)

  - Images are pulled from EC whenever a currency is added

  - API limit detection notifies you when to increase the payout cycle


### Requirements

##### Minimum Requirements

- Apache or NGinX (any version)

- PHP 5.6 or Higher

  - Read and Write Permissions Required

- ExpressCrypto Account


##### Reccomended Requirements

- PHP 7.0 or Higher

  - the higher the version the better. Never Versions genrally outperform older PHP version

- PHP-SQLite3 Extention

  - SQLite offers Higher performance to json without needing to setup a Database.



### Installation

- Download the newest version from github

- Unpack it on your server and remove the ZIP (Keep it on your local pc though incase you need it!)

- visit the index page in your browser - this will create a Security Key and store it in your config.

- open the config.php file, get your Security Key and use it to set up admin credentials

- Done! you can now configure your script and never need to touch a config file again!

<a href="https://hCaptcha.com/?r=befc8fa2362e" target="_blank">HCaptcha</a><br>
