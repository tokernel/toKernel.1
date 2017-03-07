<?php
/**
 * Extended shopping cart library
 * This is the example of library extending in toKernel framework.
 * As you can see, the class name defined with "_ext_lib" prefix.
 * Else, the class will be overidden instead of inherit.
 *
 * Parent library located in: /tokernel.framework/lib/shopping_cart.lib.php
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

class shopping_cart_ext_lib extends shopping_cart_lib {

    /**
     * Class constructor
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Return items converted to json
     *
     * @access public
     * @return string
     */
    public function items_get_json() {
        return json_encode($this->items_get());
    }

    /* End class shopping_cart_ext_lib */

}

/* End of file */
?>