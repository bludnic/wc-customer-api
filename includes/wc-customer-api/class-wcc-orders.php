<?php

class WCC_REST_Orders extends WP_REST_Controller {

	// Here initialize our namespace and resource name.
	public function __construct() {
		$this->namespace = '/wcc/v1';
		$this->resource_name = 'orders';
	}

	// Register our routes.
	public function register_routes() {
		register_rest_route($this->namespace, '/' . $this->resource_name, array(
			// Here we register the readable endpoint for collections.
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array($this, 'get_items'),
				'permission_callback' => array($this, 'get_items_permissions_check'),
				'args' => array(
				)
			),
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'create_item'),
				'args' => array(
					'first_name' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'The client\'s first name',
					),
					'last_name' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'The client\'s last name',
					),
					'company' => array(
						'required' => false,
						'type' => 'string',
						'description' => 'Your company name',
					),
					'email' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'Your e-mail address',
					),
					'phone' => array(
						'required' => false,
						'type' => 'string',
						'description' => 'Your phone number',
					),
					'address_1' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'Address 1',
					),
					'address_2' => array(
						'required' => false,
						'type' => 'string',
						'description' => 'Address 1',
					),
					'city' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'City / Town',
					),
					'state' => array(
						'required' => true,
						'type' => 'string',
						'description' => 'State / Country',
					),
					'postcode' => array(
						'required' => true,
						'type' => 'int',
						'description' => 'Postcode / ZIP',
					),
					'country' => array(
						'required' => true,
						'type' => 'int',
						'description' => 'Country code',
					),
					'products' => array(
						'required' => true,
						'type' => 'array',
						'description' => 'Products',
					),
				)
			)
		));
	}

	/**
	 * Check permissions for the orders.
	 *
	 * @param WP_REST_Request $request Current request.
	 */
	public function get_items_permissions_check($request) {
		if ( is_user_logged_in() ) {
			return true;
		}
		return new WP_Error( 'rest_forbidden' , esc_html__( 'You cannot view the orders. Please log in.' ), array( 'status' => $this->authorization_status_code() ) );
	}

	/**
	 * Get customer orders.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items($request) {
		$customer = wp_get_current_user();
		
		// Get all customer orders
		$customer_orders = get_posts( array(
			'numberposts' => -1,
			'meta_key'    => '_customer_user',
			'meta_value'  => get_current_user_id(),
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys( wc_get_order_statuses() ),
			)
		);

		$orders = array();

		foreach($customer_orders as $customer_order) {
			$order = wc_get_order($customer_order->ID);
			$orders[] = $order->get_data();
		}

		return new WP_REST_Response($orders, 200);
	}

	/**
	 * Create new order.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item($request) {
		$address = array(
			'first_name' => $request['first_name'],
			'last_name'  => $request['last_name'],
			'company'    => $request['company'],
			'email'      => $request['email'],
			'phone'      => $request['phone'],
			'address_1'  => $request['address_1'],
			'address_2'  => $request['address_2'],
			'city'       => $request['city'],
			'state'      => $request['state'],
			'postcode'   => $request['postcode'],
			'country'    => $request['country'],
		);

		// Now we create the order
		$order_data = array(
			'status' => 'pending',
			'created_via' => 'App'
		);

		if ( is_user_logged_in() ) {
			$order_data['customer_id'] = get_current_user_id();
		}

		$order = wc_create_order($order_data);

		// Set adress
		$order->set_address( $address, 'billing' );
		$order->set_address( $address, 'shipping' );

		// Set shipping method
		if ($request['shipping_method']) {
			$shipping = new WC_Shipping_Rate();
			$shipping->id = $request['shipping_method']['id'];
			$shipping->label = $request['shipping_method']['label'];
			$shipping->taxes = array(); //not required in your situation
			$shipping->cost = $request['shipping_method']['cost']; //not required in your situation
			$order->add_shipping($shipping);
		}

		// Add products and quantity
		$products = $request['products'];
		foreach($products as $key => $product) {
			try {
				$order->add_product( get_product($product['id']), $product['quantity']);
			} catch(Exception $err) {
				return new WP_Error( 'rest_forbidden' , esc_html__( 'Invalid products IDs' ));
			}
		}
		
		$total = $order->calculate_totals();
		//$order->update_status("pending", '[From app]', TRUE);

		$response = array('status' => 'pending', 'total' => $total, 'id' => $order->id, 'key' => $order->order_key);
		return new WP_REST_Response($response, 200);
	}

	// Sets up the proper HTTP status code for authorization.
	public function authorization_status_code() {

		$status = 401;

		if ( is_user_logged_in() ) {
			$status = 403;
		}

		return $status;
	}
}