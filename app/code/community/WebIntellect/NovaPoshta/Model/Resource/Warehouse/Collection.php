<?php
class WebIntellect_NovaPoshta_Model_Resource_Warehouse_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('novaposhta/warehouse');
    }
}
