<?php

/**
 *
 * Controller for the Payement Response
 *
 * @package	VirtueMart
 * @subpackage paymentResponse
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: cart.php 3388 2011-05-27 13:50:18Z alatak $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * Controller for the payment response view
 *
 * @package VirtueMart
 * @subpackage paymentResponse
 * @author Valérie Isaksen
 *
 */
class VirtueMartControllerPluginresponse extends JController {

    /**
     * Construct the cart
     *
     * @access public
     */
    public function __construct() {
	parent::__construct();
    }

    /**
     * ResponseReceived()
     * From the plugin page, the user returns to the shop. The order email is sent, and the cart emptied.
     *
     * @author Valerie Isaksen
     *
     */
    function pluginResponseReceived() {

	 PaymentResponseReceived();
	 ShipmentResponseReceived();
    }
 /**
     * ResponseReceived()
     * From the payment page, the user returns to the shop. The order email is sent, and the cart emptied.
     *
     * @author Valerie Isaksen
     *
     */
    function PaymentResponseReceived() {

if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');	JPluginHelper::importPlugin('vmpayment');
	$pm = JRequest::getInt('pm', 0);
	$pelement = JRequest::getWord('pelement');

	$return_context = "";
	$dispatcher = JDispatcher::getInstance();
	$html = "";
	$returnValues = $dispatcher->trigger('plgVmOnResponseReceived', array('payment',  'virtuemart_order_id' => &$virtuemart_order_id, 'html' => &$html));


	foreach ($returnValues as $returnValue) {
	    if ($returnValue !== null) {
		if ($returnValue) {
		    if ($virtuemart_order_id) {
			if (!class_exists('VirtueMartCart'))
			    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
			// get the correct cart / session
			$cart = VirtueMartCart::getCart();

			// send the email ONLY if payment has been accepted
			if (!class_exists('VirtueMartModelOrders'))
			    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
			$order = new VirtueMartModelOrders();
			$orderitems = $order->getOrder($virtuemart_order_id);
			//vmdebug('PaymentResponseReceived CART', $orderitems);
			$cart->sentOrderConfirmedEmail($orderitems);
			//We delete the old stuff

			$cart->emptyCart();
			break; // This was the active plugin, so there's nothing left to do here.
		    }
		}
	    }
	    // Returnvalue 'null' must be ignored; it's an inactive plugin so look for the next one
	}
	JRequest::setVar('paymentResponse', Jtext::_('COM_VIRTUEMART_CART_THANKYOU'));
	JRequest::setVar('paymentResponseHtml', $html);
	$view = $this->getView('paymentresponse', 'html');
	$layoutName = JRequest::getVar('layout', 'default');
	$view->setLayout($layoutName);

	/* Display it all */
	$view->display();
    }
    function ShipmentResponseReceived() {

    }
    /**
     * PaymentUserCancel()
     * From the payment page, the user has cancelled the order. The order previousy created is deleted.
     * The cart is not emptied, so the user can reorder if necessary.
     * then delete the order
     * @author Valerie Isaksen
     *
     */
    function pluginUserCancel() {

	if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

	if (!class_exists('VirtueMartCart'))
	    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');

	JPluginHelper::importPlugin('vmpayment');
/*
	if (!JFactory::getSession(array('id' => $return_context))) {
	    return false;
	}
 * */

	$dispatcher = JDispatcher::getInstance();
	$returnValues = $dispatcher->trigger('plgVmOnPaymentUserCancel', array(
	    'virtuemart_order_id' => &$virtuemart_order_id));

	foreach ($returnValues as $returnValue) {
	    if ($returnValue !== null) {
		if ($returnValue == 1) {
		    // $returnValue[]
		    JRequest::setVar('paymentResponse', $returnValue);
		    if (!class_exists('VirtueMartCart'))
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		    if ($virtuemart_order_id) {
			// send the email only if payment has been accepted
			if (!class_exists('VirtueMartModelOrders'))
			    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
			$order = new VirtueMartModelOrders();
			$order->remove(array('virtuemart_order_id' => $virtuemart_order_id));
		    }
		    break; // This was the active plugin, so there's nothing left to do here.
		}
	    }
	    // Returnvalue 'null' must be ignored; it's an inactive plugin so look for the next one
	}


	$view = $this->getView('cart', 'html');
	$layoutName = JRequest::getWord('layout', 'default');
	$view->setLayout($layoutName);
	JRequest::setVar('paymentResponse', Jtext::_('COM_VIRTUEMART_PAYMENT_USER_CANCEL'));

	/* Display it all */
	$view->display();
    }

    /**
     * Attention this is the function which processs the response of the payment plugin
     *
     * @author Valerie Isaksen
     * @return success of update
     */
    function pluginNotification() {


if (!class_exists('vmPSPlugin')) require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');

	if (!class_exists('VirtueMartCart'))
	    require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');

	if (!class_exists('VirtueMartModelOrders'))
	    require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );

	JPluginHelper::importPlugin('vmpayment');

	$dispatcher = JDispatcher::getInstance();
	$returnValues = $dispatcher->trigger('plgVmOnPaymentNotification', array(
	    'return_context' => &$return_context,
	    'virtuemart_order_id' => &$virtuemart_order_id,
	    'new_status' => &$new_status));

	foreach ($returnValues as $returnValue) {
	    if ($returnValue !== null) {
		$this->emptyCart($return_context);
		if ($virtuemart_order_id) {
		    // send the email only if payment has been accepted
		    if (!class_exists('VirtueMartModelOrders'))
			require( JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php' );
		    $modelOrder = new VirtueMartModelOrders();
		    $orders[$virtuemart_order_id]['order_status'] = $new_status;
		    $orders[$virtuemart_order_id]['virtuemart_order_id'] = $virtuemart_order_id;
		    $customer_notifed[$virtuemart_order_id] = 0;
		    JRequest::setVar('notify_customer', $customer_notifed);
		    $comments[$virtuemart_order_id] = 0;
		    JRequest::setVar('comment', $comments);
		    $modelOrder->updateOrderStatus($orders); // take directly the session from the DB
		    // remove vmcart
		}
		break; // This was the active plugin, so there's nothing left to do here.
	    }
	}
	// Returnvalue 'null' must be ignored; it's an inactive plugin so look for the next one
    }

    function emptyCart($session_name) {

	$sessionDatabase = new JSessionStorageDatabase();
	if (!$sessionDatabase->read($session_name)) {
	    // session does not exist, should not be created
	    return false;
	}

	$options['name'] = $session_name;
	//$session = JFactory::getSession($options);
	$cart = VirtueMartCart::getCart($options);
	$cart->emptyCart();
	return true;
    }

}

//pure php no Tag