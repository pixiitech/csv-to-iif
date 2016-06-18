<?php
global $cols, $con, $sql_server, $sql_user, $sql_pass;

$sql_user = "root";
$sql_pass = "battleship787";
$sql_server = "localhost";
$sql_db = "IIF";
$logoPic = "gazebo-wb2.png";
$commName = "CSV to IIF";
$rootdir = "/var/www/iif/";

$encryption_salt = "pixii";
$max_upload_size = 8000000;		//8 MB

$session_expiration = 60*60*24;
$cookie_expiration = 60*60*24;		//24 hours

//Define how privilege levels are saved in the database
$level_logout = -1;
$level_disabled = 0;
$level_user = 1;
$level_admin = 2;
$level_developer = 3;

//Privilege Level names
$levels = array("Disabled", "User", "Admin", "Developer");


//Color Scheme names
$colorschemes = array("Aqua (default)", "White on Blue", "Pink Panther",
		"Rainy Day", "Mainframe", "Honeycomb",
		"Ultraviolet", "No Style");
?>
