<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * @package referall-wp-plugin
     * @version 0.1.0
     * Config related class and function
    */

    class RA_Library_Config {

        public static function ra_get_config($key, $default){

            global $wpdb;
            $tbl_config = RA_CONFIG_TABLE;
            $tbl_revision = RA_REVISIONS_TABLE;

            $result = $wpdb->get_row("SELECT child_val FROM {$tbl_config} INNER JOIN {$tbl_revision} rev ON rev.ID = dv_configs.config_val  WHERE config_key = '$key' AND revs_type = 'configs' AND child_key = '$key'
            ");

            if (!$result) {
                return $default;
            } else {
                return $result->child_val;
            }
        }

        public static function ra_set_config($title, $info, $key, $value){

            global $wpdb;
            $rev_table = RA_REVISIONS_TABLE;
            $rev_fields = RA_REVISIONS_FIELDS;
            $tbl_config = RA_CONFIG_TABLE;

            $date = date("Y-m-d h:i:s");

            $result_config_val = $wpdb->query("INSERT INTO {$rev_table} ($rev_fields,  `parent_id`) VALUES ( 'configs', '$key', '$value', '1', '$date', '0' )");
            $result_config_val_id = $wpdb->insert_id;

            $result_config = $wpdb->query("INSERT INTO {$tbl_config} (`title`, `info`, `config_key`, `config_val`) VALUES ('$title', '$info', '$key', '$result_config_val_id');");
            $result_config_id = $wpdb->insert_id;

            $result_config_val_update = $wpdb->query("UPDATE {$rev_table} SET `parent_id` = '$result_config_id' WHERE ID = $result_config_val_id  ");

            if (!$result_config_val_id || !$result_config || !$result_config_val_update) {
                return false;
            } else {
                return true;
            }
        }

        public static function ra_update_config($key, $value){

            global $wpdb;
            $tbl_config = RA_CONFIG_TABLE;

            $result = $wpdb->query("UPDATE {$tbl_config} SET `config_key`='$key', `config_val`='$value';");

            if (!$result) {
                return false;
            } else {
                return true;
            }
        }

    }
