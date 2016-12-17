<?php
/**
 * toKernel - Universal PHP Framework.
 * Shopping card library.
 *
 * Each item requires to be an assoc array
 * and have keys with values dispayed bellow.
 *
 * Example:
 * $item = array(
 *      'price' => 2500.5 // float,
 *      'quantity' => 2 // int
 * );
 *
 * Other values of item depends on your needs.
 *
 * This file is part of toKernel.
 *
 * toKernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * toKernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with toKernel. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.0.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.5.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * shopping_cart_lib class library
 *
 * @author David Ayvazyan <tokernel@gmail.com>
 */
class shopping_cart_lib {

	/**
	 * Library object for working with
	 * libraries in this class
	 *
	 * @var object
	 * @access protected
	 */
	protected $lib;

	/**
	 * Session key for cart items
	 *
	 * @var string
	 * @access private
	 */
	private $session_items_key = '_shopping_cart_items_';

	/**
	 * Session key for cart options
	 *
	 * @var string
	 * @access private
	 */
	private $session_key = '_shoping_cart_options_';

	/**
	 * Item id key in array
	 *
	 * @var protected
	 * @access private
	 */
	protected $id_key = '_id';

	/**
	 * The reference of Session library
	 *
	 * @var object
	 * @access private
	 */
	private $s;

	/**
	 * Class constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->lib = lib::instance();

		$this->s = $this->lib->session;
	}

	/**
	 * Set one or more items to cart
	 *
	 * @access public
	 * @param array $data
	 * @return void
	 */
	public function items_set($data) {

		if(is_array($data)) {
			foreach($data as $item) {
				$this->item_set($item);
			}
		} else {
			$this->item_set($item);
		}

	} // End func items_set

	/**
	 * Set one item to cart and return item id.
	 *
	 * @access public
	 * @param array $item
	 * @return string
	 */
	public function item_set($item) {

		$item = $this->checked_item($item);

		$this->s->set($item[$this->id_key], $item, $this->session_items_key);

		return $item[$this->id_key];

	} // End func item_set

	/**
	 * Return all items from card
	 *
	 * @access public
	 * @return array
	 */
	public function items_get() {

		return $this->s->get_section($this->session_items_key);

	} // End func items_get

	/**
	 * Get item total price
	 *
	 * @access public
	 * @param string $id
	 * @return float
	 */
	public function item_get_total_price($id) {

		if(!$this->item_exists($id)) {
			return false;
		}

		$item = $this->item_get($id);

		return $item['quantity'] * $item['price'];

	} // End func item_get_total_price

	/**
	 * Get item quantity
	 *
	 * @access public
	 * @param string $id
	 * @return int
	 */
	public function item_get_quantity($id) {

		if(!$this->item_exists($id)) {
			return false;
		}

		$item = $this->item_get($id);

		return $item['quantity'];

	} // End func item_get_quantity

	/**
	 * Set item quantity
	 *
	 * @access public
	 * @param string $id
	 * @param int $quantity
	 * @return bool
	 */
	public function item_set_quantity($id, $quantity) {

		if(!$this->item_exists($id)) {
			return false;
		}

		if($quantity <= 0) {
			trigger_error('Cart item quantity should be grather than 0.', E_USER_WARNING);
			return false;
		}

		$item = $this->item_get($id);

		$item['quantity'] = $quantity;

		$this->item_update($id, $item);

		return true;

	} // End func item_set_quantity

	/**
	 * Check if item ixists by id
	 *
	 * @access public
	 * @param string $id
	 * @return bool
	 */
	public function item_exists($id) {

		$item = $this->s->get($id, $this->session_items_key);

		if(!is_array($item) or empty($item)) {
			return false;
		}

		return true;

	} // End func item_exists

	/**
	 * Get item by id
	 *
	 * @access public
	 * @param string $id
	 * @return array
	 */
	public function item_get($id) {

		return $this->s->get($id, $this->session_items_key);

	} // End func items_get

	/**
	 * Return the max price of of all items
	 *
	 * @access public
	 * @return float
	 */
	public function max_price() {

		$items = $this->items_get();

		if(empty($items)) {
			return false;
		}

		$max = 0;

		foreach($items as $item) {
			if($item['price'] > $max) {
				$max = $item['price'];
			}
		}

		return $max;

	} // End func max_price

	/**
	 * Update more than one items
	 *
	 * @access public
	 * @param array $items
	 * @return bool
	 */
	public function items_update($items) {

		if(!is_array($items)) {
			return false;
		}

		foreach($items as $item) {

			if(!isset($item[$this->id_key])) {
				trigger_error('Shpooing cart item id "'.$this->id_key.'" not set!', E_USER_WARNING);
			}

			$this->item_update($item[$this->id_key], $item);
		}

		return true;

	} // End func items_update

	/**
	 * Update item by id
	 *
	 * @access public
	 * @param string $id
	 * @param array $data
	 * @return bool
	 */
	public function item_update($id, $data) {

		// Check if item exists in cart
		if(!$this->item_exists($id)) {
			return false;
		}

		if(!isset($data[$this->id_key])) {
			$data[$this->id_key] = $id;
		}

		$this->s->set($id, $data, $this->session_items_key);

		return true;

	} // End func item_update

	/**
	 * Delete more than one items from cart
	 *
	 * @access public
	 * @param array $items
	 * @return bool
	 */
	public function items_delete($items) {

		if(!is_array($items)) {
			return false;
		}

		foreach($items as $item) {

			if(!isset($item[$this->id_key])) {
				trigger_error('Shpooing cart item id "'.$this->id_key.'" not set!', E_USER_WARNING);
			}

			$this->item_delete($item[$this->id_key]);
		}

		return true;

	} // End func items_delete

	/**
	 * Delete item from cart by id
	 *
	 * @access public
	 * @param string $id
	 * @return void
	 */
	public function item_delete($id) {

		$this->s->remove($id, $this->session_items_key);

	} // End func item_delete

	/**
	 * Return all items count excepts quantity in.
	 *
	 * @access public
	 * @return int
	 */
	public function items_count() {

		$section = $this->s->get_section($this->session_items_key);

		if(!is_array($section) or empty($section)) {
			return 0;
		}

		return count($section);

	} // End func items_count

	/**
	 * Return all items count included quantity in.
	 *
	 * @access public
	 * @return int
	 */
	public function items_total_count() {

		$total_count = 0;

		$items = $this->s->get_section($this->session_items_key);

		if(empty($items)) {
			return 0;
		}

		foreach($items as $item) {
			$total_count += $item['quantity'];
		}

		return $total_count;

	} // End func items_total_count

	/**
	 * Return total price of all items
	 *
	 * @access public
	 * @return float
	 */
	public function total_price() {

		$total_price = 0;

		$items = $this->items_get();

		if(empty($items)) {
			return 0;
		}

		foreach($items as $item) {
			$total_price += ($item['price'] * $item['quantity']);
		}

		$total_price = $total_price;

		return $total_price;

	} // End func total_price

	/**
	 * Reset cart
	 *
	 * @access public
	 * @return void
	 */
	public function reset() {

		$this->s->remove_section($this->session_key);
		$this->s->remove_section($this->session_items_key);

	} // End func reset

	/**
	 * Set budget to cart
	 *
	 * @access public
	 * @param float $budget
	 * @return void
	 */
	public function budget_set($budget) {

		$this->s->set('budget', $budget, $this->session_key);

	} // End func budget_set

	/**
	 * Get budget of cart
	 *
	 * @access public
	 * @return float
	 */
	public function budget_get() {

		return $this->s->get('budget', $this->session_key);

	} // End func budget_get

	/**
	 * Get the rest of cart budget
	 *
	 * @access public
	 * @return float
	 */
	public function budget_get_rest() {

		$total_price = $this->total_price();
		$budget = $this->budget_get();

		$rest = $budget - $total_price;

		return $rest;

	} // End func budget_get_rest

	/**
	 * Check the item array keys and set by default.
	 *
	 * @access protected
	 * @param $item
	 * @return array
	 */
	protected function checked_item($item) {

		if(!isset($item[$this->id_key])) {
			$item[$this->id_key] = $this->unique_id();
		}

		if(!isset($item['price'])) {
			$item['price'] = 0;
		}

		if(!isset($item['quantity'])) {
			$item['quantity'] = 1;
		}

		return $item;

	} // End func checked_item

	/**
	 * Create and return unique id for item
	 * Sometihng strnage, right ? ;)
	 *
	 * @access protected
	 * @return string
	 */
	protected function unique_id() {

		return substr(
			md5(
				uniqid(
					time() . mt_rand(
						0, 100
					),
					true
				)
			),
			3,
			8
		);

	} // End func unique_id

} // End of class shopping_cart

// End of file
?>