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
		$tbl_urlhash = RA_URLHASH_TABLE;
		$tbl_configs = RA_CONFIG_TABLE;
		$tbl_visits = RA_VISITS_TABLE;
		$tbl_coupons = RA_COUPONS_TABLE;
		$tbl_transaction = RA_TRANSACTION;

		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_configs'" ) != $tbl_configs) {
			$sql = "CREATE TABLE `".$tbl_configs."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hash_id` varchar(255) NOT NULL COMMENT 'hash id',  ";
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
				$sql .= " `hash_id` varchar(255) NOT NULL COMMENT 'hash id',  ";
				$sql .= "`revs_type` enum('none','urlhash','coupon') NOT NULL DEFAULT 'none' COMMENT 'Target table', ";	
				$sql .= "`parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Parent id of this revision',  ";
				$sql .= "`child_key` varchar(20) NOT NULL DEFAULT 0 COMMENT 'Column name on the table',  ";
				$sql .= "`child_val` varchar(50) NOT NULL DEFAULT 0 COMMENT 'Value of the row key',  ";
				$sql .= "`created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User id who created this revision',  ";
				$sql .= "`date_created` datetime DEFAULT current_timestamp() COMMENT 'The date this Revision is created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Table creation for urlhash
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_urlhash'" ) != $tbl_urlhash) {
			$sql = "CREATE TABLE `".$tbl_urlhash."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`type` enum('none','registration') NOT NULL DEFAULT 'none' COMMENT 'Type of referral', ";
				$sql .= "`hash` varchar(8) NOT NULL DEFAULT 0 COMMENT 'Hash of this referral',  ";
				$sql .= "`expiry` datetime DEFAULT NULL COMMENT 'Expiration time and date of this referral',  ";
				$sql .= "`created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User id who created this referral',  ";
				$sql .= "`date_created` datetime DEFAULT current_timestamp() COMMENT 'The date this referrral is created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Table creation for visits
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_visits'" ) != $tbl_visits) {
			$sql = "CREATE TABLE `".$tbl_visits."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Id of urlhash',  ";
				$sql .= "`client_ip` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Physical address of user who clicked the urlhash',  ";
				$sql .= "`platform` varchar(30) NOT NULL DEFAULT 0 COMMENT 'OS platform of user device',  ";
				$sql .= "`date_created` datetime DEFAULT current_timestamp() COMMENT 'The date the urlhash is clicked.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Table creation for coupons
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_coupons'" ) != $tbl_coupons) {
			$sql = "CREATE TABLE `".$tbl_coupons."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hash_id` varchar(255) NOT NULL DEFAULT 0 COMMENT 'Hash code of this coupon',  ";
				$sql .= " `expiry` datetime DEFAULT NULL COMMENT 'Expiration time and date of this referral',  ";
				$sql .= " `type` enum('none','free_ship','discount','min_spend','less') NOT NULL DEFAULT 'none' COMMENT 'Type of coupons',  ";
				$sql .= "  `limit` mediumint(9) NOT NULL COMMENT 'Limit of couipons',  ";
				$sql .= " `created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User id who created this referral',  ";
				$sql .= "`date_created` datetime DEFAULT current_timestamp() COMMENT 'The date the urlhash is clicked.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}


		//Table creation for coupons
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_transaction'" ) != $tbl_transaction) {
			$sql = "CREATE TABLE `".$tbl_transaction."` (";
				$sql .= " `ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= " `hash_id` varchar(255) NOT NULL COMMENT 'hash id',  ";
				$sql .= " `coup_id` varchar(255) NOT NULL COMMENT 'Coupon id',  ";
				$sql .= " `order_id` bigint(20) NOT NULL COMMENT 'Order Id',  ";
				$sql .= " `created_by` bigint(20) NOT NULL COMMENT 'The one who create transaction',  ";
				$sql .= " `date_created` datetime DEFAULT current_timestamp() COMMENT 'Date created',  ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}


	} 
	
	add_action( 'activated_plugin', 'ra_dbhook_activate' );