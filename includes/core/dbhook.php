<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * @package referall-wp-plugin
     * @version 0.1.0
     * Here is where you add hook to WP to create our custom database if not found.
	*/
	function ra_dbhook_activate() {
	
		global $wpdb;
		$tbl_revisions = RA_REVISIONS_TABLE;
		$tbl_referral = RA_REFERRAL_TABLE;
		$tbl_configs = RA_CONFIG_TABLE;

		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_configs'" ) != $tbl_configs) {
			$sql = "CREATE TABLE `".$tbl_configs."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`title` varchar(255) NOT NULL, ";
				$sql .= "`info` varchar(255) NOT NULL, ";
				$sql .= "`config_key` varchar(50) NOT NULL,";
				$sql .= "`config_val` varchar(50) NOT NULL DEFAULT 0, ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; ";
			$result = $wpdb->get_results($sql);

			//Pass the globally defined constant to a variable
			$conf_list = RA_CONFIG_DATA;
			$conf_fields = RA_CONFIG_FIELD;

			//Dumping data into tables
			$wpdb->query("INSERT INTO `".$tbl_configs."` $conf_fields VALUES $conf_list");
		}

		//Table creation for revisions
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_revisions'" ) != $tbl_revisions) {
			$sql = "CREATE TABLE `".$tbl_revisions."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`revs_type` enum('none','referral') NOT NULL DEFAULT 'none' COMMENT 'Target table', ";	
				$sql .= "`parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Parent id of this revision',  ";
				$sql .= "`child_key` varchar(20) NOT NULL DEFAULT 0 COMMENT 'Column name on the table',  ";
				$sql .= "`child_val` varchar(50) NOT NULL DEFAULT 0 COMMENT 'Value of the row key',  ";
				$sql .= "`created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User id who created this revision',  ";
				$sql .= "`date_created` datetime DEFAULT current_timestamp() COMMENT 'The date this Revision is created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Table creation for referral
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_referral'" ) != $tbl_referral) {
			$sql = "CREATE TABLE `".$tbl_referral."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`revs_type` enum('none','referral') NOT NULL DEFAULT 'none' COMMENT 'Target table', ";	
				$sql .= "`parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Parent id of this revision',  ";
				$sql .= "`code` varchar(16) NOT NULL DEFAULT 0 COMMENT 'Referral unique code on the table',  ";
				$sql .= "`expiry` datetime DEFAULT NULL COMMENT 'Value of the row key',  ";
				$sql .= "`created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User id who created this revision',  ";
				$sql .= "`date_created` datetime DEFAULT current_timestamp() COMMENT 'The date this Revision is created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}


	} 
	
	add_action( 'activated_plugin', 'ra_dbhook_activate' );