<?php
class WebIntellect_NovaPoshta_Model_Import
{
    /** @var  array */
    protected $_existingCities;

    /** @var  array */
    protected $_existingWarehouses;

    protected $_integer = "";

    protected $_char = '-';

    protected $_dataMapInvoice = array(
          'Ref' => 'ref',
        // 'DescriptionRu' => 'name_ru',
        //  'Description' => 'name_ua'
    );

    protected $_dataMapCity = array(
        'Ref' => 'ref',
        'DescriptionRu' => 'name_ru',
        'Description' => 'name_ua'
    );

    protected $_dataMapWarehouse = array(

        'Ref' => 'ref',
        'CityRef' => 'city_id',
        'Description' => 'address_ua',
      'DescriptionRu' => 'address_ru',
        'Phone' => 'phone',
       // 'weekday_work_hours' => 'weekday_work_hours',
       // 'weekday_reseiving_hours' => 'weekday_reseiving_hours',
      //  'weekday_delivery_hours' => 'weekday_delivery_hours',
       // 'saturday_work_hours' => 'saturday_work_hours',
       // 'saturday_reseiving_hours' => 'saturday_reseiving_hours',
      // 'saturday_delivery_hours' => 'saturday_delivery_hours',
        'Max_Weight_Allowed' => 'max_weight_allowed',
        'Longitude' => 'longitude',
        'Latitude' => 'latitude',
        'Number' => 'number_in_city'
    );

    /**
     * @throws Exception
     * @return Ak_NovaPoshta_Model_Import
     */
    public function run()
    {
        $apiKey = Mage::helper('novaposhta')->getStoreConfig('api_key');
        $apiUrl = Mage::helper('novaposhta')->getStoreConfig('api_url');
        if (!$apiKey || !$apiUrl) {
            Mage::helper('novaposhta')->log('No API key or API URL configured');
            throw new Exception('No API key or API URL configured');
        }

        try {
            /** @var $apiClient Ak_NovaPoshta_Model_Api_Client */
            $apiClient = Mage::getModel('novaposhta/api_client', array($apiUrl, $apiKey));
            Mage::helper('novaposhta')->log('Start city import');
            $cities = $apiClient->getCityWarehouses();
            $this->_importCities($cities);
            Mage::helper('novaposhta')->log('End city import');

            Mage::helper('novaposhta')->log('Start warehouse import');
            $warehouses = $apiClient->getWarehouses();
            $this->_importWarehouses($warehouses);
            //Mage::helper('novaposhta')->log( $a);
            Mage::helper('novaposhta')->log('End warehouse import');

            Mage::helper('novaposhta')->log('Start invoices import');
            $invoices = $apiClient->getExInvoice();
            $this->_importInvoice($invoices);
            //Mage::helper('novaposhta')->log( $a);
            Mage::helper('novaposhta')->log('End invoices import');


        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('novaposhta')->log("Exception: \n" . $e->getMessage());
            throw $e;
        }

        return $this;
    }

    /**
     * @param array $cities
     * @return bool
     * @throws Exception
     *
     *
     */

    protected function _importInvoice(array $invoices)
    {
        if (empty($invoices)) {
            Mage::helper('novaposhta')->log('No city with warehouses received');
            throw new Exception('No city with warehouses received');
        }



        $cities = $this->_applyMap($invoices, $this->_dataMapInvoice);





        return $cities;
    }










    protected function _importCities(array $cities)
    {
        if (empty($cities)) {
            Mage::helper('novaposhta')->log('No city with warehouses received');
            throw new Exception('No city with warehouses received');
        }

        $tableName  = Mage::getSingleton('core/resource')->getTableName('novaposhta_city');
        $connection = $this->_getConnection();

        $cities = $this->_applyMap($cities, $this->_dataMapCity);

        $existingCities = $this->_getExistingCities();
        $citiesToDelete = array_diff(array_keys($existingCities), array_keys($cities));

        if (count($citiesToDelete) > 0) {
            $connection->delete($tableName, $citiesToDelete);
            Mage::helper('novaposhta')->log(sprintf("Warehouses deleted: %s", implode(',', $citiesToDelete)));
        }

        if (count($cities) > 0) {
            $tableName  = Mage::getSingleton('core/resource')->getTableName('novaposhta_city');
            $connection = $this->_getConnection();
            $connection->beginTransaction();
            try {
                foreach ($cities as $data) {

                    $connection->insertOnDuplicate($tableName, $data);

                }

                $connection->commit();
            } catch (Exception $e) {
                $connection->rollBack();
                throw $e;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    protected function _getExistingCities()
    {
        if (!$this->_existingCities) {
            /** @var Ak_NovaPoshta_Model_Resource_City_Collection $collection */
            $collection = Mage::getResourceModel('novaposhta/city_collection');
            $this->_existingCities = $collection->getAllIds();
        }
        return $this->_existingCities;
    }

    /**
     * @param array $existingCity
     * @param array $city
     *
     * @return bool
     */
    protected function _isCityChanged(array $existingCity, array $city)
    {
        foreach ($existingCity as $key => $value) {
            if (isset($city[$key]) && $city[$key] != $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $apiObjects
     * @param array $map
     * @return array
     *
     */
    protected function _applyMap(array $apiObjects, array $map)
    {
        $helper    = Mage::helper('novaposhta');
        $resultingArray = array();
        $id = 0;

        foreach ($apiObjects as $apiObject) {
            $id ++;
            $resultingArray[$id] = array();

            foreach ($apiObject as $apiKey => $value) {


                if (!isset($map[$apiKey])) {

                    continue;
                }
                $resultingArray[$id][$map[$apiKey]] = addcslashes((string)$value, "\000\n\r\\'\"\032");
                $resultingArray[$id]['id'] = $id;
            }
        }
     //  $helper->log( $resultingArray );
        return $resultingArray;
    }


/*

    protected function _applyMap(array $apiObjects, array $map)
    {
        $helper    = Mage::helper('novaposhta');
        $resultingArray = array();
        //$id = 0;
        $idKey = array_search('id', $map);

        foreach ($apiObjects as $apiObject) {
            $id = str_replace($this->_char, $this->_integer,(string) $apiObject->$idKey);
            $resultingArray[$id] = array();
          //  $ref =  str_replace($this->_char, $this->_integer,$apiObject->$refKey);

         // $apiObject=krsort ($apiObject);



            foreach ($apiObject as $apiKey => $value) {


                if (!isset($map[$apiKey])) {

                    continue;
                }
            //    $resultingArray[$id]['id'] = $map['id'];
                $resultingArray[(string)$id][$map[$apiKey]] = str_replace($this->_char, $this->_integer,(string)$value);

            //    $resultingArray[$id]['ref'] = $ref;
            }
        }
       //  $helper->log( $resultingArray);
        return $resultingArray;

    } *//*
       protected function _applyMap(array $apiObjects, array $map)
       {
           $helper    = Mage::helper('novaposhta');

           $resultingArray = array();
           $idKey = array_search('ref', $map);



           foreach ($apiObjects as $apiObject) {
               $id =  printf("%u",crc32((string) $apiObject->$idKey));
               //$helper->log( $id );
               //$id = (string) $apiObject->$idKey;
               $resultingArray[$id] = array();
               foreach ($apiObject as $apiKey => $value) {
                   if (!isset($map[$apiKey])) {
                       continue;
                   }
                   $resultingArray[$id][$map[$apiKey]] = addcslashes((string)$value, "\000\n\r\\'\"\032");
                   $resultingArray[$id]['id'] = $id;
               }
           }
           $helper->log( $resultingArray );
           return $resultingArray;
       }

*/

       /**
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    protected function _getRead()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * @param array $warehouses
     * @return bool
     * @throws Exception
     */
    protected function _importWarehouses(array $warehouses)
    {
        if (empty($warehouses)) {
            Mage::helper('novaposhta')->log('No warehouses received');
            throw new Exception('No warehouses received');
        }

        $warehouses = $this->_applyMap($warehouses, $this->_dataMapWarehouse);
        $existingWarehouses = $this->_getExistingWarehouses();
        $warehousesToDelete = array_diff(array_keys($existingWarehouses), array_keys($warehouses));

        $tableName  = Mage::getSingleton('core/resource')->getTableName('novaposhta_warehouse');

        ######################
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('novaposhta_city');
        $ref = $readConnection->fetchCol('SELECT ref FROM '.$table);
       // Mage::helper('novaposhta')->log($ref);

        #######################


        $connection = $this->_getConnection();


        if (count($warehousesToDelete) > 0) {
            $connection->delete($tableName, $warehousesToDelete);
            Mage::helper('novaposhta')->log(sprintf("Warehouses deleted: %s", implode(',', $warehousesToDelete)));
        }

        $connection->beginTransaction();
        try {
            ##

          //  $foreignKeys = $read-> getForeignKeys($tableName2);
          //  Mage::helper('novaposhta')->log( $foreignKeys);
            ##
            foreach ($warehouses as $data) {
                if (in_array($data['city_id'], $ref)) {
                    //      Mage::helper('novaposhta')->log($data);
                    $connection->insertOnDuplicate($tableName, $data);
                }
            }

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * @return array
     */
    protected function _getExistingWarehouses()
    {
        if (!$this->_existingWarehouses) {
            /** @var Ak_NovaPoshta_Model_Resource_Warehouse_Collection $collection */
            $collection = Mage::getResourceModel('novaposhta/warehouse_collection');
            $this->_existingWarehouses = $collection->getAllIds();
        }
        return $this->_existingWarehouses;
    }
}