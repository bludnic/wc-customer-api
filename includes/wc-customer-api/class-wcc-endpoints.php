<?php


require_once WCC_PLUGIN_DIR . 'lib/woocommerce-customer-api/class-wcc-api.php';

require WCC_PLUGIN_DIR . 'lib//woocommerce-api/autoload.php';
use Automattic\WooCommerce\Client;

class WCC_Endpoints extends WP_REST_Controller {

  public $woocommerce;

  function __construct() {
    $this->woocommerce = new Client(
        'http://myaustraliashop.com', 
        'ck_7ed712929aef9f82e0241bfce660dcb4c6a03d34', 
        'cs_417f710f4c712bcb710eaef74cdffbc610e42866',
      array('wp_api' => true,
        'version' => 'wc/v2')
      
    );

    $this->wcc = new WCC_API('http://myaustraliashop.com', 'ck_7ed712929aef9f82e0241bfce660dcb4c6a03d34', 'cs_417f710f4c712bcb710eaef74cdffbc610e42866');
  }

  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {
    $version = '1';
    $namespace = 'wcc/v' . $version;
    $base = 'route';
    register_rest_route( $namespace, '/' . $base, array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_items' ),
        'permission_callback' => array( $this, 'get_items_permissions_check' ),
        'args'            => array(

          ),
        ),
      array(
        'methods'         => WP_REST_Server::CREATABLE,
        'callback'        => array( $this, 'create_item' ),
        'permission_callback' => array( $this, 'create_item_permissions_check' ),
        'args'            => $this->get_endpoint_args_for_item_schema( true ),
        ),
      ) );
    register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', array(
      array(
        'methods'         => WP_REST_Server::READABLE,
        'callback'        => array( $this, 'get_item' ),
        'permission_callback' => array( $this, 'get_item_permissions_check' ),
        'args'            => array(
          'context'          => array(
            'default'      => 'view',
            ),
          ),
        ),
      array(
        'methods'         => WP_REST_Server::EDITABLE,
        'callback'        => array( $this, 'update_item' ),
        'permission_callback' => array( $this, 'update_item_permissions_check' ),
        'args'            => $this->get_endpoint_args_for_item_schema( false ),
        ),
      array(
        'methods'  => WP_REST_Server::DELETABLE,
        'callback' => array( $this, 'delete_item' ),
        'permission_callback' => array( $this, 'delete_item_permissions_check' ),
        'args'     => array(
          'force'    => array(
            'default'      => false,
            ),
          ),
        ),
      ) );
    register_rest_route( $namespace, '/' . $base . '/schema', array(
      'methods'         => WP_REST_Server::READABLE,
      'callback'        => array( $this, 'get_public_item_schema' ),
      ) );


    // register_rest_route( $namespace, '/products', array(
    //   'methods'         => WP_REST_Server::READABLE,
    //   'callback'        => array( $this, 'get_products' )
    // ));

    // register_rest_route( $namespace, '/products/categories', array(
    //   'methods'         => WP_REST_Server::READABLE,
    //   'callback'        => array( $this, 'get_categories' )
    // ));

    register_rest_route( $namespace, '/profile', array(
      'methods'         => WP_REST_Server::READABLE,
      'callback'        => array( $this, 'profile' )
    ));

    register_rest_route( $namespace, 'register', array(
      'methods'         => WP_REST_Server::CREATABLE,
      'callback'        => array( $this, 'register' )
    ));

    register_rest_route( $namespace, '/payment_gateways', array(
      'methods'         => WP_REST_Server::READABLE,
      'callback'        => array( $this, 'payment_gateways' )
    ));    

    // register_rest_route( $namespace, '/orders', array(
    //   array(
    //     'methods'         => WP_REST_Server::READABLE,
    //     'callback'        => array( $this, 'get_orders' )
    //   ),
    //   array(
    //     'methods'         => WP_REST_Server::CREATABLE,
    //     'callback'        => array( $this, 'create_order'),
    //     'args'            => array('first_name' => array(
    //                            'required' => true,
    //                            'type' => 'string',
    //                            'description' => 'The client\'s first name'
    //                          )
    //     )
    //   ),
    // ));

    // register_rest_route( $namespace, '/orders' . '/(?P<id>[\d]+)', array(
    //   array(
    //     'methods'         => WP_REST_Server::READABLE,
    //     'callback'        => array( $this, 'get_order' ),
    //     ),
    //   array(
    //     'methods'         => WP_REST_Server::EDITABLE,
    //     'callback'        => array( $this, 'update_item' ),
    //     'permission_callback' => array( $this, 'update_item_permissions_check' ),
    //     'args'            => $this->get_endpoint_args_for_item_schema( false ),
    //     ),
    //   array(
    //     'methods'  => WP_REST_Server::DELETABLE,
    //     'callback' => array( $this, 'delete_item' ),
    //     'permission_callback' => array( $this, 'delete_item_permissions_check' ),
    //     'args'     => array(
    //       'force'    => array(
    //         'default'      => false,
    //         ),
    //       ),
    //     ),
    // ));
  }
  
  /**
   * Get products
   */
  public function get_products($request) {
    $params = $request->get_params();

    try {
      $wproducts = $this->woocommerce->get('products', $params);
      $products = array();
      foreach ($wproducts as $key => $product) {
        $products[] = array(
          'id' => $product['id'],
          'name' => $product['name'],
          'description' => $product['description'],
          'price' => $product['price'],
          'sale_price' => $product['sale_price'],
          'image' => $product['images'][0]['src']
        );
      }
      return new WP_REST_Response($products, 200);
    } catch (Exception $err) {
        return new WP_Error( 'code', 'Invalid params' );
    }

  }
  
  /**
   * Get categories
  */
  public function get_categories($request) {
    $params = $request->get_params();

    try {
        $wcategories = $this->woocommerce->get('products/categories', $params);
        return new WP_REST_Response($wcategories, 200);
    } catch(Exception $err) {
        return new WP_Error( 'code', 'Invalid params' );
    }
  }

  
  /**
   * User profile
  */
  public function profile($request) {
    $params = $request->get_params();

    $current_user = wp_get_current_user();

    if ( 0 == $current_user->ID ) {
        return new WP_Error( 'code', __( 'message', 'text-domain' ) );
    } else {
        return new WP_REST_Response($current_user, 200);
    }

  }

  /**
   * Register user
  */
  public function register($request) {
    $params = $request->get_params();

    if ( ($params['username']) && ($params['email']) && ($params['password']) ) {
      $user_id = username_exists( $params['username'] );
      if ( !$user_id and email_exists($user_email) == false ) {
        $user_id = wp_create_user( $params['username'], $params['password'], $params['email'] );
        return new WP_REST_Response(array('user_id' => $user_id), 200);
      } else {
        return new WP_Error( 'code', 'User already exists. Password inherited.');
      }
    } else {
      return new WP_Error( 'code', 'Incorrect data');
    }

  }


  /**
   *  List of Payment_gateways
  */

  public function payment_gateways($request) {
    $params = $request->get_params();

    $params['enabled'] = true;

    $payment_gateways = $this->woocommerce->get('payment_gateways', $params);
    return new WP_REST_Response($payment_gateways, 200);
  }

  /**
   * User order
   */
  public function get_orders($request) {
    //$params = $request->get_params();

    $current_user = wp_get_current_user();
    if ( 0 == $current_user->ID ) {
        return new WP_Error( 'code', 'Not authorized' );
    }

    $args = array(
        'customer' => $current_user->ID
    );

    $orders = $this->woocommerce->get('orders', $args);
    return new WP_REST_Response($orders, 200);
  }

  /**
   * Get single order
  */
  public function get_order($request) {
    $params = $request->get_params();
    try {
        $order = $this->woocommerce->get('orders/' . $params['id']);
        return new WP_REST_Response($order, 200);
    } catch(Exception $err) {
        return new WP_Error( 'code', 'Invalid ID' );
    }
  }

  /**
   * Create order
  */
  public function create_order($request) {
    $params = $request->get_params();

    $current_user = wp_get_current_user();



    if ( 0 == $current_user->ID ) {
        return new WP_Error( 'code', "Not logged" );
    } else {
        global $woocommerce;

        $address = array(
            'first_name' => '111Joe',
            'last_name'  => 'Conlin',
            'company'    => 'Speed Society',
            'email'      => 'joe@testing.com',
            'phone'      => '760-555-1212',
            'address_1'  => '123 Main st.',
            'address_2'  => '104',
            'city'       => 'San Diego',
            'state'      => 'Ca',
            'postcode'   => '92121',
            'country'    => 'US'
        );

        // Now we create the order
        $order = wc_create_order();

        // The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
        $order->add_product( get_product('22'), 2); // This is an existing SIMPLE product
        $order->add_product( get_product('54'), 3); // This is an existing SIMPLE product
        $order->set_address( $address, 'billing' );
        $order->set_address( $address, 'shipping' );
        //
        $order->calculate_totals();
        $order->update_status("completed", '[From app]', TRUE);
        return new WP_REST_Response($order, 200);
    }

  }
  


  /**
   * Get a collection of items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items( $request ) {
    $items = array(); //do a query, call another class, etc
    $data = array();
    foreach( $items as $item ) {
      $itemdata = $this->prepare_item_for_response( $item, $request );
      $data[] = $this->prepare_response_for_collection( $itemdata );
    }

    return new WP_REST_Response( $data, 200 );
  }

  /**
   * Get one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item( $request ) {
    //get parameters from request
   $params = $request->get_params();
    $item = array();//do a query, call another class, etc
    $data = $this->prepare_item_for_response( $item, $request );

    //return a response or error based on some conditional
    if ( 1 == 1 ) {
      return new WP_REST_Response( $data, 200 );
    }else{
      return new WP_Error( 'code', __( 'message', 'text-domain' ) );
    }
  }

  /**
   * Create one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function create_item( $request ) {

    $item = $this->prepare_item_for_database( $request );

    if ( function_exists( 'slug_some_function_to_create_item')  ) {
      $data = slug_some_function_to_create_item( $item );
      if ( is_array( $data ) ) {
        return new WP_REST_Response( $data, 200 );
      }
    }

    return new WP_Error( 'cant-create', __( 'message', 'text-domain'), array( 'status' => 500 ) );
  }

  /**
   * Update one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function update_item( $request ) {
    $item = $this->prepare_item_for_database( $request );

    if ( function_exists( 'slug_some_function_to_update_item')  ) {
      $data = slug_some_function_to_update_item( $item );
      if ( is_array( $data ) ) {
        return new WP_REST_Response( $data, 200 );
      }
    }

    return new WP_Error( 'cant-update', __( 'message', 'text-domain'), array( 'status' => 500 ) );

  }

  /**
   * Delete one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function delete_item( $request ) {
    $item = $this->prepare_item_for_database( $request );

    if ( function_exists( 'slug_some_function_to_delete_item')  ) {
      $deleted = slug_some_function_to_delete_item( $item );
      if (  $deleted  ) {
        return new WP_REST_Response( true, 200 );
      }
    }

    return new WP_Error( 'cant-delete', __( 'message', 'text-domain'), array( 'status' => 500 ) );
  }

  /**
   * Check if a given request has access to get items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check( $request ) {
    //return true; <--use to make readable by all
   return current_user_can( 'edit_something' );
 }
 
  /**
   * Check if a given request has access to get a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_item_permissions_check( $request ) {
    return $this->get_items_permissions_check( $request );
  }

  /**
   * Check if a given request has access to create items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function create_item_permissions_check( $request ) {
    return current_user_can( 'edit_something' );
  }

  /**
   * Check if a given request has access to update a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function update_item_permissions_check( $request ) {
    return $this->create_item_permissions_check( $request );
  }

  /**
   * Check if a given request has access to delete a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function delete_item_permissions_check( $request ) {
    return $this->create_item_permissions_check( $request );
  }

  /**
   * Prepare the item for create or update operation
   *
   * @param WP_REST_Request $request Request object
   * @return WP_Error|object $prepared_item
   */
  protected function prepare_item_for_database( $request ) {
    return array();
  }

  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_item_for_response( $item, $request ) {
    return array();
  }

  /**
   * Get the query params for collections
   *
   * @return array
   */
  public function get_collection_params() {
    return array(
      'page'     => array(
        'description'        => 'Current page of the collection.',
        'type'               => 'integer',
        'default'            => 1,
        'sanitize_callback'  => 'absint',
        ),
      'per_page' => array(
        'description'        => 'Maximum number of items to be returned in result set.',
        'type'               => 'integer',
        'default'            => 10,
        'sanitize_callback'  => 'absint',
        ),
      'search'   => array(
        'description'        => 'Limit results to those matching a string.',
        'type'               => 'string',
        'sanitize_callback'  => 'sanitize_text_field',
        ),
      );
  }
}