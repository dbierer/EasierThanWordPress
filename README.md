# FileCMS
NOTE: formerly called _SimpleHtml_

Simple PHP framework that builds HTML files from HTML widgets.
* Includes a class that can generate and validate CAPTCHAs (uses the GD extension).
* Includes the CKEditor for full-featured editing.
* Includes an email contact form that uses PHPMailer.
* Is able to import single files from a legacy website, or can do bulk import
* Includes a complete set of transformation filters that can be applied during import, or afterwards
* Entirely file-based: does not require a database!
* Very fast and flexible.
* Once you've got it up and running, just upload HTML snippets and/or modify the configuration file.
* Works on PHP 8.1

License: Apache v2

## Initial Installation
1. Clone the `filecms-website` repository to the project root of your new website.
  * If you have `git` installed run this command from a command prompt / terminal window:
```
git clone https://github.com/dbierer/filecms-website.git /path/to/website
```
  * If you don't have `git` installed, just download the ZIP file from:
```
https://github.com/dbierer/filecms-website/archive/refs/heads/main.zip
```
  * And unzip into `/path/to/website`
2. Use composer to install `unlikelysource/filecms-core` and 3rd party source code (e.g. PHPMailer)
```
cd /path/to/website
wget https://getcomposer.org/download/latest-stable/composer.phar
php composer.phar self-update
php composer.phar install
```

## Basic website config
All references are from `/path/to/website`

* Primary config file: `/src/config/config.php`
* Bootstrap file: `/bootstrap.php`
* Pre-processing code: `/src/processing.php`

Additional documentation on these three follows.

## To Run Locally Using PHP
From this directory, run the following command:
```
cd /path/to/website
php -S localhost:8888 -t public
```

## To Run Locally Using Docker and docker-compose

### Windows
Install [Docker Desktop for Windows](https://hub.docker.com/editions/community/docker-ce-desktop-windows)

Open the Power Shell (some commands don't work in the regular command prompt)

To bring the docker container online, run this command:
```
cd \path\to\website
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
cd /path/to/website
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
  * The bootstrap file also loads the Composer autoloader
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

## Pre-Processing
Before the final HTML view is rendered, `/public/index.php` includes `/src/processsing.php`.
In this file you can include any pre-processing you need done.
* The request URL is available as the variable `$uri`
* This is where the admin URL (e.g. `/super`) is captured and sent to processing

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
    'profile'  => ['REMOTE_ADDR','HTTP_ACCEPT_LANGUAGE'],
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

## Clicks
A class `FileCMS\Common\Stats\Clicks` was added as of version 0.2.1.
Records the following information into a CSV file:
* URL
* Date
* Time
* IP address
* Referrer
* 1
The "1" can be used in a spreadsheet to create totals by any of the other fields.
After logging in as the admin user, go to `/super/clicks`.
### Statistical Methods
The following methods are available for your use:
#### Clicks::get(string $click_fn) : array
Returns an array keyed and sorted by URL, with hit grand totals.
#### Clicks::get_by_page_by_day(string $click_fn) : array
Returns an array keyed and sorted by URL + Y-m-d, with hit totals for each day
#### Clicks::get_by_path(string $click_fn, string $path) : array
Returns the same as `get_by_page_by_day()` except that it filters results based on `$path`.
Use this to return stats on URLs such as `/practice/dr_tom/`.

## Change Log
### tag: v0.2.2 / v0.2.3
* 2022-04-22 DB: Finished testing modifications to Profile
* 2022-04-18 DB: Updated tests
* 2022-02-17 DB: Added option to prevent %%CARDS%% tags from being overwritten + implemented messages marker replacement for static HTML pages + expanded tests
### tag: v0.2.1
* 2022-02-16 DB: Updated tests + removed user key from Common\Security\Profile
### tag: v0.2.2
* 2022-02-13 DB: Fixed bug whereby you can never login
### tag: v0.2.4
2022-05-12 DB:
* Created `Email::trustedSend()` that allows you to directly call the core email send function
* Refactored `Email::confirmAndSend()` to call `trustedSend()`
* Added `$debug` option to facilitate testing and debugging
  * If set `TRUE` the email is not actually sent, and a `PHPMailer` instance is set to `Email::$phpMailer`
* Added `FileCMSTest\Common\Contact\EmailTest` test class
### tag: v0.2.5
* `FileCMS\Common\Contact\Email::trustedSend()`
  * Fixed bug whereby you were only allowed to send a string to `$cc` and `$bcc`
  * These inputs now allowed `mixed` types (expected string|array)
* Updated `FileCMSTest\Common\Contact\EmailTest` and `FileCMSTest\Common\Security\ProfileTest`
* `FileCMS\Common\Import\Import`
  * Added message if URL not found
  * Wrapped `file_get_contents($url)` call in `try` / `catch` to prevent expected errors from messing up test results
### tag: v0.2.6
* `FileCMS\Common\Contact\Email::trustedSend()`
  * Fixed bug whereby PHPMailer was always set to SMTP regardless of config settings
* Updated `FileCMSTest\Common\Contact\EmailTest`
  * Added tests to see if PHPMailer instance is set to "smtp" or "mail"
### tag: v0.2.8
* `FileCMS\Common\Contact\Email::confirmAndSend()`
  * Removed CAPTCHA verification logic and put into new `AntiSpam` class
* `FileCMS\Common\Contact\AntiSpam`
  * Added static function `verifyCaptcha($config)`
### tag: v0.2.9
* `FileCMS\Common\Security\Profile::verify()`
  * Removed type-hint from method signature for backward compatibility
### tag: v0.2.10
Date:   Sat Jun 25 16:42:03 2022 +0700
* 2022-06-25 DB: Enhancing security in Common\Contact\Email
* 2022-06-21 DB: Minor fix to Common\Security\Profile::verify()
### tag: v0.2.11
Date:   Sun Jul 10 12:50:24 2022 +0700
Update Clicks.php
Added new column
* `add()` includes `json_encode($_GET)`
* `raw_get()` does `json_decode()` on new column
* Updated `CLICK_HEADERS`


