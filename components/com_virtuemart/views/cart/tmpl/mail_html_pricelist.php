<?php
/**
*
* Layout for the shopping cart
*
* @package	VirtueMart
* @subpackage Cart
* @author Max Milbers
*
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
// jimport( 'joomla.application.component.view');
// $viewEscape = new JView();
// $viewEscape->setEscape('htmlspecialchars');

		//of course, some may argue that the $product_rows should be generated in the view.html.php, but
		//
		$product_rows = array();

		$i=0;
		foreach ($this->cart->products as $priceKey=>$product){
			// Added for the zone shipping module
			//$vars["zone_qty"] += $product["quantity"];

			if ($i % 2) $product_rows[$i]['row_color'] = "sectiontableentry2";
			else $product_rows[$i]['row_color'] = "sectiontableentry1";
			$product->virtuemart_category_id = $this->cart->getCardCategoryId($product->virtuemart_product_id);
			/* Create product URL */
			$url = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id);

			/** @todo Add variants */
			$product_rows[$i]['product_name'] = JHTML::link($url, $product->product_name);



			// Add the variants
			if(!empty($product->customfieldsCart)){
				$product_rows[$i]['customfieldsCart'] = ShopFunctions::customFieldInCartDisplay($priceKey,$product->customfieldsCart);
			} else {
				$product_rows[$i]['customfieldsCart'] ='';
			}


			$product_rows[$i]['product_sku'] = $product->product_sku;

			/* Product PRICE */
			$product_rows[$i]['salesPrice'] = empty($this->prices[$priceKey]['salesPrice'])? 0:$this->prices[$priceKey]['salesPrice'];
			$product_rows[$i]['basePriceWithTax'] = empty($this->prices[$priceKey]['salesPrice'])? 0:$this->prices[$priceKey]['basePriceWithTax'];
//			$product_rows[$i]['basePriceWithTax'] = $this->prices[$priceKey]['basePriceWithTax'];
			$product_rows[$i]['subtotal'] = $this->prices[$priceKey]['subtotal'];
			$product_rows[$i]['subtotal_tax_amount'] = $this->prices[$priceKey]['subtotal_tax_amount'];
			$product_rows[$i]['subtotal_discount'] = $this->prices[$priceKey]['subtotal_discount'];
			$product_rows[$i]['subtotal_with_tax'] = $this->prices[$priceKey]['subtotal_with_tax'];

			// UPDATE CART / DELETE FROM CART
			if(!empty($this->layoutName) && $this->layoutName=='default'){
			$product_rows[$i]['update_form'] = '<form action="index.php" method="post" style="display: inline;">
				<input type="hidden" name="option" value="com_virtuemart" />
				<input type="text" title="'. JText::_('COM_VIRTUEMART_CART_UPDATE') .'" class="inputbox" size="3" maxlength="4" name="quantity" value="'.$product->quantity.'" />
				<input type="hidden" name="view" value="cart" />
				<input type="hidden" name="task" value="update" />
				<input type="hidden" name="cart_virtuemart_product_id" value="'.$priceKey.'" />
				<input type="submit" class="vmicon vm2-add_quantity_cart" name="update" title="'. JText::_('COM_VIRTUEMART_CART_UPDATE') .'" align="middle" value=" "/>
			  </form>';
			$product_rows[$i]['delete_form'] = '<form action="index.php" method="post" name="delete" style="display: inline;">
				<input type="hidden" name="option" value="com_virtuemart" />
				<input type="hidden" name="view" value="cart" />
				<input type="hidden" name="task" value="delete" />
				<input type="hidden" name="cart_virtuemart_product_id" value="'.$priceKey.'" />
				<input type="submit" class="vmicon vm2-remove_from_cart" name="delete" title="'. JText::_('COM_VIRTUEMART_CART_DELETE') .'" align="middle" value=" "/>
			  </form>';
			} else {
				$product_rows[$i]['update_form'] = $product->quantity;
				$product_rows[$i]['delete_form'] ='';
			}
			$i++;
		} // End of for loop through the Cart


		?>
		<table class="cart-summary" cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<th align="left"><?php echo JText::_('COM_VIRTUEMART_CART_NAME') ?></th>
				<th align="left" ><?php echo JText::_('COM_VIRTUEMART_CART_SKU') ?></th>
 				<th align="center" width="100px" ><?php echo JText::_('COM_VIRTUEMART_CART_PRICE') ?></th>
				<th align="right" width="100px" ><?php echo JText::_('COM_VIRTUEMART_CART_QUANTITY') ?></th>

                                        <?php if ( VmConfig::get('show_tax')) { ?>
                                <th align="right" width="60px"><?php  echo "<span  style='color:gray'>".JText::_('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') ?></th>
				<?php } ?>
                                <th align="right" width="60px"><?php echo "<span  style='color:gray'>".JText::_('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?></th>
				<th align="right" width="70px"><?php echo JText::_('COM_VIRTUEMART_CART_TOTAL') ?></th>
			</tr>
		<?php foreach( $product_rows as $prow ) { ?>
			<tr valign="top" class="<?php echo $prow['row_color'] ?>">
				<td align="left" ><?php echo $prow['product_name'].$prow['customfieldsCart']; ?></td>
				<td align="left" ><?php echo $prow['product_sku'] ?></td>
				<td align="center" >
					<?php if ($prow['basePriceWithTax'] != $prow['salesPrice'] ) {
						echo '<span style="text-decoration:line-through">'.$prow['basePriceWithTax'] .'</span><br />' ;
					}
					echo $prow['salesPrice'] ;
					?>
				</td>
				<td align="right" ><?php echo $prow['update_form'] ?>
					<?php echo $prow['delete_form'] ?>
				</td>

				<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo "<span  style='color:gray'>".$prow['subtotal_tax_amount']."</span>" ?></td>
                                <?php } ?>
				<td align="right"><?php echo "<span  style='color:gray'>".$prow['subtotal_discount']."</span>" ?></td>
				<td colspan="1" align="right"><?php echo $prow['subtotal_with_tax'] ?></td>
			</tr>
		<?php } ?>
		<!--Begin of SubTotal, Tax, Shipping, Coupon Discount and Total listing -->
                  <?php if ( VmConfig::get('show_tax')) { $colspan=3; } else { $colspan=2; } ?>
		<tr>
			<td colspan="4">&nbsp;</td>

			<td colspan="<?php echo $colspan ?>"><hr /></td>
		</tr>
		  <tr class="sectiontableentry1">
			<td colspan="4" align="right"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL'); ?></td>

                        <?php if ( VmConfig::get('show_tax')) { ?>
			<td align="right"><?php echo "<span  style='color:gray'>".$this->prices['taxAmount']."</span>" ?></td>
                        <?php } ?>
			<td align="right"><?php echo "<span  style='color:gray'>".$this->prices['discountAmount']."</span>" ?></td>
			<td align="right"><?php echo $this->prices['salesPrice'] ?></td>
		  </tr>

		<?php
		foreach($this->cartData['dBTaxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>

                                   <?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"> </td>
                                <?php } ?>
				<td align="right"><?php echo -$this->prices[$rule['virtuemart_calc_id'].'Diff'];  ?> </td>
				<td align="right"><?php echo $this->prices[$rule['virtuemart_calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		} ?>
		<?php
		if (VmConfig::get('coupons_enable')) {
		?>
			<tr class="sectiontableentry2">
				<td colspan="2" align="left"><?php if(!empty($this->layoutName) && $this->layoutName=='default') echo JHTML::_('link', JRoute::_('index.php?view=cart&task=edit_coupon'), JText::_('COM_VIRTUEMART_CART_EDIT_COUPON')); ?> </td>
				<?php if (!empty($this->cartData['couponCode'])) { ?>
					<td colspan="2" align="left"><?php
						echo $this->cartData['couponCode'] . ' (' . $this->cartData['couponDescr'] . ')';
					?> </td>

                                        <?php if ( VmConfig::get('show_tax')) { ?>
					<td align="right"><?php echo $this->prices['couponTax']; ?> </td>
                                        <?php } ?>
					<td align="right">&nbsp;</td>
					<td align="right"><?php echo $this->prices['salesPriceCoupon']; ?> </td>
				<?php } else { ?>
					<td colspan="6" align="left">&nbsp;</td>
				<?php } ?>
			</tr>
		<?php } ?>
		<tr class="sectiontableentry1">
			<td colspan="4" align="left"><?php echo $this->cartData['shippingName']; ?> </td>
			<?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php echo "<span  style='color:gray'>".$this->prices['shippingTax']."</span>"; ?> </td>
			<?php } ?>
			<td></td>
			<td align="right"><?php echo $this->prices['salesPriceShipping']; ?> </td>
		</tr>

		<tr class="sectiontableentry1">
			<td colspan="4" align="left"><?php echo $this->cartData['paymentName']; ?> </td>
							 <?php if ( VmConfig::get('show_tax')) { ?>
			<td align="right"><?php //echo $this->prices['paymentTax']; ?> </td>
							<?php } ?>
			<td align="right"><?php echo "<span  style='color:gray'>".$this->prices['paymentDiscount']."</span>"; ?></td>
			<td align="right"><?php  echo $this->prices['salesPricePayment']; ?> </td>
		</tr>
		<?php

		foreach($this->cartData['taxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>
				<td> </td>
				<td align="right"><?php echo $this->prices[$rule['virtuemart_calc_id'].'Diff']; ?> </td>
				<td align="right"><?php    ?> </td>
				<td align="right"><?php echo $this->prices[$rule['virtuemart_calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		}

		foreach($this->cartData['dATaxRulesBill'] as $rule){ ?>
			<tr class="sectiontableentry<?php $i ?>">
				<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>
				<td> </td>
                                     <?php if ( VmConfig::get('show_tax')) { ?>
				<td align="right"><?php  ?> </td>
                                <?php } ?>
				<td align="right"><?php echo $this->prices[$rule['virtuemart_calc_id'].'Diff'];   ?> </td>
				<td align="right"><?php echo $this->prices[$rule['virtuemart_calc_id'].'Diff'];   ?> </td>
			</tr>
			<?php
			if($i) $i=1; else $i=0;
		} ?>

		  <tr>
			<td colspan="4">&nbsp;</td>
			<td colspan="<?php echo $colspan ?>"><hr /></td>
		  </tr>
		  <tr class="sectiontableentry2">
			<td colspan="4" align="right"><?php echo JText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?>: </td>

                        <?php if ( VmConfig::get('show_tax')) { ?>
			<td align="right"> <?php echo "<span  style='color:gray'>".$this->prices['billTaxAmount']."</span>" ?> </td>
                        <?php } ?>
			<td align="right"> <?php echo "<span  style='color:gray'>".$this->prices['billDiscountAmount']."</span>" ?> </td>
			<td align="right"><strong><?php echo $this->prices['billTotal'] ?></strong></td>
		  </tr>



	</table>