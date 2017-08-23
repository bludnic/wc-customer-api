<?php
/**
 * REST API Customer controller
 *
 * Handles requests to the /customer endpoint.
 *
 * @author   bluder
 * @category API
 * @package  WCC/API
 * @since    3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Customer controller class.
 *
 * @package WCC/API
 * @extends WC_REST_Customers_Controller
 */
class WCC_REST_Customer_Controller extends WC_REST_Customers_Controller {
	
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wcc/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
  protected $rest_base = 'customer';

	/**
	 * Register the routes for customer.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(),
			),
		));
	}

	/**
	 * Get a single customer.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$customer_id = get_current_user_id();
		$customer = parent::get_item(array('id' => $customer_id));

		if ( empty( $customer_id ) ) {
			return new WP_Error( 'wcc_rest_not_authorized', __( 'Not authorized', 'woocommerce' ), array( 'status' => 403 ) );
		}
		return $customer;
	}

	/**
	 * Check if user is logged in.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		return is_user_logged_in();
	}

}