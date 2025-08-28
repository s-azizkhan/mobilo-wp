<?php

namespace Mobilo\WpTheme\Interfaces;

use WC_Product;

interface CartItemInterface {
	public function get_thumbnail(): string;
	public function get_quantity(): float;
	public function get_title(): string;
	public function get_url(): string;
	public function get_subtotal(): float;
	public function get_subtotal_html(): string;
	public function get_item_key(): string;
	public function get_raw_item();
	public function get_product(): WC_Product;
	public function get_data(): array;
	public function get_data_v2(): array;
	public function get_formatted_data(): string;
}
