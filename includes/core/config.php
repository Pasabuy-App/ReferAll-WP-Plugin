<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * @package referall-wp-plugin
     * @version 0.1.0
     * This is where you provide all the constant config.
	*/

	//Defining Global Variables
	define('RA_PREFIX', 'ra_');

	//Configs CONSTANT
	define('RA_CONFIG_TABLE', RA_PREFIX.'configs');
	define("RA_CONFIG_DATA", $ra_config_list);
	define("RA_CONFIG_FIELD", "(title, info, config_key, config_val)");

	//Revisions CONSTANT
	define('RA_REVISIONS_TABLE', RA_PREFIX.'revisions');
	define("RA_REVISIONS_FIELDS", "(revs_type, parent_id, child_key, child_val, created_by)");

	//Referral CONSTANT
	define('RA_URLHASH_TABLE', RA_PREFIX.'urlhash');
	define("RA_URLHASH_FIELDS", "(type, hash, expiry, created_by)");

	//Visits CONSTANT
	define('RA_VISITS_TABLE', RA_PREFIX.'visits');
	define("RA_VISITS_FIELDS", "(parent_id, client_ip, platform)");

	//Coupons CONSTANT
	define('RA_COUPONS_TABLE', RA_PREFIX.'coupons');
	define("RA_COUPONS_FIELDS", "(hash_id, expiry, type, limit, created_by)");

	define('RA_TRANSACTION', RA_PREFIX.'coupons');
	define('RA_TRANSACTION_FILEDS', '( hash_id, coup_id, order_id, created_by, date_created )');


