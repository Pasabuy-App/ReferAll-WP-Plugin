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

	//Referral CONSTANT
	define('RA_REFERRAL_TABLE', RA_PREFIX.'referral');

