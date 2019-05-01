<?php
/**
 * Magento
 *
 */


/**
 */
class Xpscommerce_LostOrderAdmin_Model_Observer {
    public function addButtonLostOrder(Varien_Event_Observer $observer) {
        $block = Mage::app()->getLayout()->getBlock('sales_order_edit');
        if (!$block) {
            return $this;
        }
        $order = Mage::registry('current_order');
        $url   = Mage::helper("adminhtml")->getUrl(
            "adminhtml/lostorderadmin/refundorder",
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

