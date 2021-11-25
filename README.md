# FileCMS
Really simple PHP app that builds HTML files from HTML widgets.
* Includes a class that can generate and validate CAPTCHAs (uses the GD extension).
* Includes the CKEditor for full-featured editing.
* Includes an email contact form that uses PHPMailer.
* Is able to import single files from a legacy website, or can do bulk import
* Includes a complete set of transformation filters that can be applied during import, or afterwards
* Entirely file-based: does not require a database!
* Very fast and flexible.
* Once you've got it up and running, just upload HTML snippets and/or modify the configuration file.

License: Apache v2

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

## Bootstrap and Document Root
Set the website document root to `/public`
* The central point of entry is `/public/index.php`
* There is a file `.htaccess` that controls URL rewriting
* If you are using *nginx* you will need to incorporate the same logic into your primary config file
* `/public/index.php` first loads the *bootstrap* file `/bootstrap.php`
  * This file defines three key constants used throughout the program (summarized in the table shown next)
  * The bootstrap file also load the Composer autoloader
  * If you add your own classes under `/src` be sure to update `composer.json` and refresh Composer autoloading:
```
composer dump-autoload
```
Here is a summary of the three key constants defined by `/bootstrap.php`.  Change as needed.

| Constant | Default | Description |
| :------  | :------ | :----------
| BASE_DIR | Same directory as `bootstrap.php` | Project root |
| HTML_DIR | `/templates/site` | Location of HTML snippets |
| SRC_DIR  | `/src` | Location of source code |


## Templates
By default templates are stored in `/templates/site`.  You can alter this in the config file.
### Config File
Default: `/src/config/config.php`
* Delimiter: `DELIM` defaults to `%%`
* "Cards" `CARDS` defaults to `cards`
  * Represents the subdirectory under which view renderer expects to file HTML "cards"
### Layout
The overall website look-and-feel is in a single HTML file, by default in `/templates/layout/layout.phtml`.
* The view rendered by requests is injected into the layout by replacing `%%CONTENTS%%`.

### HTML
You can create HTML snippets designed to fit into `layout.phtml` any place in the designated HTML directory.
* Be sure to set the constant `HTML_DIR` in the file `/bootstrap.php`.

### Cards
Important: each `%%CARD%%` directive you add **must** be on its own line!

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
Selected transformation filters can be applied to one or more pages during the import process.

Here are some notes on config file settings under the `IMPORT` config key:
* `IMPORT::enable`
  * Set this value to `FALSE` if you do not wish this feature to be available.
* `IMPORT::delim_start`
  * tells the importer where to start cutting out content from the HTML source
  * default: &lt;body&gt;
* `IMPORT::delim_stop`
  * tells the importer where to stop cutting out content from the HTML source
  * default: &lt;/body&gt;
* `IMPORT::trusted_src`
  * list of one or more prefixes from "trusted" sources for import
  * allows you to limit where imports can be taken from
  * in case you get hacked, this prevents attackers from importing malicious from their own sites
* `IMPORT::import_file_field`
  * this file must be in JSON format
  * name of the file upload field used in the form
  * you can upload a list of URLs to import followed by a list of transforms to apply
    * the URLs key is 'URLS'
    * the 'IMPORT' key lets you override any Import configuration including the transforms to apply during import
* `IMPORT::transform`
  * sub-array of transforms to make available to the importer
  * `callback` : anything that's callable
    * if your own PHP function or anonymous function, signature must match `SimpleHtml\Transform\TransformInterface`
  * `params` : array of parameters the callback expects
  * `description` : shows up when you run `/super/import`
After logging in as the admin user, go to `/super/import`.

## Transform Feature
You can apply transformation filters on existing pages.
The importer itself is at `/templates/site/super/import.phtml`.
Included transformation classes are located in `/src/Transform`.
You can add your own by simply extending `FileCMS\Common\Transform\Base`.
After logging in as the admin user, go to `/super/transform`.

Here are some notes on config file settings under the `TRANSFORM` config key:
* `TRANSFORM::enable`
  * Set this value to `FALSE` if you do not wish this feature to be available.
* `TRANSFORM::backup_dir`
  * Directory where backups will be placed prior to transformation
* `TRANSFORM::transform_dir`
  * Directory where transform classes are found
* `TRANSFORM::transform_file_field`
  * Name of the form field that is used if you want to upload a set of transforms

## Pre-Processing
Before the final HTML view is rendered, `/public/index.php` includes `/src/processsing.php`.
In this file you can include any pre-processing you need done.
* The request URL is available as the variable `$uri`
* This is where the admin URL is captured and sent to processing
