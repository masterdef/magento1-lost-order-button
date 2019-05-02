<?php

class Xpscommerce_LostOrderAdmin_Adminhtml_RefundorderController
    extends Mage_Adminhtml_Controller_Action
    //Mage_Adminhtml_Sales_Order_CreditmemoController
{
    public function indexAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        if ($this->_canCreditmemo($order)) {
            $creditmemo = $this->_initCreditmemo($order);
            /*
            $service = Mage::getModel('sales/service_order', $order);
            $data = array('qtys' => array());

            foreach ($order->getItemsCollection() as $item) {
                $data['qtys'][$item->getId()]   = array(
                    'qty' => $item->getQtyOrdered(),
                );
            }

            $creditmemo = $service->prepareCreditmemo($data);
            */

            $creditmemo->setRefundRequested(true);
            $creditmemo->register();

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
