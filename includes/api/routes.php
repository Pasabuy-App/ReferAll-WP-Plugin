<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/** 
        * @package referall-wp-plugin
		* @version 0.1.0
		* This is the primary gateway of all the rest api request.
	*/

    //Require the USocketNet class which have the core function of this plguin. 
    
    //Url Hashing
    require plugin_dir_path(__FILE__) . '/v1/urlhash/class-create.php'; 
    require plugin_dir_path(__FILE__) . '/v1/urlhash/class-validate.php'; 
    require plugin_dir_path(__FILE__) . '/v1/urlhash/class-delete.php'; 

    //Visits
    require plugin_dir_path(__FILE__) . '/v1/visits/class-insert.php'; 
  
    //Coupons
    require plugin_dir_path(__FILE__) . '/v1/coupons/class-create.php'; 
    require plugin_dir_path(__FILE__) . '/v1/coupons/class-validate.php'; 
    require plugin_dir_path(__FILE__) . '/v1/coupons/class-delete.php'; 
    require plugin_dir_path(__FILE__) . '/v1/coupons/class-consume.php'; 
    require plugin_dir_path(__FILE__) . '/v1/coupons/class-listing.php'; 
    require plugin_dir_path(__FILE__) . '/v1/coupons/class-update.php'; 


    require plugin_dir_path(__FILE__) . '/v1/class-globals.php'; // globals
	
	// Init check if USocketNet successfully request from wapi.
    function referall_route()
    {
        /*
         * URLHASH RESTAPI
        */
            register_rest_route( 'referall/v1/urlhash', 'create', array(
                'methods' => 'POST',
                'callback' => array('RA_Urlhash_Create','listen'),
            ));

            register_rest_route( 'referall/v1/urlhash', 'validate', array(
                'methods' => 'POST',
                'callback' => array('RA_Validate_Urlhash','listen'),
            ));

            register_rest_route( 'referall/v1/urlhash', 'delete', array(
                'methods' => 'POST',
                'callback' => array('RA_Delete_Urlhash','listen'),
            ));

        /*
         * VISITS RESTAPI
        */
            register_rest_route( 'referall/v1/visits', 'insert', array(
                'methods' => 'POST',
                'callback' => array('RA_Url_Visits','listen'),
            ));
            
        /*
         * COUPONS RESTAPI
        */
            register_rest_route( 'referall/v1/coupons', 'create', array(
                'methods' => 'POST',
                'callback' => array('RA_Coupon_Create','listen'),
            ));

            register_rest_route( 'referall/v1/coupons', 'validate', array(
                'methods' => 'POST',
                'callback' => array('RA_Validate_Coupon','listen'),
            ));

            register_rest_route( 'referall/v1/coupons', 'delete', array(
                'methods' => 'POST',
                'callback' => array('RA_Delete_Coupon','listen'),
            ));

            register_rest_route( 'referall/v1/coupons', 'consume', array(
                'methods' => 'POST',
                'callback' => array('RA_Coupon_Consume','listen'),
            ));

            register_rest_route( 'referall/v1/coupons', 'list', array(
                'methods' => 'POST',
                'callback' => array('RA_Listing_Coupon','listen'),
            ));

            register_rest_route( 'referall/v1/coupons', 'update', array(
                'methods' => 'POST',
                'callback' => array('RA_Update_Coupons','listen'),
            ));


            
    }
    add_action( 'rest_api_init', 'referall_route' );
