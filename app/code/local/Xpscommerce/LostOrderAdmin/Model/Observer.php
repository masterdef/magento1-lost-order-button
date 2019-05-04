<?php
/**
 * Magento
 *
 */


/**
 * LostOrderAdmin Observer
 *
 * @category    Xpscommerce
 * @package     Xpscommerce_LostOrderAdmin
 * @author      @ridestore
 */
class Xpscommerce_LostOrderAdmin_Model_Observer {
    /**
     * Add LostOrder Button to Order View
     *
     * @param Varien_Event_Observer $observer
     * @return Xpscommerce_LostOrderAdmin_Model_Observer
     */
    public function addButtonLostOrder(Varien_Event_Observer $observer) {
        $block = Mage::app()->getLayout()->getBlock('sales_order_edit');
        if (!$block) {
            return $this;
        }
        $order = Mage::registry('current_order');

        if (!$order->hasCreditmemos() && $order->hasShipments()) {
            $this->_addButtonLostOrderToBlock($block, $order);
        }

        return $this;
    }

    /**
     * Put button to block
     * @param Mage_Adminhtml_Block_Sales_Order_View $block
     * @param Mage_Sales_Model_Order $order
     * @return Xpscommerce_LostOrderAdmin_Model_Observer
     **/
    private function _addButtonLostOrderToBlock(Mage_Adminhtml_Block_Sales_Order_View $block, Mage_Sales_Model_Order $order) {
            $url   = Mage::helper("adminhtml")->getUrl("lostorderadmin/adminhtml_refundorder",
                array('order_id' => $order->getId())
            );
            $message = Mage::helper('lostorderadmin')->__('Is this a lost order and have you sent a replacement?');

            $block->addButton(
                'button_id',
                array(
                    'label'   => Mage::helper('lostorderadmin')->__('Lost Order'),
                    'onclick' => "confirmSetLocation('{$message}', '{$url}')",
                    'class'   => 'go'
                )
            );
        
            return $this;
    }
}

