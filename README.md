## Install ##

To install this script you should navigate to the folder where you want to install it and type 
```
#!php

git clone https://Flozn27@bitbucket.org/Flozn27/hosteurope-dns-update.git
```

## Composer ##
This project uses composer primary to install PHPUnit


**Get composer** (you are still in the checkout folder)
```
#!php

curl -s https://getcomposer.org/installer | php
```
**or**
```
#!php
wget https://getcomposer.org/installer

```
**and execute** (again in the checkout folder)
```
#!php
php composer.phar install

```

* * *

## Requirements ##

 * \>= PHP 5.6 up to PHP7.0.12 (in case you only have the ability to use a lower version as mentioned before, feel free to contact me and we'll find a solution)
 * At least 2MB free space

* * *

## Next steps ##
Copy and rename or just rename the config.ini.skel file to config.ini and replace the dummy values in section "Credentials" with your login data. Then just call the init script (run.php) from wherever you are; There is no need to navigate to its directory and call the script from there because it uses the chdir() function which tells the script to change to its own directory on initialization.

* * *

## Examples ##

Create a new entry:
```
#!php
user> php run.php -d example.com -h best (Results in best.example.com)
```

Update a single entry with newest ip address:
```
#!php

user> php run.php -d example.com -h best 
```

Update the main host i.e. .example.com
```
#!php

user> php run.php -d example.com
```

Update a single entry to a CNAME record type (must be written in uppercase and need the --force option):
```
#!php
user> php run.php -d example.com -h best -t CNAME -a "other.example.com" --force
```

Update a single entry to a TXT record type (must be written in uppercase and need the --force option):
```
#!php
user> php run.php -d example.com -h best -t TXT -a "v=spf1 a ip4:12.34.56.78/28 include:acmeemailmarketing.com ~all" --force
```

Delete an existing entry (--force is required to raise awareness):
```
#!php
user> php run.php -d example.com -h best --delete --force
```

* * *

## Additional notes ##

Run automatic updates through cronjobs only because of updating records with type A (or AAAA in future). The CNAME and TXT entries have static values so they need no continuing updates. The script itself has some detection logic for this case, so don't worry; You can't destroy anything. It just produces more load and change requests to your account (which should be at the absolute minimum to prevent unnecessary action calls to HostEurope DNS update bot (DR BOPP))

* * *

## What is this repository for? ##

* This script provides an opportunity to automatically update your dns entries at HostEurope
* Currently you can only update one entry per request (but it´s planned to provide a multiple update possibilty)

* * *

## Contribution guidelines ##

* Create a new branch and commit your improvements
* If possible, write a testcase for your improvement
* If you think you are done create a pull request