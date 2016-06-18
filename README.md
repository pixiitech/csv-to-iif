# CSV to IIF

This program is designed to convert CSV files to Quickbooks IIF format. 
It also can code your transactions before they are imported into Quickbooks by searching the transaction descriptions for keywords and coding accordingly. Useful with web transaction ledgers that don't support Quickbooks.

It has only been tested with Quickbooks 2012, but may work with other versions.

## INSTALL:
1. Copy the included files to your PHP-enabled web server.
2. Make sure MySQL is installed on your server.
3. Edit config.php, and enter your MySQL credentials like so:
```
$sql_user = " ... ";
$sql_pass = " ... ";
$sql_server = "localhost"; //or put your external hostname here
```

## USAGE
1. Load the main page in your browser: `http://servername/iif`
2. Set up your account codes under 'Manage Codes' i.e.:
> Advertising	6020	
> Bank Service Charges	6060	
> Business License & Fees	6090	
> Car/Truck Expense	6100
3. Under 'Manage Aliases', start by adding your most common payees. When you add records here, the software will look for the 'Search String' and substitute it for the full string in the 'Alias' column. Useful for hard-to-read POS output seen on statements. ie, LITTLE CAESA becomes Little Caesar's. Select the accounting code from the dropdown box and click 'Save'.
4. Go back to the main 'Conversion' page
5. Enter your bank account name as it is shown in QuickBooks
6. Select transaction type (Checks and Deposits must be processed separately)
7. The default format string works in most cases
8. Paste or load your CSV data from a file. Make sure the data is free from extraneous commas and apostrophes, these will not import correctly. Amounts should be positive for both checks and deposits, omitting the dollar sign.
9. Click 'Upload'
10. Copy the output and paste it into Notepad (or a plain text editor) and save to a .iif file.
11. Open Quickbooks, and import from IIF file and chose your saved file.
12. Check your ledger and make sure the amount is balanced.

## COPYING:
This software is free to use under the MIT license.

## AUTHOR:
Gregory Hedrick
www.pixiitech.net