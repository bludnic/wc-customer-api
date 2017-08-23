<?php
/**
 *
 * Plugin Name: WooCommerce Customer API
 * Description: Extends the WC REST API for client access using JWT Authentication.
 * Version: 1.0.0
 * Author: bluder
 * Author URI: http://vlas.pro
 *
 * @package WCC
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
};


/**
 * WCC Bootstrap Plugin
 */
class WCC {


	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 *	Init plugin.
	 */
	function init() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/wc-customer-api/class-wcc-rest-customer-controller.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/wc-customer-api/class-wcc-rest-products-controller.php';
		require_once plugin_dir_path( __FILE__ ) . 'includes/wc-customer-api/class-wcc-rest-product-categories-controller.php';
		
		$wcc_customer = new WCC_REST_Customer_Controller();
		$wcc_products = new WCC_REST_Products_Controller();
		$wcc_product_categories = new WCC_REST_Product_Categories_Controller();

		add_action( 'rest_api_init', array($wcc_customer, 'register_routes'));
		add_action( 'rest_api_init', array($wcc_products, 'register_routes'));
		add_action( 'rest_api_init', array($wcc_product_categories, 'register_routes'));
	}

}

$wcc = new WCC();
