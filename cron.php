<?php
require_once('../wp-config.php');
require_once('includes/functions.php');
$url_custom 		=   $_SERVER['DOCUMENT_ROOT']."/sunny";
$perfix 			=   $wpdb->prefix;
//echo $perfix; 	
//$url_custom 		=   $_SERVER['DOCUMENT_ROOT'];
$result				=   $wpdb->get_results("select last_date from 	".$perfix."rets_cron_propertydate  where id =1");
$start_date    		=   $result[0]->last_date;
//$date			    =   strtotime("+1 hours", strtotime($start_date));
$date			    =   strtotime("+1 day", strtotime($start_date));
$end_date  			=   date("Y-m-d\TH:i:s", $date);
$list_date	    	=  "$start_date-$end_date";
echo $list_date;
$dt 				= 		new DateTime();
$today_date	 		= 		$dt->format('Y-m-d');
$date1 				= 		strtotime($today_date);
$date2 				= 		strtotime($start_date);
$dateDiff 			= 		$date1 - $date2;
$days      			= 		floor($dateDiff/(60*60*24));
$days;
if($days<=0)
{
echo "There is no data to display records";
exit; 
} 
/*$rets_login_url 				=	'http://rets.torontomls.net:6103/rets-treb3pv/server/login';
$rets_username 					=  'D14mjd';
$rets_password			 		=  'Bh$5739';
$rets_user_agent 				= "D14mjd_a";
$rets_user_agent_password 		= "Bh$5739"; */


$rets_login_url 				=	'http://rets.sef.mlsmatrix.com/Rets/login.ashx';
$rets_username 					=   'lanAERbel';
$rets_password			 		=   '8050';

// use http://retsmd.com to help determine the names of the classes you want to pull.
// these might be something like RE_1, RES, RESI, 1, etc.
//////////////////////////////
require_once("rets.php");
//echo "hi" ;
// start rets connection
$rets = new phRETS;
// only enable this if you know the server supports the optional RETS feature called 'Offset'
//$rets->SetParam("offset_support", true);
//$rets->AddHeader("PHRETS/1.7", 'Bh$5739');
//$rets->AddHeader("User-Agent", $rets_user_agent);
echo "+ Connecting to {$rets_login_url} as {$rets_username}<br>\n";
$connect = $rets->Connect($rets_login_url,$rets_username,$rets_password);
if ($connect) {
			echo "  + Connected<br>\n";	
}
else {
        echo "  + Not connected:<br>\n";
        print_r($rets->Error());
       exit;
}
die();
$tbName    = $perfix.'rets_property_listing';
$query     = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$tbName'";
$dataArr   = $wpdb->get_results($query,ARRAY_A);

/* echo "<pre>";
print_R($dataArr);
echo "</pre>";
die();  */
$property_classes = array('Listing');
$count = 1;
foreach ($property_classes as $class_name) {
	//echo $list_date = "2015-06-15-2015-07-15";
			$search = $rets->SearchQuery("Property",$class_name,"(MatrixModifiedDT=$list_date)",array( 'Format' => 'COMPACT-DECODED', 'Count' => 1, 'StandardNames' => 0));			
			echo "    +  $class_name Total found: {$rets->TotalRecordsFound()}<br>\n";	
				$mcount=1;
				while ($listing = $rets->FetchRow($search))
				{
					/* echo "<pre>";
					print_R($listing);
					echo "</pre>";
					die();  */						
					$property_id			= $listing['MLSNumber'];
					$matrixUniquId			= $listing['Matrix_Unique_ID'];
					$p_status 				= $listing['Status'];

					switch($p_status )
					{
						case 'Active':
						$res = "Sale";
						break;
						case 'Backup Contract-Call LA':
						$res = "Sale";
						break;
						case 'Cancelled':
						$res = "Sale";
						break;
						case 'Closed Sale':
						$res = "Sold";
						break;
						case 'Incoming':
						$res = "Sale";
						break;
						case 'Pending Sale':
						$res = "Sale";
						break;
						case 'Terminated':
						$res = "Sale";
						break;
						case 'Rented':
						$res = "Rent";
						break;
					}
					
					echo "Status :: $p_status";
					$result				=   $wpdb->get_results("select matrix_unique_id,post_id from .".$perfix."rets_listing_id where matrix_unique_id = '$matrixUniquId'");
					$matrixUniquId    		=   $result[0]->matrixUniquId;
					$post_id    			=   $result[0]->post_id;
					if($matrixUniquId!="")
					{
					if($p_status == 'Active' || $p_status == 'Closed Sale' || $p_status == 'Pending Sale' || $p_status == 'Rented')
					{	
					$post_content			= $listing['Remarks'];	
					$street_no				= $listing['StreetNumber'];
					$street_d				= $listing['StreetDirPrefix'];
					$street_name			= $listing['StreetName'];
					$stree_sufix		    = $listing['St_sfx'];
					$street_ds			    = $listing['StreetDirSuffix'];		
					$county					= $listing['StateOrProvince'];
					$zipcode				= $listing['PostalCode'];	
					//Condo-Status-Complex Name mls-A2076030-in City Name
					$complexName		= $listing['ComplexName'];	
					$city					= $listing['City'];
					
					$post_title             = "Condo $res $complexName MLS $property_id IN $city";				
					$address				= "$street_no  $street_name $stree_sufix $street_ds  $county $zipcode";					
				
					$dataInsertMain['post_id'] = $post_id;
					foreach($dataArr as $dataVal)
					{
						$fieldName = $dataVal['COLUMN_NAME'];
		        		 if(array_key_exists($fieldName,$listing))
						{
						  $dataInsertMain[$fieldName] = $listing[$fieldName];
						}					
					}
						
						$post_information = array(
						'ID' => $post_id,
						'post_status' =>  'publish',
						);
						wp_update_post($post_information);
						
						$tableName  = $wpdb->prefix.'rets_property_listing';						
						$wpdb->insert($tableName,$dataInsertMain);							
							
						$tableName  = $wpdb->prefix.'rets_listing_id';
						$dataInsert  = array('listing_status'=>$p_status);
						$where = array('post_id' => $post_id);
						$wpdb->update($tableName, $dataInsert, $where);							
					}
					else
					{
						echo "trash start";
						$post_information = array(
						'ID' => $post_id,
						'post_status' =>  'draft',
						);
						wp_update_post($post_information);						
						$tableName  = $wpdb->prefix.'rets_listing_id';
						$dataInsert  = array('listing_status'=>$p_status);
						$where = array('post_id' => $post_id);
						$wpdb->update($tableName, $dataInsert, $where);	
					}
						
						
					}
					else{
					echo "Insert";		
					$property_id			= $listing['MLSNumber'];
					$matrixUniquId			= $listing['Matrix_Unique_ID'];					
					$p_status 				= $listing['Status'];		
					if($p_status == 'Active' || $p_status == 'Closed Sale' || $p_status == 'Pending Sale' || $p_status == 'Rented')
					{	
					$post_content			= $listing['Remarks'];	
					$street_no				= $listing['StreetNumber'];
					$street_d				= $listing['StreetDirPrefix'];
					$street_name			= $listing['StreetName'];
					$stree_sufix		    = $listing['St_sfx'];
					$street_ds			    = $listing['StreetDirSuffix'];		
					$county					= $listing['StateOrProvince'];
					$zipcode				= $listing['PostalCode'];	
					$complexName			= $listing['ComplexName'];	
					$city					= $listing['City'];
					
					$post_title             = "Condo $res $complexName MLS $property_id IN $city";						
					$address				= "$street_no  $street_name $stree_sufix $street_ds  $county $zipcode";
					$post_id = wp_insert_post(array (
					'post_type' => 'listing',
					'post_title' => $post_title,
					'post_content' => $post_content,
					'post_author' => 1,
					'post_status' => 'publish',
					));	
					
					$dataInsertMain['post_id'] = $post_id;
					foreach($dataArr as $dataVal)
					{
						$fieldName = $dataVal['COLUMN_NAME'];
		        		if(array_key_exists($fieldName,$listing))
						{
						  $dataInsertMain[$fieldName] = $listing[$fieldName];
						}					
					}
			
										
					$tableName  = $wpdb->prefix.'rets_property_listing';						
					$wpdb->insert($tableName,$dataInsertMain);							
			
					$year_built  = $listing['YearBuilt'];	
					$bed  		 = $listing['BedsTotal'];	
					$bathroom  	 = $listing['BathsFull'];	
					$pType		 = $listing['PropertyType'];
					
					update_post_meta($post_id,"year_built",$year_built);
					update_post_meta($post_id,"bed",$bed);
					update_post_meta($post_id,"bathroom",$bathroom);
					update_post_meta($post_id,"status",$p_status);
					update_post_meta($post_id,"property_type",$pType);
					update_post_meta($post_id,"mls_id",$property_id);
					update_post_meta($post_id,"matrix_unique_id",$matrixUniquId);
				   
					$sphotos		  = $rets->GetObject("Property", "Photo", $matrixUniquId,"*",1);
					if(is_array($sphotos))
					{
						$photo_count  = sizeOf($sphotos);
						$dataSmImg 	  = json_encode($sphotos);
					}
					
					$photos		  = $rets->GetObject("Property", "HighRes", $matrixUniquId,"*",1);
					$dataImg 	  = json_encode($photos);					
					$tableName   = $wpdb->prefix.'largeimage';
					$dataInsert  = array('post_id'=>$post_id,'large_image'=>$dataImg,'photo_image'=>$dataSmImg);
					$wpdb->insert($tableName,$dataInsert);
					
					$latLong     = getLatLong($address);
					$latitude    = $latLong['latitude']?$latLong['latitude']:'Not found';
					$longitude   = $latLong['longitude']?$latLong['longitude']:'Not found';
					$tableName   = $wpdb->prefix.'rets_listing_id';
					$dataInsert  = array('matrix_unique_id'=>$matrixUniquId,'post_id'=>$post_id,'lat'=>$latitude,'lng'=>$longitude,'pic_count'=>$photo_count,'type'=>$class_name,'listing_status'=>$p_status);
					$wpdb->insert($tableName,$dataInsert);							
					}
					} /* else end here */				
					
					if($mcount>=1)
					{
						die('Please stop');
					}
					$mcount++;
				//die();	
			  	}			
				/* end */
				$tableName  = $wpdb->prefix.'rets_cron_propertydate';
				$dataInsert  = array('last_date'=>$end_date);
				$where = array('id' => 1);
				$wpdb->update($tableName, $dataInsert, $where);
				/* end */
}
echo "+ Disconnecting<br>\n";
$rets->Disconnect();
?>









