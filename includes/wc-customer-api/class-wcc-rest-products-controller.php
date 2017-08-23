<?php
/**
 * REST API Products controller
 *
 * Handles requests to the /products endpoint.
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
 * REST API Products controller class.
 *
 * @package WCC/API
 * @extends WC_REST_Products_Controller
 */
class WCC_REST_Products_Controller extends WC_REST_Products_Controller {
	
	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wcc/v1';

	/**
	 * Register the routes for products.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'args'                => $this->get_collection_params(),
			),
		));
	}

	/**
	 * Get products.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		add_filter( 'woocommerce_rest_check_permissions', array( $this, 'rest_check_permissions' ), 10, 4 );
		return parent::get_items($request);
	}

	/**
	 * Give quests permission to read products
	 * https://codegists.com/snippet/php/woo_rest_authorisationphp_brianhenryie_php
	 */
	public function rest_check_permissions( $permission, $context, $object_id, $post_type ) {
		if ( $post_type === 'product' && $context === 'read' ) {
			return true;
		}

		return $permission;
	}

}
