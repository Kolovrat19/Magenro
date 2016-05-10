<?php
class WebIntellect_NovaPoshta_Model_Observer
{
    /** При нажатии ОПЛАТА в корзине
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function loadQuoteAddressCollectionData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Resource_Quote_Address_Collection $addressCollection */
        $addressCollection = $observer->getData('quote_address_collection');

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $select = $connection->select();
        $select->from($resource->getTableName('novaposhta_quote_address'));
        $select->where('address_id IN (?)', $addressCollection->getAllIds());

        foreach ($connection->fetchAll($select) as $row) {
            $addressId = $row['address_id'];
            unset($row['address_id']);
            $addressCollection->getItemById($addressId)->addData($row);
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function loadQuoteAddressData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote_Address $address */

        $address = $observer->getData('quote_address');
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $select = $connection->select();
        $select->from($resource->getTableName('novaposhta_quote_address'));
        $select->where('address_id = ?', $address->getId());

        if ($data = $connection->fetchRow($select)) {
            unset($data['address_id']);
            $address->addData($data);
        }

        return $this;
    }

    /** При нажатии ОПЛАТА в корзине
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveQuoteAddressData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote_Address $address */

        $address = $observer->getData('quote_address');

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $warehouseId = $address->getData('warehouse_id');

        $warehouseLabel = '';

        if ($warehouseId) {
            /** @var Ak_NovaPoshta_Model_Warehouse $warhouse */
            $warehouse = Mage::getModel('novaposhta/warehouse')->load($warehouseId);
            $warehouseLabel = $warehouse->getCity()->getData('name_ru') . ', ' . $warehouse->getData('address_ru') . ', ' . $warehouse->getData('phone');
        }

        $data = array(
            'address_id' => $address->getId(),
            'warehouse_id' => $warehouseId,
            'warehouse_label' => $warehouseLabel,
        );

        $tableName = $resource->getTableName('novaposhta_quote_address');

        if ($data['warehouse_id'] || $data['warehouse_label']) {
            $connection->insertOnDuplicate($tableName, $data);
        } else {
            $connection->delete($tableName, sprintf('address_id = %d', $data['address_id']));
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function loadOrderAddressCollectionData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Resource_Order_Address_Collection $addressCollection */
        $addressCollection = $observer->getData('order_address_collection');

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $select = $connection->select();
        $select->from($resource->getTableName('novaposhta_order_address'));
        $select->where('address_id IN (?)', $addressCollection->getAllIds());

        foreach ($connection->fetchAll($select) as $row) {
            $addressId = $row['address_id'];
            unset($row['address_id']);
            $addressCollection->getItemById($addressId)->addData($row);
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function loadOrderAddressData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Address $address */

        $address = $observer->getData('address');
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $select = $connection->select();
        $select->from($resource->getTableName('novaposhta_order_address'));
        $select->where('address_id = ?', $address->getId());

        if ($data = $connection->fetchRow($select)) {
            unset($data['address_id']);
            $address->addData($data);
        }

        return $this;
    }

    /** Сохранение заказа
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveOrderAddressData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Address $address */
        $address = $observer->getData('address');
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);

        $warehouseId = $address->getData('warehouse_id');
        $warehouseLabel = '';

        if ($warehouseId) {
            /** @var Ak_NovaPoshta_Model_Warehouse $warhouse */
            $warehouse = Mage::getModel('novaposhta/warehouse')->load($warehouseId);
            $warehouseLabel = $warehouse->getCity()->getData('name_ru') . ', ' . $warehouse->getData('address_ru') . ', ' . $warehouse->getData('phone');
        }

        $data = array(
            'address_id' => $address->getId(),
            'warehouse_id' => $warehouseId,
            'warehouse_label' => $warehouseLabel,
        );

        $tableName = $resource->getTableName('novaposhta_order_address');

        if ($data['warehouse_id'] || $data['warehouse_label']) {
            $connection->insertOnDuplicate($tableName, $data);
        } else {
            $connection->delete($tableName, sprintf('address_id = %d', $data['address_id']));
        }

        return $this;
    }

    /** Сохранение заказа
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveOrderData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getData('order');

        if ($warehouseLabel = $order->getShippingAddress()->getData('warehouse_label')) {
            $shippingDescription = $order->getData('shipping_description');
            $order->setData('shipping_description', $shippingDescription . PHP_EOL . " ({$warehouseLabel}) ");
        }

        return $this;
    }

    /** Переход от вычисления суммы доставки до Оплаты картами
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveShippingMethodBefore(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Controller_Varien_Action $controller */
        $controller = $observer->getData('controller_action');

        if (preg_match('/^novaposhta_type_\d+$/i', $controller->getRequest()->getParam('shipping_method'))) {
            $warehouse = Mage::getModel('novaposhta/warehouse')->load($controller->getRequest()->getParam('novaposhta_warehouse'));
            if (!$warehouse->getId()) {
                Mage::throwException(Mage::helper('novaposhta')->__('Invalid Warehouse.'));
            }
            /** @var Mage_Sales_Model_Quote $quote */
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $quote->getShippingAddress()->setData('warehouse_id', $warehouse->getId());
        }
    }
}