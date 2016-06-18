<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php require 'config.php'; 
session_start();?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="author" content="Gregory Hedrick" />
<?php include 'style.php'; ?>
<title>
CSV to IIF
</title>
</head>
<body>

<?php
function setField($ID, $val)
{
    echo "<script>document.forms['recordinput'].elements['{$ID}'].value = '{$val}';</script>";
}
include 'menu.php'; 

echo "<h2 style='text-align:center'>CSV to IIF</h2>";

/* Connect to SQL Server */
$con = mysql_connect($sql_server, $sql_user, $sql_pass);
if (!$con)
	die ("Could not connect to SQL Server: " . mysql_error() . "<br />");
$db_selected = mysql_select_db($sql_db, $con);
if (!$db_selected)
	die ("Could not find database. <br />");

$outputbuffer = "";

if (isset($_POST['data']))
{
	$dataparse = $_POST['data'];
	$format = $_POST['format'];
	$fieldlist = array();
	if ($_POST['newline'] == 'Windows')
	    $newline = "\r\n";
	if ($_POST['newline'] == 'Linux')
	    $newline = "\n";
	if ($_POST['newline'] == 'Mac')
	    $newline = "\r";
	if ($_POST['newline'] == 'Windows')
	    $nllength = 2;
	else
	    $nllength = 1;
	$dellength = strlen($_POST['delimiter']);

	//Parse format string
	for ($fieldcount = 0; $format != ""; $fieldcount++)
	{
		$delpos = strpos($format, $_POST['fsdelimiterspecify']);
		if ( $delpos != false )
	            $Name = substr($format, 0, $delpos);
		else
		    $Name = $format;
		$Name = trim($Name);
	        if ($Name == "")
		   break;
	        $format = substr($format, $delpos + $dellength);
	        $fieldlist[$fieldcount] = $Name;
		if  ($delpos == false)
		   break;
	}

	//Parse data
	$rowcount = 0;
	$outputbuffer .= "!TRNS,TRNSID,TRNSTYPE,DATE,ACCNT,NAME,AMOUNT,DOCNUM,CLEAR
!SPL,SPLID,TRNSTYPE,DATE,ACCNT,NAME,AMOUNT,DOCNUM,CLEAR
!ENDTRNS
";
	while ($dataparse != "")
	{
	    $rowdata = array();
	    $break = false;
	    for ( $n = 0; (($break == false) && ($dataparse != "")); $n++ )
	    {
			$nlpos = strpos($dataparse, $newline);
			$delpos = strpos($dataparse, $_POST['delimiter']);
			if ( $delpos === false ) //Last field before EOF
			{
			    $rowdata[$fieldlist[$n]] = trim($dataparse);
			    $dataparse = "";
			    $break = true;
			}
			else if ( $nlpos === false ) //Last line, more fields
			{
			    $rowdata[$fieldlist[$n]] = trim(substr($dataparse, 0, $delpos));
			    $dataparse = substr($dataparse, $delpos + $dellength);
			}
			else if ( $nlpos < $delpos ) //Last field of not last line
			{
			    $rowdata[$fieldlist[$n]] = trim(substr($dataparse, 0, $nlpos));
			    $dataparse = substr($dataparse, $nlpos + $nllength);
			    $break = true;
			}
			else	//More fields, more lines
			{
			    $rowdata[$fieldlist[$n]] = trim(substr($dataparse, 0, $delpos));
			    $dataparse = substr($dataparse, $delpos + $dellength);
			}
	    }

	    //if ( $rowdata[0] == '' )
		//continue;

	    $rowdata['NAME'] = mysql_real_escape_string($rowdata['NAME']);
	    $lName = strtolower($rowdata['NAME']);
	    $querystring = "SELECT * FROM Alias WHERE '{$lName}' LIKE CONCAT('%', LOWER(Search), '%')";
	    //echo $querystring . "<br />";
	    $result = mysql_query($querystring, $con);
	    $row = mysql_fetch_array($result);
	    if ($row)
	    {
		$rest = stristr($rowdata['NAME'], $row['Search']);
		if ( strstr($row['Alias'], '%') )
		    $rowdata['NAME'] = strstr($row['Alias'], '%', true) . substr($rest, strlen($row['Search']));
		else if ( strstr($row['Alias'], '$') )
		    $rowdata['NAME'] = stristr($rowdata['NAME'], $row['Search'], true) . substr($row['Alias'], 1);
		else
	    	    $rowdata['NAME'] = $row['Alias'];
		$rowdata['ACCNT'] = $row['Code'];
	    }
	    else
		$rowdata['ACCNT'] = $_POST['miscacct'];

	    if (!isset($rowdata['CLEAR']))
	    	$rowdata['CLEAR'] = 'N';

	    switch ( $_POST['TRNSTYPE'] )
	    {
		case 'CHECK':
	    		$outputbuffer .= "TRNS,{$rowdata['TRNSID']},CHECK,{$rowdata['DATE']},\"{$_POST['bank']}\",\"{$rowdata['NAME']}\",\"-{$rowdata['AMOUNT']}\",,{$rowdata['CLEAR']}
SPL,,{$_POST['TRNSTYPE']},{$rowdata['DATE']},\"{$rowdata['ACCNT']}\",{$rowdata['DOCNUM']},\"{$rowdata['AMOUNT']}\",,{$rowdata['CLEAR']}
ENDTRNS


";
		    break;
		case 'DEPOSIT':
	    		$outputbuffer .= "TRNS,{$rowdata['TRNSID']},DEPOSIT,{$rowdata['DATE']},\"{$_POST['bank']}\",\"{$rowdata['NAME']}\",\"{$rowdata['AMOUNT']}\",,{$rowdata['CLEAR']}
SPL,,{$_POST['TRNSTYPE']},{$rowdata['DATE']},\"{$rowdata['ACCNT']}\",{$rowdata['DOCNUM']},\"-{$rowdata['AMOUNT']}\",,{$rowdata['CLEAR']}
ENDTRNS


";
		    break;
	    }
	    $rowcount++;
	}  
	echo $rowcount . " transaction(s) processed.<br />";
}

echo "<br />
<div class='recordinput'>
<form name='sqlupload' method='post' action='index.php'>
<table class='criteria'><tbody>
<tr><td colspan='2'><b>Format Field Delimiter: </b>
<input type='text' name='fsdelimiterspecify' size='2' value=\",\" /></td></tr>
<tr><td colspan='2'><b>   Data Field Delimiter: </b> 
<input type='text' name='delimiter' size='2' value=',' /></td></tr>
<tr><td colspan='2'><b>   Newline </b><input type='radio' name='newline' value='Linux' checked='true' /> Linux \\n (default)  
		  <input type='radio' name='newline' value='Windows' /> Windows \\r\\n  
		  <input type='radio' name='newline' value='Mac' /> Mac \\r  <br /><br /></td></tr>
<tr><td colspan='2'><b>Bank account name (in QB):</b> <input type='text' name='bank' size='35' value='{$_POST['bank']}'/>
<br />
<i>ie, Free Checking 1122</i>
</td></tr>
<tr><td colspan='2'><b>Transaction type:</b> <input type='radio' name='TRNSTYPE' value='CHECK' checked='true' /> Check <input type='radio' name='TRNSTYPE' value='DEPOSIT' /> Deposit 
<tr><td colspan='2'><b>Miscellaneous account coding:</b> <input type='text' size='40' name='miscacct' value='Miscellaneous' />
<tr><td><b>Format String </b><br />
<input type='file' id='loadFormatString' name='loadFormatString' size='1' />
</td>
<td>Acceptable fields:<br />
TRNSID,TRNSTYPE,DATE,ACCNT,NAME,AMOUNT,DOCNUM,CLEAR,SPLID<br />
<textarea name='format' rows='4' cols='100'>";
if ( isset( $_POST['format'] ))
    echo $_POST['format'];
else
    echo "DATE,NAME,AMOUNT";
echo "</textarea></td></tr>
<tr><td><b>CSV Data</b><br />
<input type='file' id='loadData' name='loadFormatString' size='1' />
</td>
<td><textarea name='data' rows='8' cols='100'>{$_POST['data']}</textarea></td></tr>
<tr><td><b>Output Window</b></td>
<td><textarea name='output' rows='8' cols='100'>{$outputbuffer}</textarea></td></tr>
<td colspan='2'><input type='submit' value='Upload' /><input type='reset' value='Clear' onclick='document.forms[\"sqlupload\"].elements[\"format\"].value = \"\"; document.forms[\"sqlupload\"].elements[\"data\"].value = \"\"; document.forms[\"sqlupload\"].elements[\"output\"].value = \"\";' />
</td></tr></tbody></table>
</form></div>";
//Script to enable text file upload
echo "<script type='text/javascript'>
  function readFormatFile(evt) {
    var f = evt.target.files[0]; 
    if (f) {
      var r = new FileReader();
      r.onload = function(e) { 
	      var contents = e.target.result;
	      document.forms['sqlupload'].elements['format'].value = contents;
      }
      r.readAsText(f);
    } else { 
      alert('Failed to load file');
    }
  }
  function readDataFile(evt) {
    var f = evt.target.files[0]; 
    if (f) {
      var r = new FileReader();
      r.onload = function(e) { 
	      var contents = e.target.result;
	      document.forms['sqlupload'].elements['data'].value = contents;
      }
      r.readAsText(f);
    } else { 
      alert('Failed to load file');
    }
  }
  document.getElementById('loadFormatString').addEventListener('change', readFormatFile, false);
  document.getElementById('loadData').addEventListener('change', readDataFile, false);
</script>";
mysql_close($con);
?>
<?php include 'footer.php';?>

</body>
</html>