<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * @package referall-wp-plugin
     * @version 0.1.0
     * Data for DataVice config.
    */

	$ra_config_list = "
	('Referral Expiry', 'Default multiplier to get the date expiry of referral.', 'referral_span', '1', sha2(1, 256))
	;";

	$ra_config_list_revision = "
	('configs', '1', 'referral_span', '3600', '1', sha2(1, 256))";