<?php

class WCC_API_Products {

	public function __construct($wc) {
		$this->wc = $wc;
	}

	public function get_products($params = []) {
		$products = $this->wc->get('products', $params);
		print_r($products);
	}

}