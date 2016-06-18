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
Alias Management
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

/* Load code list */
$querystring = "SELECT * FROM Code ORDER BY Name";
$result = mysql_query($querystring, $con);
$codes = array();
while ($row = mysql_fetch_array($result))
    array_push($codes, $row['Name']);

/* Create newly submitted alias */

if ( isset( $_POST['SearchNew'] ) && ( $_POST['SearchNew'] != "New Alias" ))
{
    $lSearch = strtolower($_POST['SearchNew']);
    $querystring = "SELECT Search FROM Alias WHERE LOWER(Search) = '{$lSearch}'";
    if (mysql_fetch_array(mysql_query($querystring, $con)))
	echo "Search String {$_POST['SearchNew']} already exists, please delete the existing one.<br />";
    else
    {
	$_POST['SearchNew'] = mysql_real_escape_string($_POST['SearchNew']);
	$_POST['AliasNew'] = mysql_real_escape_string($_POST['AliasNew']);
	$querystring = "INSERT INTO Alias (Search, Alias, Code) VALUES ('{$_POST['SearchNew']}', ";
	$querystring .= "'{$_POST['AliasNew']}', '{$_POST['CodeNew']}')";
	$result = mysql_query($querystring, $con);
	if ($result)
	    echo "Add new alias {$_POST['AliasNew']} succeeded.<br />";
        else
	    echo "Add new alias {$_POST['AliasNew']} failed.<br />";
    }
}

/* Delete aliases */

$querystring = "SELECT * FROM Alias";
$result = mysql_query($querystring, $con);
while ( $row = mysql_fetch_array($result) )
{
    if ( isset( $_POST['Delete' . $row['Idx']] ))
    {
	$querystring = "DELETE FROM Alias WHERE Idx = {$row['Idx']}";
	$result = mysql_query($querystring, $con);
	if ($result)
        {
	    echo "Deleted alias " . $row['Alias'] . ".<br />";
	}
	else
	    echo "Failed to delete alias " . $row['Alias'] . ".<br />";
    }
}

/* Display aliases and create HTML form */
echo "<br /><div style='text-align:center'>";
echo "<form id='recordinput' name='recordinput' method='post' action='alias.php'>";
echo "<table style='margin:0px auto' border=1 cellpadding=4 ><tbody><tr><td>Search String</td><td>Alias</td>";
echo "<td>Code</td><td>Delete Alias</td></tr>";
echo "<tr><td><input name='SearchNew' size='25' onclick='this.value=\"\"' value='New Alias' /></td>";
echo "<td><input name='AliasNew' size='25'></input></td><td>";
echo "<select name='CodeNew'>";
for ( $i = 0; $i < count($codes); $i++ )
    echo "<option value='{$codes[$i]}'>{$codes[$i]}</option>";
echo "</select></td></tr>";

$querystring = "SELECT * FROM Alias";
$result = mysql_query($querystring, $con);
while ( $row = mysql_fetch_array($result) )
{
    echo "<tr>";
    echo "<td>{$row['Search']}</td>";
    echo "<td>{$row['Alias']}</td>";
    echo "<td>{$row['Code']}</td>";
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

