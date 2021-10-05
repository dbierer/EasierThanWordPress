# Simple HTML
Really simple PHP app that builds HTML files from HTML widgets.

## Initial Installation
1. Clone this repository to the project root of your new website.
2. Use composer to install 3rd party source code (e.g. PHPMailer)
```
wget https://getcomposer.org/download/latest-stable/composer.phar
php composer.phar self-update
php composer.phar install
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

## To Run Locally Using PHP
From this directory, run the following command:
```
php -S localhost:8888 -t public
```

## To Run Locally Using Docker and docker-compose

### Windows
Install [Docker Desktop for Windows](https://hub.docker.com/editions/community/docker-ce-desktop-windows)

Open the Power Shell (some commands don't work in the regular command prompt)

To bring the docker container online, run this command:
```
admin up
```
To stop the container do this:
```
admin down
```
To open a command shell into the container:
```
admin shell
```

### Linux / Mac
Install Docker + docker-compose:
* Mac
  * Install [Docker Desktop for Mac](https://hub.docker.com/editions/community/docker-ce-desktop-mac)
* Linux
  * Install [Docker](https://docs.docker.com/engine/install/)
  * Install [docker-compose](https://docs.docker.com/compose/install/#install-compose-on-linux-systems)

Open a terminal window (Terminal Application)

To bring the docker container online, run this command:
```
./admin.sh up
```
To stop the container do this:
```
./admin.sh down
```
To open a command shell into the container:
```
./admin.sh shell
```

### Browser Access
To access from your browser:
```
http://localhost:8888/
```
* Or, if your IP addressing is working:
```
http://10.10.10.10/
```

## Templates
### Config File
Default: `/src/config/config.php`
* Delimiter: `DELIM` defaults to `%%`
* "Cards" `CARDS` defaults to `cards`
  * Represents the subdirectory under which view renderer expects to file HTML "cards"
### Cards
#### Auto-Populate All Cards
To get an HTML file to auto-populate with cards use this syntax:
```
DELIM+DIR+DELIM
```
Example: you have a subdirectory off `HTML_DIR` named `projects` and you want to load all HTML card files under the `cards` folder:
```
%%PROJECTS%%
```
#### Auto-Populate Specific Number of Cards
To only load a certain (random) number of cards, use `=`.
Example: you have a subdirectory off `HTML_DIR` named `features` and you want to load 3 random HTML card files under the `cards` folder:
```
%%FEATURES=3%%
```
#### Auto-Populate Specified Cards in a Certain Order
For each card, only use the base filename, no extension (i.e. do not add `.html`).
Example: you have a directory `HTML_DIR/blog/cards` with files `one.html`, `two.html`, `three.html`, etc.
You want the cards to be loaded in the order `one.html`, `two.html`, `three.html`, etc.:
```
%%BUNDLES=one,two,three,etc.%%
```

## Editing Pages
By default, if you enter the URL `/super/login` you're prompted to login as a super user.
Configure the username, password and secondary authentication factors in: `/src/config/config.php` under the `SUPER` config key.

### SUPER config key
Example configuration for super user:
```
// other config not shown
'SUPER' => [
    'username' => 'admin',
    'password' => 'password',
    'attempts' => 3,
    'message'  => 'Sorry! Unable to login.  Please contact your administrator',
    // array of $_SERVER keys to store in session if authenticated
    'profile'  => ['REMOTE_ADDR','HTTP_USER_AGENT','HTTP_ACCEPT_LANGUAGE','HTTP_COOKIE'],
    // change the values to reflect the names of fiels in your login.phtml form
    'login_fields' => [
        'name'     => 'name',
        'password' => 'password',
        'other'    => 'other',
        'phrase'   => 'phrase',     // CAPTCHA phrase
    ],
    'validation'   => [
        'City' => 'London',
        'Postal Code' => '12345',
        'Last Name' => 'Smith',
    ],
    'allowed_ext'  => ['html','htm'],
    'ckeditor'     => [
        'width' => '100%',
        'height' => 400,
    ],
],
// other config not shown
```
Here's a breakdown of the `SUPER` config keys

| Key | Explanation |
| :-- | :---------- |
| username | Super user login name |
| password | Super user login password |
| attempts | Maximum number of failed login attempts.  If this number is exceeded, a random third authentication field is required for login. |
| message  | Message that displayed if login fails |
| profile  | Array of `$_SERVER` keys that form the super user's profile once logged in |
| login_fields | Field names drawn from your `login.phtml` login form |
| validation   | You can specify as many of these as you want.  If the login attemp exceeds `attempts`, the SimpleHtml framework will automatically add a random field drawn from this list. |
| allowed_ext  | Only files with an extension on this list can be edited. |
| ckeditor     | Default width and height of the CKeditor screen |

## Contact Form
The skeleton app includes under `/templates` a file `contact.phtml` that implements an email contact form with a CAPTCHA
* Uses the PHPMailer package
* Configuration can be done in `/src/config/config.php` using the `COMPANY_EMAIL` key
* CAPTCHA configuration can be done in `/src/config/config.php` using the `CAPTCHA` key

## Import Feature
You can enable the import feature by setting the `IMPORT::enable` config key to `TRUE`.
The importer itself is at `/templates/site/super/import.phtml`.
Here are some notes on config file settings under the `IMPORT` config key:
* `IMPORT::delim_start`
  * tells the importer where to start cutting out content from the HTML source
  * default: &lt;body&gt;
* `IMPORT::delim_stop`
  * tells the importer where to stop cutting out content from the HTML source
  * default: &lt;/body&gt;
* `IMPORT::transform`
  * sub-array of transforms to make available to the importer
  * `callback` : anything that's callable
    * if your own PHP function or anonymous function, signature must match `SimpleHtml\Transform\TransformInterface`
  * `params` : array of parameters the callback expects
  * `description` : shows up when you run `/super/import`


