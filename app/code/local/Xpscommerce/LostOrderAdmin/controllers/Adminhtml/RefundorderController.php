<?php

class Xpscommerce_LostOrderAdmin_Adminhtml_RefundorderController
    extends Mage_Adminhtml_Controller_Action
    //Mage_Adminhtml_Sales_Order_CreditmemoController
{

    /**
        When proceeding I want the following to be made:
        All qty of the products on the order should be “refunded”

        This because I want the item status to be set to “refunded” (IMPORTANT that the product is NOT put back in stock)
        The subtotal should then be added to “Adjustment fee” so that we don’t refund this amount to the customer.
        The Shipping & Handling fee should be added to “Refund Shipping & Handling” so this is refunded to the customer (if 0 then 0 will be refunded to customer)
        After this is done I want a automessage to be added as a comment “[MAGENTO USER NAME]: Order is lost and has been locked for handling. To refund the customer make a manual creditmemo on this order�
        Video: 
        https://cl.ly/9f869eb55e92
    **/
    public function indexAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->_canCreditmemo($order)) {
            $creditmemo = $this->_initCreditmemo($order);

            $creditmemo->setGrandTotal($order->getShippingAmount());
            $creditmemo->setBaseGrandTotal($order->getShippingAmount());
            $creditmemo->setAdjustmentNegative($order->getSubtotal());
            $creditmemo->setBaseAdjustment(-($order->getSubtotal()));
            $creditmemo->setAdjustment(-($order->getSubtotal()));
            $creditmemo->setRefundRequested(true);
            $creditmemo->register();


            $username = Mage::getSingleton('admin/session')->getUser()->getUsername();
            $order->addStatusHistoryComment("{$username}: Order is lost and has been locked for handling. To refund the customer make a manual creditmemo on this order");
            $order->save();

            $this->_saveCreditmemo($creditmemo);
        }

        $this->_redirect('adminhtml/sales_order/view', array('order_id' => $orderId));
    }

    /**
     * Initialize creditmemo model instance
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    protected function _initCreditmemo($order)
    {
        $creditmemo = false;

        if ($order) {
            $data = array('qtys' => array());

            foreach ($order->getItemsCollection() as $item) {
                $data['qtys'][$item->getId()]   = $item->getQtyOrdered();
            }

            $service = Mage::getModel('sales/service_order', $order);
            $creditmemo = $service->prepareCreditmemo($data);

            /**
             * Process back to stock flags
             */
            foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                $orderItem = $creditmemoItem->getOrderItem();
                $creditmemoItem->setBackToStock(false);
            }
        }

        $args = array('creditmemo' => $creditmemo, 'request' => $this->getRequest());
        Mage::dispatchEvent('adminhtml_sales_order_creditmemo_register_before', $args);

        Mage::register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }

    /**
     * Save creditmemo and related order, invoice in one transaction
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function _saveCreditmemo($creditmemo)
    {
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($creditmemo)
            ->addObject($creditmemo->getOrder());
        if ($creditmemo->getInvoice()) {
            $transactionSave->addObject($creditmemo->getInvoice());
        }
        $transactionSave->save();

        return $this;
    }

    /**
     * Check if creditmeno can be created for order
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    protected function _canCreditmemo($order)
    {
        /**
         * Check order existing
         */
        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('The order no longer exists.'));
            return false;
        }

        /**
         * Check creditmemo create availability
         */
        if (!$order->canCreditmemo()) {
            $this->_getSession()->addError($this->__('Cannot create credit memo for the order.'));
            return false;
        }
        return true;
    }
}
