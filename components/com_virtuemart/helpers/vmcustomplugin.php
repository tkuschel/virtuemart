<?php

/**
 * Abstract class for shipper plugins
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Oscar van Eijk
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: vmshipperplugin.php 4007 2011-08-31 07:31:35Z alatak $
 */
// Load the helper functions that are needed by all plugins
if (!class_exists('ShopFunctions'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'shopfunctions.php');
if (!class_exists('DbScheme'))
    require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'dbscheme.php');


// Get the plugin library
jimport('joomla.plugin.plugin');

/**
 * Abstract class for shipper plugins.
 * This class provides some standard and abstract methods that can or must be reimplemented.
 *
 * @tutorial All methods are documented, but to make life easier, here's a short overview
 * how the methods can be used in the process order.
 * 	* _createTable() is called by the constructor. Use this method to create or alter the database table.
 * 	* When a shopper selects a shipper, plgOnSelectShipper() is fired. It displays the shipper and can be used
 * 	for collecting extra - shipper specific - info.
 * 	* After selecting, plgVmShipperSelected() can be used to store extra shipper info in the cart. The selected shipper
 * 	ID will be stored in the cart by the checkout process before this method is fired.
 * 	* plgOnConfirmShipper() is fired when the order is confirmed and stored to the database. It is called
 * 	before the rest of the order or stored, when reimplemented, it *must* include a call to parent::plgOnConfirmShipper()
 * 	(or execute the same steps to put all data in the cart)
 *
 * When a stored order is displayed in the backend, the following events are used:
 * 	* plgVmOnShowOrderShipperBE() displays specific data about (a) shipment(s) (NOTE: this plugin is
 * 	OUTSIDE any form!)
 * 	* plgVmOnShowOrderLineShipperBE() can be used to show information about a single orderline, e.g.
 * 	display a package code at line level when more packages are shipped.
 * 	* plgVmOnEditOrderLineShipperBE() can be used add a package code for an order line when more
 * 	packages are shipped.
 * 	* plgVmOnUpdateOrderShipperBE is fired inside a form. It can be used to add shipper data, like package code.
 * 	* plgVmOnSaveOrderShipperBE() is fired from the backend after the order has been saved. If one of the
 * 	show methods above have to option to add or edit info, this method must be used to save the data.
 * 	* plgVmOnUpdateOrderLine() is fired from the backend after an order line has been saved. This method
 * 	must be reimplemented if plgVmOnEditOrderLineShipperBE() is used.
 *
 * The frontend 1 show method:
 * 	* plgVmOnShowOrderShipperFE() collects and displays specific data about (a) shipment(s)
 *
 * @package	VirtueMart
 * @subpackage Plugins
 * @author Oscar van Eijk
 */
abstract class vmCustomPlugin extends JPlugin {

    //private $_virtuemart_shippermethod_id = 0;
    /**
     * @var string Identification of the shipper. This var must be overwritten by all plugins,
     * by adding this code to the constructor:
     * $this->_selement = basename(__FILE, '.php');
     */
    protected $_celement = '';
    protected $_tablename = '';
    /**
     * @var array List with all carriers the have been implemented with the plugin in the format
     * id => name
     */
    protected $customs;

    /**
     * Constructor
     *
     * @param object $subject The object to observe
     * @param array  $config  An array that holds the plugin configuration
     * @since 1.5
     */
    function __construct(& $subject, $config) {
        parent::__construct($subject, $config);
        $lang = JFactory::getLanguage();
        $filename = 'plg_vmcustom_' . $this->_celement;
        $lang->load($filename, JPATH_ADMINISTRATOR);
        $this->carrier = array();
        if (!class_exists('JParameter'))
            require(JPATH_VM_LIBRARIES . DS . 'joomla' . DS . 'html' . DS . 'parameter.php' );
    }

    /**
     * This functions gets the used and configured payment method
     * pelement of this class determines the used jplugin.
     * The right payment method is determined by the vendor and the jplugin id.
     *
     * This function sets the used payment plugin as variable of this class
     * @author Max Milbers
     *
     */
    protected function getVmCustomParams($vendorId=0, $shipper_id=0) {

        if (!$vendorId)
            $vendorId = 1;
        $db = JFactory::getDBO();

        $q = 'SELECT   `custom_params` FROM #__virtuemart_custom_plg WHERE `virtuemart_shippingcarrier_id` = "' . $shipper_id . '" AND `virtuemart_vendor_id` = "' . $vendorId . '" AND `published`="1" ';
        $db->setQuery($q);
        return $db->loadResult();
    }
	/**
	 * display the plugin param for product
	 */
	abstract function plgVmOnProductEdit($value,$row, $product_id);

	/**
	 * save the plugin param on product save
	 */
	public function plgVmOnProducSave(&$value, $product_id){
			
	}
 
	/**
	 * display the plugin on product FE
	 */
		public function plgVmOnDisplayFE(&$value, $product_id){
			$value = $value.'2';
	}
	
	/**
	 * *** Can only set in table at order then put it in session ***
	 * *** Have to add it in Virtuemart cart ? ***
	 * * @author Patrick Kohl
	 */
	function plgVmOnAddProductInCartFE($product, $customPlg , $virtuemart_product_id) {
		
		if (!empty($textInputs)) {
        $session = JFactory::getSession();
		$sessionCustom = $session->get('vmcustom', 0, 'vm');
			if (!empty($sessionCustom)) {
				foreach ($textInputs as $textInput) $sessionCustom[$this->_celement][] = $textInput ;
			}
		$session->set('vmcustom', serialize($sessionCustom),'vm');
		}
    }

	/**
	 * save the product in cart
	 * Have to save product plugin param value from session  to custom table
	 */
		public function plgVmOnOrder(&$value, $product_id){
			$value = $value.'2';
	}

	/**
	 * display values for Vendor
	 * 
	 */
		public function plgVmOnOrderDisplayVendor(&$value, $product_id){
			$value = $value.'2';
	}

	/**
	 * display values for shopper
	 * 
	 */
		public function plgVmOnOrderDisplayShopper(&$value, $product_id){
			$value = $value.'2';
	}

/***************OLD CODE !!!*****************/
    /**
     * Get the total weight for the order, based on which the proper shipping rate
     * can be selected.
     * @param object $_cart Cart object
     * @return float Total weight for the order
     * @author Oscar van Eijk
     */
    protected function getOrderWeight(VirtueMartCart $cart, $to_weight_unit) {
        $weight = 0;
        foreach ($cart->products as $prod) {
            $weight += ( ShopFunctions::convertWeigthUnit($prod->product_weight, $prod->product_weight_unit, $to_weight_unit) * $prod->quantity);
        }
        return $weight;
    }

    /**
     * Fill the array with all carriers found with this plugin for the current vendor
     * @return True when carrier(s) was (were) found for this vendor, false otherwise
     * @author Oscar van Eijk
     */
    protected function getCustoms($_vendorId) {
        $db = JFactory::getDBO();
        if (VmConfig::isJ15()) {
            $q = 'SELECT v.*  '
                    . 'FROM   #__virtuemart_custom_plg AS v '
                    . ',      #__plugins             j '
                    . 'WHERE j.`element` = "' . $this->_selement . '" '
                    . 'AND   v.`custom_jplugin_id` = j.`id` '
                    . 'AND   v.`published` = "1" '
                    . 'AND  (v.`virtuemart_vendor_id` = "' . $_vendorId . '" '
                    . ' OR   v.`virtuemart_vendor_id` = "0") '
            ;
        } else {
            $q = 'SELECT v.* '
                    . 'FROM   #__virtuemart_custom_plg AS v '
                    . ',      #__extensions    AS      j '
                    . 'WHERE j.`folder` = "vmshipper" '
                    . 'AND j.`element` = "' . $this->_selement . '" '
                    . 'AND   v.`published` = "1" '
                    . 'AND   v.`custom_jplugin_id` = j.`extension_id` '
                    . 'AND  (v.`virtuemart_vendor_id` = "' . $_vendorId . '" '
                    . ' OR   v.`virtuemart_vendor_id` = "0") '
            ;
        }


        $db->setQuery($q);
        if (!$results = $db->loadObjectList()) {
//			$app = JFactory::getApplication();
//			$app->enqueueMessage(JText::_('COM_VIRTUEMART_CART_NO_CARRIER'));
            return false;
        }
        $this->shippers = $results;
        return true;
    }

    /**
     * Check if this shipper has carriers for the current vendor.
     * @author Oscar van Eijk
     * @param integer $_vendorId The vendor ID taken from the cart.
     * @return True when a shipper_id was found for this vendor, false otherwise
     */
    protected function validateVendor($_vendorId) {

        if (!$_vendorId) {
            $_vendorId = 1;
        }

        $_db = JFactory::getDBO();

        if (VmConfig::isJ15()) {
            $_q = 'SELECT 1 '
                    . 'FROM   #__virtuemart_custom_plg v '
                    . ',      #__plugins             j '
                    . 'WHERE j.`element` = "' . $this->_selement . '" '
                    . 'AND   v.`custom_jplugin_id` = j.`id` '
                    . 'AND   v.`virtuemart_vendor_id` = "' . $_vendorId . '" '
                    . 'AND   v.`published` = 1 '
            ;
        } else {
            $_q = 'SELECT 1 '
                    . 'FROM   #__virtuemart_custom_plg AS v '
                    . ',      #__extensions   AS     j '
                    . 'WHERE j.`folder` = "vmshipper" '
                    . 'AND j.`element` = "' . $this->_selement . '" '
                    . 'AND   v.`custom_jplugin_id` = j.`extension_id` '
                    . 'AND   v.`virtuemart_vendor_id` = "' . $_vendorId . '" '
                    . 'AND   v.`published` = 1 '
            ;
        }




        $_db->setQuery($_q);
        $_r = $_db->loadAssoc();

        if ($_r) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Method to create te plugin specific table; must be reimplemented.
     * @example
     * 	$_scheme = DbScheme::get_instance();
     * 	$_scheme->create_scheme('#__vm_order_shipper_'.$this->_selement);
     * 	$_schemeCols = array(
     * 		 'id' => array (
     * 				 'type' => 'int'
     * 				,'length' => 11
     * 				,'auto_inc' => true
     * 				,'null' => false
     * 		)
     * 		,'virtuemart_order_id' => array (
     * 				 'type' => 'int'
     * 				,'length' => 11
     * 				,'null' => false
     * 		)
     * 		,'shipper_id' => array (
     * 				 'type' => 'text'
     * 				,'null' => false
     * 		)
     * 	);
     * 	$_schemeIdx = array(
     * 		 'idx_order_s' => array(
     * 				 'columns' => array ('virtuemart_order_id')
     * 				,'primary' => false
     * 				,'unique' => false
     * 				,'type' => null
     * 		)
     * 	);
     * 	$_scheme->define_scheme($_schemeCols);
     * 	$_scheme->define_index($_schemeIdx);
     * 	if (!$_scheme->scheme()) {
     * 		JError::raiseWarning(500, $_scheme->get_db_error());
     * 	}
     * 	$_scheme->reset();
     * @author Oscar van Eijk
     */
    abstract protected function _createTable();

    /**
     * This event is fired during the checkout process. It allows the shopper to select
     * one of the available shippers.
     * It should display a radio button (name: shipper_id) to select the shipper. In the description,
     * the shipping cost can also be displayed, based on the total order weight and the shipto
     * country (this wil be calculated again during order confirmation)
     *
     * @param object $_cart the cart object
     * @param integer $_selected ID of the shipper currently selected
     * @return HTML code to display the form
     * @author Oscar van Eijk
     */
    public function plgVmOnSelectShipper($cart, $_selectedShipper = 0) {

        if ($this->getShippers($cart->vendorId) === false) {
            if (empty($this->_name)) {
                $app = JFactory::getApplication();
                $app->enqueueMessage(JText::_('COM_VIRTUEMART_CART_NO_CARRIER'));
                return;
            } else {
                //return JText::sprintf('COM_VIRTUEMART_SHIPPER_NOT_VALID_FOR_THIS_VENDOR', $this->_name , $_cart->vendorId );
                return;
            }
        }
    }

    /**
     * This event is fired after the shipping method has been selected. It can be used to store
     * additional shipper info in the cart.
     *
     * @param object $_cart Cart object
     * @param integer $_selected ID of the shipper selected
     * @return boolean True on succes, false on failures, null when this plugin was not selected.
     * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
     * @author Oscar van Eijk
     */
    public function plgVmOnShipperSelected($cart, $_selectedShipper = 0) {

        if (!$this->selectedThisShipper($this->_selement, $_selectedShipper)) {
            return null; // Another shipper was selected, do nothing
        }
        // should return $shipping rates for this
        $cart->setShippingRate($this->selectShippingRate($cart));
        return true;
    }

    /**
     * This event is fired after the payment has been processed; it selects the actual shipping rate
     * based on the shipto (country, zip) and/or order weight, and optionally writes extra info
     * to the database (in which case this method must be reimplemented).
     * Reimplementation is not required, but when done, the following check MUST be made:
     * 	if (!$this->selectedThisShipper($this->_selement, $_cart->shipper_id)) {
     * 		return null;
     * 	}
     *
     * Returing parent::plgVmOnCheckoutCheckShipperData($_cart) is valid but will produce extra overhead!
     *
     * @param object $_cart Cart object
     * @return integer The shipping rate ID
     * @author Oscar van Eijk
     */
    public function plgVmOnCheckoutCheckShipperData(VirtueMartCart $cart) {
        return $this->selectShippingRate($cart);
    }

    /**
     * This method is fired when showing the order details in the backend.
     * It displays the shipper-specific data.
     * NOTE, this plugin should NOT be used to display form fields, since it's called outside
     * a form! Use plgVmOnUpdateOrderBE() instead!
     *
     * @param integer $_orderId The order ID
     * @param integer $_vendorId Vendor ID
     * @param object $_shipInfo Object with the properties 'carrier' and 'name'
     * @return mixed Null for shippers that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk
     */
    public function plgVmOnShowOrderShipperBE($_orderId, $_vendorId, $_shipInfo) {
        if (!($this->selectedThisShipper($this->_selement, $this->getShipperIDForOrder($_orderId)))) {
            return null;
        }
        /*
          if (!class_exists('CurrencyDisplay')

          )require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
          $_currency = CurrencyDisplay::getInstance();  //Todo, set currency of shopper or user?
          //		$_currency = VirtueMartModelVendor::getCurrencyDisplay($_vendorId);
          $_html = '<table class="admintable">' . "\n"
          . '	<thead>' . "\n"
          . '		<tr>' . "\n"
          . '			<td class="key" style="text-align: center;" colspan="2">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_LBL') . '</td>' . "\n"
          . '		</tr>' . "\n"
          . '	</thead>' . "\n"
          . '	<tr>' . "\n"
          . '		<td class="key">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_CARRIER_LBL') . ': </td>' . "\n"
          . '		<td align="left">' . $_shipInfo->carrier . '</td>' . "\n"
          . '	</tr>' . "\n"
          . '	<tr>' . "\n"
          . '		<td class="key">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_MODE_LBL') . ': </td>' . "\n"
          . '		<td>' . $_shipInfo->name . '</td>' . "\n"
          . '	</tr>' . "\n"
          . '	<tr>' . "\n"
          . '		<td class="key">' . JText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING_PRICE_LBL') . ': </td>' . "\n"
          . '		<td align="left">' . $_currency->priceDisplay($this->getShippingRate($this->getShippingRateIDForOrder($_orderId))) . '</td>' . "\n"
          . '	</tr>' . "\n"
          . '</table>' . "\n"
          ;
         *
         *
         */
        return $_html;
    }

    /**
     * This method is fired when editing the order line details in the backend.
     * It can be used to add line specific package codes
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for shippers that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk
     */
    public function plgVmOnEditOrderLineShipperBE($_orderId, $_lineId) {
        return null;
    }

    /**
     * Save updated order data to the shipper specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this shipper is not actived.
     * @author Oscar van Eijk
     */
    public function plgVmOnUpdateOrderShipper($_formData) {
        return null;
    }

    /**
     * Save updated orderline data to the shipper specific table
     *
     * @param array $_formData Form data
     * @return mixed, True on success, false on failures (the rest of the save-process will be
     * skipped!), or null when this shipper is not actived.
     * @author Oscar van Eijk
     */
    public function plgVmOnUpdateOrderLineShipper($_formData) {
        return null;
    }

    /**
     * This method is fired when showing the order details in the frontend.
     * It displays the shipper-specific data.
     *
     * @param integer $_orderId The order ID
     * @return mixed Null for shippers that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk
     */
    public function plgVmOnShowOrderShipperFE($_orderId) {
        /*
          if (!($this->selectedThisShipper($this->_selement, $this->getShipperIDForOrder($_orderId)))) {
          return null;
          }
         */
    }

    /**
     * This method is fired when showing the order details in the frontend, for every orderline.
     * It can be used to display line specific package codes, e.g. with a link to external tracking and
     * tracing systems
     *
     * @param integer $_orderId The order ID
     * @param integer $_lineId
     * @return mixed Null for shippers that aren't active, text (HTML) otherwise
     * @author Oscar van Eijk
     */
    public function plgVmOnShowOrderLineShipperFE($_orderId, $_lineId) {
        return null;
    }

    /**
     * Get the shipping rate ID for a given order number
     * @access protected
     * @author Oscar van Eijk
     * @param int $_id The order ID
     * @return int The shipping rate ID, or -1 when not found
     */
    protected function getShippingRateIDForOrder($_id) {
        $_db = JFactory::getDBO();
        $_q = 'SELECT `ship_method_id` '
                . 'FROM #__virtuemart_orders '
                . "WHERE virtuemart_order_id = $_id";
        $_db->setQuery($_q);
        if (!($_r = $_db->loadAssoc())) {
            return -1;
        }
        return $_r['ship_method_id'];
    }

    /**
     * Check the order total to see if this order is valid for free shipping.
     * @access protected
     * @final
     * @return boolean; true when shipping is free
     * @author Oscar van Eijk
     */
    final protected function freeShipping() {
        if (!class_exists('VirtueMartCart'))
            require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
        $_cart = VirtueMartCart::getCart();
        if (!class_exists('VirtueMartModelVendor'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
        $_vendor = new VirtueMartModelVendor();
        $_vendor->setId($_cart->vendorId);
        $_store = $_vendor->getVendor();

        if ($_store->vendor_freeshipping > 0) {
            $_prices = $_cart->getCartPrices();
            if ($_prices['salesPrice'] > $_store->vendor_freeshipping) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the shipper ID for a given order number
     * @access protected
     * @author Oscar van Eijk
     * @param int $_id The order ID
     * @return int The shipper ID, or -1 when not found
     */
    protected function getShipperIDForOrder($order_id) {
        /*
          $_db = &JFactory::getDBO();
          $_q = 'SELECT s.`shipping_rate_carrier_id` AS shipper_id '
          . 'FROM #__virtuemart_orders        AS o '

          . "WHERE o.`virtuemart_order_id` = $_id "
          . 'AND   o.`ship_method_id` = s.`virtuemart_shippingrate_id`';
          $_db->setQuery($_q);
          if (!($_r = $_db->loadAssoc())) {
          return -1;
          }
          return $_r['shipper_id'];
         * */
    }

    /**
     * Select the shipping rate ID, based on the selected shipper in combination with the
     * shipto address (country and zipcode)  .
     * @param object $_cart Cart object
     * @param int $_shipperID Shipper ID, by default taken from the cart
     * @return int Shipping rate ID, -1 when no match is found. Only 1 selected ID will be returned;
     * if more ID's match, the cheapest will be selected. ????
     */
    protected function selectShippingRate(VirtueMartCart $_cart, $_shipperId = 0) {

    }

    /**
     * This method checks if the selected shipper matches the current plugin
     * @param string $_selement Element name, taken from the plugin filename
     * @param int $_sid The shipper ID
     * @author Oscar van Eijk
     * @return True if the calling plugin has the given payment ID
     */
    final protected function selectedThisShipper($selement, $sid) {
        $db = JFactory::getDBO();

        if (VmConfig::isJ15()) {
            $q = 'SELECT COUNT(*) AS c '
                    . 'FROM #__virtuemart_custom_plg AS vm '
                    . ',    #__plugins AS j '
                    . "WHERE vm.virtuemart_shippingcarrier_id = '$sid' "
                    . 'AND   vm.shipping_carrier_jplugin_id = j.id '
                    . "AND   j.element = '$selement'";
        } else {
            $q = 'SELECT COUNT(*) AS c '
                    . 'FROM #__virtuemart_custom_plg AS vm '
                    . ',      #__extensions    AS      j '
                    . 'WHERE j.`folder` = "vmshipper" '
                    . "AND vm.virtuemart_shippingcarrier_id = '$sid' "
                    . 'AND   vm.shipping_carrier_jplugin_id = j.extension_id '
                    . "AND   j.element = '$selement'";
        }


        $db->setQuery($q);
        return $db->loadResult(); // TODO Error check
    }

    /**
     * Get the name of the shipper
     * @param int $_sid The Shipper ID
     * @author Valérie Isaksen
     * @return string Shipper name
     */
    final protected function getThisShipperNameById($id) {
        $db = JFactory::getDBO();
        $q = 'SELECT `shipping_carrier_name` '
                . 'FROM #__virtuemart_custom_plg '
                . "WHERE virtuemart_shippingcarrier_id ='$id' ";
        $db->setQuery($q);
        return $db->loadResult(); // TODO Error check
    }

    /**
     * Get the name of the shipper
     * @param int $_sid The Shipper ID
     * @author Valérie Isaksen
     * @return string Shipper name
     */
    public function getThisShipperName(Tablecustom_plg $shipping) {
        return $shipping->shipping_carrier_name;
    }

    /**
     * Get Shipper Data for a go given Shipper ID
     * @param int $_sid The Shipper ID
     * @author Valérie Isaksen
     * @return  Shipper data
     */
    final protected function getThisShipperData($virtuemart_shippingcarrier_id) {
        $db = JFactory::getDBO();
        $q = 'SELECT * '
                . 'FROM #__virtuemart_custom_plg '
                . "WHERE virtuemart_shippingcarrier_id ='" . $virtuemart_shippingcarrier_id . "' ";
        $db->setQuery($q);
        $result = $db->loadObject(); // TODO Error check
        return $result;
    }

    /**
     * This method writes all shipper plugin specific data to the plugin's table
     *
     * @param array $_values Indexed array in the format 'column_name' => 'value'
     * @param string $_table Table name
     * @author Oscar van Eijk
     */
    protected function writeShipperData($_values, $_table) {
        if (count($_values) == 0) {
            JError::raiseWarning(500, 'writeShipperData got no data to save to ' . $_table);
            return;
        }
        $_cols = array();
        $_vals = array();
        foreach ($_values as $_col => $_val) {
            $_cols[] = "`$_col`";
            $_vals[] = "'$_val'";
        }
        $_db = JFactory::getDBO();
        $_q = 'INSERT INTO `' . $_table . '` ('
                . implode(',', $_cols)
                . ') VALUES ('
                . implode(',', $_vals)
                . ')';
        $_db->setQuery($_q);
        if (!$_db->query()) {
            JError::raiseWarning(500, $_db->getErrorMsg());
        }
    }

    protected function calculateSalesPriceShipping($shipping_value, $tax_id, $currency_id) {

        if (!class_exists('calculationHelper'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'calculationh.php');
        if (!class_exists('CurrencyDisplay'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');

        $db = JFactory::getDBO();
        $calculator = calculationHelper::getInstance();
        $currency = CurrencyDisplay::getInstance();

        $shipping_value = $currency->convertCurrencyTo($currency_id, $shipping_value);

        $taxrules = array();
        if (!empty($tax_id)) {
            $q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $tax_id . '" ';
            $db->setQuery($q);
            $taxrules = $db->loadAssocList();
        }

        if (count($taxrules) > 0) {
            $salesPriceShipping = $calculator->roundDisplay($calculator->executeCalculation($taxrules, $shipping_value));
        } else {
            $salesPriceShipping = $shipping_value;
        }

        return $salesPriceShipping;
    }

    protected function getShippingHtml($shipper_name, $shipper_id, $selectedShipper, $cost, $tax_id) {
        if ($selectedShipper == $shipper_id) {
            $checked = '"checked"';
        } else {
            $checked = '';
        }

        if (!class_exists('VirtueMartModelVendor'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
        $vendor_id = 1;
        $vendor_currency = VirtueMartModelVendor::getVendorCurrency($vendor_id);

        if (!class_exists('CurrencyDisplay'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
        $currency = CurrencyDisplay::getInstance();
        $salesPriceShipping = $this->calculateSalesPriceShipping($cost, $tax_id, $vendor_currency->virtuemart_currency_id);

        $shippingCostDisplay = $currency->priceDisplay($salesPriceShipping);

        $html = '<input type="radio" name="shipper_id" id="shipper_id_' . $shipper_id . '" value="' . $shipper_id . '" ' . $checked . '>'
                . '<label for="shipper_id_' . $shipper_id . '">' . $shipper_name . " (" . $shippingCostDisplay . ")</label><br/>\n";
        return $html;
    }

    public function plgVmOnShipperSelectedCalculatePrice(VirtueMartCart $cart, Tablecustom_plg $shipping) {

        if (!$this->selectedThisShipper($this->_selement, $cart->virtuemart_shippingcarrier_id)) {
            return null; // Another shipper was selected, do nothing
        }

        $shipping->shipping_name = $this->getThisShipperName($shipping);

        if (!class_exists('JParameter'))
            require(JPATH_VM_LIBRARIES . DS . 'joomla' . DS . 'html' . DS . 'parameter.php' );
        $params = new JParameter($shipping->shipping_carrier_params);
        $shipping->shipping_rate_vat_id = $params->get('tax_id');
        $shipping->shipping_value = $params->get('shipping_value');

        return true;
    }

    function plgVmOnCheckAutomaticSelectedShipping(VirtueMartCart $cart) {

        $nbShipper = 0;
        $virtuemart_shippingcarrier_id = 0;
        $nbShipper = $this->getSelectableShipping($cart, $virtuemart_shippingcarrier_id);
        return ($nbShipper == 1) ? $virtuemart_shippingcarrier_id : 0;
    }

    function plgVmOnCheckShippingIsValid(VirtueMartCart $cart) {
        if (!$this->selectedThisShipper($this->_selement, $cart->virtuemart_shippingcarrier_id)) {
            return null; // Another shipper was selected, do nothing
        }
        $shipper = $this->getThisShipperData($cart->virtuemart_shippingcarrier_id);
        return $this->checkShippingConditions($cart, $shipper);
    }

    function getParamShippings($cart, &$nbShipper, &$virtuemart_shippingcarrier_id, $selectedShipper=0) {

        return null;
    }

    /*
     * This method returns the number of shipping methods valid
     */

    function getSelectableShipping(VirtueMartCart $cart, &$virtuemart_shippingcarrier_id) {
        $nbShipper = 0;
        if ($this->getShippers($cart->vendorId) === false) {
            return false;
        }

        foreach ($this->shippers as $shipper) {
            if ($this->checkShippingConditions($cart, $shipper)) {
                $nbShipper++;
                $virtuemart_shippingcarrier_id = $shipper->virtuemart_shippingcarrier_id;
            }
        }
        return $nbShipper;
    }

    function displayTaxRule($tax_id) {
        $html = '';
        $db = JFactory::getDBO();
        if (!empty($tax_id)) {
            $q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $tax_id . '" ';
            $db->setQuery($q);
            $taxrule = $db->loadObject();

            $html = $taxrule->calc_name . '(' . $taxrule->calc_kind . ':' . $taxrule->calc_value_mathop . $taxrule->calc_value . ')';
        }
        return $html;
    }

}