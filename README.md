# Simple HTML
Really simple PHP app that build HTML files from HTML fragments.
For more information on LfPHP Cloud Services:
[https://lfphpcloud.net](https://lfphpcloud.net)

## Update
Use composer to update existing `/vendor` source code
```
php composer.phar self-update
php composer.phar update
```

## Basic website config
* Open `/src/config/config.php`
  * Modify configuration to suit your needs
  * Use `/src/config/config.php.dist` as a guide
* Open `/public/index.php`
  * Modify the three global constants to suit your needs:
    * `BASE_DIR`
    * `HTML_DIR`
    * `SRC_DIR`

## Configure the management utlity `lfc.sh`
Edit `lfc.sh` and change the value of the `NAME` variable
* If your website is called `http://my.supersite.com/` the short name would be `supersite`
Edit `lfc.sh` and change the value of the `EXT` variable
* If your website is called `http://my.supersite.com/` the ext would be `com`

## Populate Credentials
Copy security credentials file
```
cp security_cred.json.dist security_cred.json
```
Populate `security_cred.json` file with the appropriate information
* Any info you don't have or will not use just leave blank
Initialize run files with prompts:
```
./lfc.sh creds templates/deployment
```
Initialize run files no prompts:
```
./lfc.sh creds templates/deployment --no-prompts
```

## To Run Locally Using PHP
From this directory, run the following command:
```
php -S localhost:8888 -t public
```

## To Run Locally Using Docker-Compose
* Install Docker
* Install Docker-Compose
* Run this command:
```
./lfc.sh up
```
* To stop the container:
```
./lfc.sh down
```

## To Test Actual Deployment:
Bring online:
```
./lfc.sh start
```
You can access your website on `http://locahost:8888/`

Take off line:
```
./lfc.sh stop
```

## Deployment to LfPHP Cloud Services
Bring online:
```
./lfc.sh deploy
```
If you see `200` or `201` codes you're good
* Wait a few minutes for the deployment to complete
* You can get an idea how long it will take to deploy by running `./lfc.sh start`

If you see an error code
* Test locally and debug

