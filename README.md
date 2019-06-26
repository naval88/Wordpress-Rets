# Wordpress-Rets
How to  use RETS server to pull real estate listings, photos and other data made available from an MLS system in wordpress
Git Clone above files in wordpress folder or name the folder cron.
Cron.php is main file you need cridential 
$rets_login_url 				=	'';
$rets_username 					=   '';
$rets_password			 		=   '';

Cross check cridential working fine by login 
https://retsmd.com/ here

Above site play an importan role in over all development

Simple example to get list of properties
<?php
$property_classes = array('Listing');
$count = 1;
foreach ($property_classes as $class_name) {
	$list_date = "2015-06-15-2015-07-15";
	$search = $rets->SearchQuery("Property",$class_name,"(MatrixModifiedDT=$list_date)",array( 'Format' => 'COMPACT-DECODED', 'Count' => 1, 'StandardNames' => 0));			
	while ($listing = $rets->FetchRow($search))
	{
		echo "<pre>";
		print_R($listing);
		echo "</pre>";
	}
}
?>

Let me know if you need more help 
Email address : navalkishor2005@gmail.com
Skype id : naval.kishor66
        
        
			
