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

	$ra_config_list = "('Encrypt Method', 'Encryption algorithm for encrypting data.', 'encrypt_method', 'AES-256-CBC'),
	('Unique Hash Generator', 'Algorithm used to generate a random and almost-unique hash.', 'hash_type', 'sha256');
	;";
