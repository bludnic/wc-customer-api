<?php

class WCC_REST_Shipping {

	// Here initialize our namespace and resource name.
	public function __construct() {
		$this->namespace = '/wcc/v1';
		$this->resource_name = 'shipping';
	}

	// Register our routes.
	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->resource_name . '/methods', array(
			// Here we register the readable endpoint for collections.
			array(
				'methods' => 'POST',
				'callback' => array($this, 'get_shipping_methods'),
				'args' => array(
					'products' => array(
						'required' => true,
						'type' => 'array',
						'description' => 'Products',
					),
					'country' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'Country',
					),
					'state' => array(
						'required' => false,
						'type' => 'string',
						'description' => 'Country',
					),
					'city' => array(
						'required' => false,
						'type' => 'string',
						'description' => 'Country',
					),
					'postcode' => array(
						'required' => false,
						'type' => 'string',
						'description' => 'Country',
					),
				)
			),
		));
		register_rest_route($this->namespace, '/' . $this->resource_name . '/countries', array(
			// Here we register the readable endpoint for collections.
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array($this, 'get_shipping_countries'),
				'args' => array(
				)
			),
		));
	}

	/**
	 * Get shipping methods.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	function get_shipping_methods($request) {
		//https://stackoverflow.com/questions/42763601/how-to-get-a-list-of-shipping-methods-without-a-filledshopping-cart

		global $woocommerce;

		$products = $request['products'];
		$country = $request['country'];
		$state = $request['state'];
		$city = $request['city'];
		$postcode = $request['postcode'];

		// Create new object of Cart and Shipping
		$customer = new WC_Customer();
		$cart = new WC_Cart();
		$shipping = new WC_Shipping();

		// Set customer data
		WC()->customer->set_shipping_location($country, $state, $postcode, $city);

		// Add products to cart

		foreach($products as $product) {
			$cart->add_to_cart($product['id'], $product['quantity']);
		}

		// Get availabale shipping methos for this location
		$cart_packages = $cart->get_shipping_packages();
		$package = $shipping->calculate_shipping_for_package($cart_packages[0]);

		$methods = array();

		if ( empty($package['rates']) ) {
				return new WP_REST_Response(array(), 200);
		}

		foreach($package['rates'] as $key => $rate) {
			$methods[] = array(
				'id' => $rate->id,
				'method_id' => $rate->method_id,
				'label' => $rate->label,
				'cost' => $rate->cost,
				'taxes' => $rate->taxes,
			);
		}

		return new WP_REST_Response($methods, 200);
	}

	/**
	 * Get shipping countries.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	function get_shipping_countries($request) {
		$countries = WC()->countries->get_shipping_countries();
		return new WP_REST_Response($countries, 200);
	}
}