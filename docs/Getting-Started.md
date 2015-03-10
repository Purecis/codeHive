##Getting Started
***
Duplicate `sample` App from `apps/sample` to new folder and rename it as your project name, for example `myproject` then you can edit path in `index.php` and change the app path to `app/myproject`.

dont forget to edit the `.htaccess` file and set the base for your project to let the Router work fine.

you can set your app title and information from the config file in your project `app/myproject/config.php` and change the default values to your requirements, you can remove all unnessesary data from the file if you decide not to use them, for example: if you dont want to use database  just remove the `connection` array, and if you dont want to use uploading and library remove `upload` array, and you can set the Environment type.



###Set the Development Environment

`Requirements`

- Apache2+
- PHP 5.4+
- MySQL 5.4+
- mod_rewrite in apache

##### Windows
 
use latest version of WAMP, MAMP, XAMP, AppServ with some customization for `php.ini` file
 
##### Mac
latest version of MAMP with `php.ini` customizations

##### Linux
you can use LAMP or install apache, php, mysql seperatly via your package manager

##### Web Hosting
check the requirements then upload and have fun



### PHP.ini Customization
1. short_open_tag = off
2. error_reporting = E_ALL ~ E_NOTICE

