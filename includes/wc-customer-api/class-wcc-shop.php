<?php

class WCC_REST_Shop extends WP_REST_Controller {

	// Here initialize our namespace and resource name.
	public function __construct() {
		$this->namespace = '/wcc/v1';
		$this->resource_name = 'shop';
	}

	// Register our routes.
	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->resource_name, array(
			// Here we register the readable endpoint for collections.
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array($this, 'get_shop_data'),
				'args' => array(
				)
			),
		));
	}


	/**
	 * Get shop data
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_shop_data($request) {
		$data = array(
			'currency' => get_woocommerce_currency()
		);

		return new WP_REST_Response($data, 200);
	}

}