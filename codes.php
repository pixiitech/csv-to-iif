<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php require 'config.php'; 
session_start();?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="author" content="Gregory Hedrick" />
<?php
$lUser = strtolower($_SESSION['Username']);

?>
<?php include 'style.php'; ?>
<title>
Code Management
</title>
</head>
<body>

<?php

function setField($ID, $val)
{
    echo "<script>document.forms['recordinput'].elements['{$ID}'].value = '{$val}';</script>";
}

function hasSpaces($txt)
{
    if ( strpos($txt, ' ') != false )
	return true;
    if ( strpos($txt, '\n') != false )
	return true;
    if ( strpos($txt, '\r') != false )
	return true;
    return false;
}

require 'menu.php';

/* Connect to SQL Server */
$con = mysql_connect($sql_server, $sql_user, $sql_pass);
if (!$con)
	die ("Could not connect to SQL Server: " . mysql_error() . "<br />");
$db_selected = mysql_select_db($sql_db, $con);
if (!$db_selected)
	die ("Could not find database. <br />");

/* Create newly submitted Code */

if ( isset( $_POST['NameNew'] ) && ( $_POST['NameNew'] != "New Code" ))
{
    $querystring = "SELECT Name FROM Code WHERE LOWER(Name) = '{$_POST['NameNew']}'";
    if (mysql_fetch_array(mysql_query($querystring, $con)))
	echo "Code {$_POST['NameNew']} already exists, please delete the existing one.<br />";
    else
    {
	$_POST['NameNew'] = mysql_real_escape_string($_POST['NameNew']);
	$querystring = "INSERT INTO Code (Name, Accnum) VALUES ('{$_POST['NameNew']}', ";
	$querystring .= "'{$_POST['Accnum']}')";
	$result = mysql_query($querystring, $con);
	if ($result)
	    echo "Add new code {$_POST['CodeNew']} succeeded.<br />";
        else
	    echo "Add new code {$_POST['CodeNew']} failed.<br />";
    }
}

/* Delete codes */

$querystring = "SELECT * FROM Code";
$result = mysql_query($querystring, $con);
while ( $row = mysql_fetch_array($result) )
{
    if ( isset( $_POST['Delete' . $row['Idx']] ))
    {
	$querystring = "DELETE FROM Code WHERE Idx = {$row['Idx']}";
	$result = mysql_query($querystring, $con);
	if ($result)
        {
	    echo "Deleted code " . $row['Name'] . ".<br />";
	}
	else
	    echo "Failed to delete code " . $row['Name'] . ".<br />";
    }
}

/* Display codes and create HTML form */
echo "<br /><div style='text-align:center'>";
echo "<form id='recordinput' name='recordinput' method='post' action='codes.php'>";
echo "<table style='margin:0px auto' border=1 cellpadding=4 ><tbody><tr><td>Code Name</td><td>Accnum</td>";
echo "<td>Delete Code</td></tr>";
echo "<tr><td><input name='NameNew' size='25' onclick='this.value=\"\"' value='New Code' /></td>";
echo "<td><input name='Accnum' size='10'></input></td><td></td></tr>";

$querystring = "SELECT * FROM Code ORDER BY Accnum";
$result = mysql_query($querystring, $con);
while ( $row = mysql_fetch_array($result) )
{
    echo "<tr>";
    echo "<td>{$row['Name']}</td>";
    echo "<td>{$row['Accnum']}</td>";
    echo "<td><input type='checkbox' name='Delete{$row['Idx']}' />";
    echo "</tr>";
}
echo "</tbody></table>";
echo "<input type='submit' value='Save' style='text-align:center' /></form></div>";
mysql_close($con);
?>
<?php include 'footer.php'; ?>

</body>
</html>

