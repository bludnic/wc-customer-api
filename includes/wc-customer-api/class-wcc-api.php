<?php

require WCC_PLUGIN_DIR . 'lib//woocommerce-api/autoload.php';
use Automattic\WooCommerce\Client;

require WCC_PLUGIN_DIR . 'lib/woocommerce-customer-api/class-wcc-api-products.php';

class WCC_API {
  
  public $wc;
  public $products;

	public function __construct($store_url, $key, $secret) {
		$this->wc = new Client(
			$store_url, $key, $secret,
			[
			'wp_api' => true,
			'version' => 'wc/v1',
			]
		);

		$this->products = new WCC_API_Products($this->wc);
	}


}