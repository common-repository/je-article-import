<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

global $wpdb;
$tablename = $wpdb->prefix . "options";
	
// remove plugin options
delete_option( 'je_ai_setting_header' );
delete_option( 'je_ai_setting_seperator' );
delete_option( 'je_ai_setting_footer' );
delete_option( 'je_ai_db_version' );

//drop all custom je_ai_setting_mailreciptient_%
$option_mailreciptient = $wpdb->get_results( $wpdb->prepare ( "SELECT option_name, option_value FROM $tablename WHERE option_name LIKE 'je_ai_setting_mailreciptient_%%'", $p_tablename), ARRAY_A);
If (count($option_mailreciptient) != NULL)
{
	$i = 0;
	foreach ($option_mailreciptient as $i => $value)
	{
		delete_option( $option_mailreciptient[$i]["option_name"] );
		$i++;
	}
}

//drop all custom je_ai_setting_imageid_%
$option_imageid = $wpdb->get_results( $wpdb->prepare ( "SELECT option_name, option_value FROM $tablename WHERE option_name LIKE 'je_ai_setting_imageid_%%'", $p_tablename), ARRAY_A);
If (count($option_imageid) != NULL)
{
	$i = 0;
	foreach ($option_imageid as $i => $value)
	{
		delete_option( $option_imageid[$i]["option_name"] );
		$i++;
	}
}

//drop all custom je_ai_setting_imageurl_%
$option_imageurl = $wpdb->get_results( $wpdb->prepare ( "SELECT option_name, option_value FROM $tablename WHERE option_name LIKE 'je_ai_setting_imageurl_%%'", $p_tablename), ARRAY_A);
If (count($option_imageurl) != NULL)
{
	$i = 0;
	foreach ($option_imageurl as $i => $value)
	{
		delete_option( $option_imageurl[$i]["option_name"] );
		$i++;
	}
}

//drop a custom db table
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}je_ai" );

?>