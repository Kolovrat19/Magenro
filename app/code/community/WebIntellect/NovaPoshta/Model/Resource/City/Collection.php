<?php
class WebIntellect_NovaPoshta_Model_Resource_City_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('novaposhta/city');
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $resultingArray = array();
        foreach ($this->getData() as $val){

            $resultingArray[$val['ref']] = $val['name_ru'];

       }



        return $resultingArray;
    }

}
