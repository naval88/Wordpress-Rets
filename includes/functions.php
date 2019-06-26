<?php
ini_set ('max_execution_time', 300);
function Generate_Featured_Image( $image_url, $post_id  ){
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = $post_id.".jpg";
    if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
    else                                    $file = $upload_dir['basedir'] . '/' . $filename;
    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
    $res2= set_post_thumbnail( $post_id, $attach_id );
}
function get_category_id($cat_name){
    $term = get_term_by('name', $cat_name, 'product_cat');
    return $term->term_id;
}
function create_table_sql_from_metadata($table_name, $rets_metadata, $key_field, $field_prefix = "") {

        $sql_query = "CREATE TABLE {$table_name} (\n";

        foreach ($rets_metadata as $field) {

                $field['SystemName'] = "`{$field_prefix}{$field['SystemName']}`";
				

                $cleaned_comment = addslashes($field['LongName']);

                $sql_make = "{$field['SystemName']} ";

                if ($field['Interpretation'] == "LookupMulti") {
                        $sql_make .= "TEXT";
                }
                elseif ($field['Interpretation'] == "Lookup") {
                        $sql_make .= "VARCHAR(50)";
                }
                elseif ($field['DataType'] == "Int" || $field['DataType'] == "Small" || $field['DataType'] == "Tiny") {
                        $sql_make .= "INT({$field['MaximumLength']})";
                }
                elseif ($field['DataType'] == "Long") {
                        $sql_make .= "BIGINT({$field['MaximumLength']})";
                }
                elseif ($field['DataType'] == "DateTime") {
                        $sql_make .= "DATETIME default '0000-00-00 00:00:00' not null";
                }
                elseif ($field['DataType'] == "Character" && $field['MaximumLength'] <= 255) {
                        $sql_make .= "VARCHAR({$field['MaximumLength']})";
                }
                elseif ($field['DataType'] == "Character" && $field['MaximumLength'] > 255) {
                        $sql_make .= "TEXT";
                }
                elseif ($field['DataType'] == "Decimal") {
                        $pre_point = ($field['MaximumLength'] - $field['Precision']);
                        $post_point = !empty($field['Precision']) ? $field['Precision'] : 0;
                        $sql_make .= "DECIMAL({$field['MaximumLength']},{$post_point})";
                }
                elseif ($field['DataType'] == "Boolean") {
                        $sql_make .= "CHAR(1)";
                }
                elseif ($field['DataType'] == "Date") {
                        $sql_make .= "DATE default '0000-00-00' not null";
                }
                elseif ($field['DataType'] == "Time") {
                        $sql_make .= "TIME default '00:00:00' not null";
                }
                else {
                        $sql_make .= "VARCHAR(255)";
                }

                $sql_make .= " COMMENT '{$cleaned_comment}'";
                $sql_make .= ",\n";

                $sql_query .= $sql_make;
        }

        $sql_query .= "PRIMARY KEY(`{$field_prefix}{$key_field}`) )";
	
        return $sql_query;
}





function create_table_sql_from_metadata1($table_name, $rets_metadata, $key_field, $field_prefix = "") {
	$sql_query = "CREATE TABLE ".$table_name." (\n";
	foreach ($rets_metadata as $field) {
		$cleaned_comment = addslashes($field->getLongName());
		$sql_make = "\t`" . $field_prefix . $field->getSystemName()."` ";
		if ($field->getInterpretation() == "LookupMulti") {
			$sql_make .= "TEXT";
		} elseif ($field->getInterpretation() == "Lookup") {
			$sql_make .= "VARCHAR(50)";
		} elseif ($field->getDataType() == "Int" || $field->getDataType() == "Small" || $field->getDataType() == "Tiny") {
			$sql_make .= "INT(".$field->getMaximumLength().")";
		} elseif ($field->getDataType() == "Long") {
			$sql_make .= "BIGINT(".$field->getMaximumLength().")";
		} elseif ($field->getDataType() == "DateTime") {
			$sql_make .= "DATETIME default '0000-00-00 00:00:00' NOT NULL";
		} elseif ($field->getDataType() == "Character" && $field->getMaximumLength() <= 255) {
			$sql_make .= "VARCHAR(".$field->getMaximumLength().")";
		} elseif ($field->getDataType() == "Character" && $field->getMaximumLength() > 255) {
			$sql_make .= "TEXT";
		} elseif ($field->getDataType() == "Decimal") {
			$pre_point = ($field->getMaximumLength() - $field->getPrecision());
			$post_point = !empty($field->getPrecision()) ? $field->getPrecision() : 0;
			$sql_make .= "DECIMAL({$field->getMaximumLength()},{$post_point})";
		} elseif ($field->getDataType() == "Boolean") {
			$sql_make .= "CHAR(1)";
		} elseif ($field->getDataType() == "Date") {
			$sql_make .= "DATE default '0000-00-00' NOT NULL";
		} elseif ($field->getDataType() == "Time") {
			$sql_make .= "TIME default '00:00:00' NOT NULL";
		} else {
			$sql_make .= "VARCHAR(255)";
		}
		$sql_make .=  " COMMENT '".$cleaned_comment."',\n";
		$sql_query .= $sql_make;
	}
	$sql_query .=  "PRIMARY KEY(`".$field_prefix.$key_field."`) )";
	return $sql_query;
}

/*
*http://www.codexworld.com/get-latitude-longitude-from-address-using-google-maps-api-php/
*
*
*/
function getLatLong($address){
    if(!empty($address)){
        //Formatted address
        $formattedAddr = str_replace(' ','+',$address);
        //Send request and receive json data by address
		$url = "https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddr.'&sensor=false&key=AIzaSyDblX96WMSh70vFX3qvnwQoXPmVuZ05qaM";
		$geocodeFromAddr = file_get_contents($url); 
        $output = json_decode($geocodeFromAddr);
        //Get latitude and longitute from json data
        $data['latitude']  = $output->results[0]->geometry->location->lat; 
        $data['longitude'] = $output->results[0]->geometry->location->lng;
        //Return latitude and longitude of the given address
        if(!empty($data)){
            return $data;
        }else{
            return false;
        }
    }else{
        return false;   
    }
}

?>
