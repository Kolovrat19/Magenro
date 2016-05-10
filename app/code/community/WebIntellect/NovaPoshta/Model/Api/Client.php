<?php
class WebIntellect_NovaPoshta_Model_Api_Client
{
    protected $_httpClient;

    const DELIVERY_TYPE_APARTMENT_APARTMENT = 1;
    const DELIVERY_TYPE_APARTMENT_WAREHOUSE = 2;
    const DELIVERY_TYPE_WAREHOUSE_APARTMENT = 3;
    const DELIVERY_TYPE_WAREHOUSE_WAREHOUSE = 'DoorsDoors';

    const LOAD_TYPE_STANDARD   = 1;
    const LOAD_TYPE_SECURITIES = 4;

    /**
     * @return string
     */
    protected function _getApiUri()
    {
        return Mage::helper('novaposhta')->getStoreConfig('api_url');
    }

    /**
     * @return string
     */
    protected function _getApiKey()
    {
        return Mage::helper('novaposhta')->getStoreConfig('api_key');
    }

    /**
     * @return Zend_Http_Client
     */
    protected function _getHttpClient()
    {
        if (!$this->_httpClient) {
            $this->_httpClient = new Zend_Http_Client($this->_getApiUri());
        }

        return $this->_httpClient;
    }

    /**
     * @param array $array
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     */ //Строим Xml для запроса
    protected function _buildXml(array $array, SimpleXMLElement $element = null)
    {
        if (is_null($element)) {
            $element = new SimpleXMLElement('<file/>');
        }

        foreach ($array as $key => $value) {
            if (!is_numeric($key)) {
                if (is_array($value)) {
                    $this->_buildXml($value, $element->addChild($key));
                } else {
                    $element->addChild($key, $value);
                }
            }
        }

        return $element;
    }

    /**
     * @param array $data
     * @return SimpleXMLElement
     */ //
    protected function _makeRequest(array $data)
    {
        /** @var Ak_NovaPoshta_Helper_Data $helper */
        $helper    = Mage::helper('novaposhta');
        $xmlString = $this->_buildXml($data)->asXML();

       // $helper->log('Request XML:' . $xmlString);

        /** @var Zend_Http_Response $response */
        $response = $this->_getHttpClient()
            ->resetParameters(true)
            ->setRawData($xmlString)
            ->request(Zend_Http_Client::POST);

        //$response = $this->addAttribute('type', 'documentary');
        //$helper->log('Response status code:' . $response->getStatus());

        //$helper->log('Response body:' . $response->getBody());

        //$helper->log(print_r((array) new SimpleXMLElement($response->getBody()), true));

        if (200 != $response->getStatus()) {
            Mage::throwException('Server error, response status:' . $response->getStatus());
        }

        return new SimpleXMLElement($response->getBody());


    }

    /**
     * @return SimpleXMLElement
     */
    public function getCityWarehouses()
    {


        $responseXml = $this->_makeRequest(array(
            'apiKey' => $this->_getApiKey(),
            'modelName' => 'Address',
            'calledMethod' => 'getCities',
            'methodProperties' => array(
                "Page" => "1"

            )
        ));

      //  $helper->log( $responseXml->xpath('data/item'));
        return $responseXml->xpath('data/item');

    }


    /**
     * @return SimpleXMLElement
     */
    public function getWarehouses()
    {

        $responseXml = $this->_makeRequest(array(
            'modelName' => 'Address',
            'calledMethod' => 'getWarehouses',
            'methodProperties' =>  new SimpleXMLElement('<ref/>'),
            'apiKey' => $this->_getApiKey(),
        ));

     //   $helper    = Mage::helper('novaposhta');
     //   $helper    = Mage::helper('novaposhta');
     //  $helper->log(  $responseXml->xpath('data/item'));

        return $responseXml->xpath('data/item');

    }

    public function getExInvoice()
    {

        $responseXml = $this->_makeRequest(array(
             'modelName' => 'InternetDocument',
             'calledMethod' => 'getDocumentPrice',
             'methodProperties' => array (
                    'CitySender' => '8d5a980d-391c-11dd-90d9-001a92567626',
                    'CityRecipient' => 'db5c88f0-391c-11dd-90d9-001a92567626',
                    'Weight' => '100',
                    'Cost' => '200',
                    'ServiceType' => 'DoorsDoors' ),
           /*
            'apiKey' => $this->_getApiKey(),
            'modelName' => 'InternetDocument',
            'calledMethod' => 'save',
            'methodProperties' => array(
                'PayerType' => 'Sender',
                'PaymentMethod' => 'Cash',
                'DateTime' => '12.05.2014',
                'CargoType' => 'Cargo',
                'VolumeGeneral' => '10',
                'Weight' => '10',
                'ServiceType' => 'WarehouseDoors',
                'SeatsAmount' => '1',
                'Description' => 'абажур',
                'Cost' => '500',
                'CitySender' => 'db5c88f5-391c-11dd-90d9-001a92567626',
                'Sender' => '4d87603b-c3bc-11e3-9fa0-0050568002cf',
                'SenderAddress' => 'c9f591a1-a91a-11e3-9fa0-0050568002cf',
                'ContactSender' => 'b2dd60db-c3a8-11e3-9fa0-0050568002cf',
                'SendersPhone' => '0937640250',
                'CityRecipient' => 'd8ff4ee6-981a-11e1-9e32-0026b97ed48a',
                'Recipient' => '10cf99b4-d4f0-11e3-95eb-0050568046cd',
                'RecipientAddress' => '400d602d-d4f4-11e3-95eb-0050568046cd',
                'ContactRecipient' => '045246fc-d4f5-11e3-95eb-0050568046cd',
                'RecipientsPhone' => '0663456655',*/


        ));
      // $helper    = Mage::helper('novaposhta');
      // $helper->log($responseXml->xpath('data/item'));

        return $responseXml->xpath('data/item');
    }



    public function getShippingCost(
        Zend_Date $deliveryDate,
        WebIntellect_NovaPoshta_Model_City $senderCity, WebIntellect_NovaPoshta_Model_City $recipientCity,
        $packageWeight, $publicPrice,
        $deliveryType = 'WarehouseWarehouse')
    {

        $response = $this->_makeRequest(array(
            'modelName' => 'InternetDocument',
            'calledMethod' => 'getDocumentPrice',
            'methodProperties' => array(

                'CitySender' => $senderCity->getData('ref'),
                'CityRecipient' => $recipientCity->getData('ref'),
                'Weight' => $packageWeight,
                'Cost' => $publicPrice,

                'ServiceType' => 'WarehouseWarehouse',


            )
        ));


        if (1 == (int) $response->error) {
            Mage::throwException('Novaposhta Api error');
        }

        $arr = array (
            'delivery_date' => (string) $response->date,
            'cost' => (float) $response->data->item->Cost[0],
        );
     //   Mage::helper('novaposhta')->log($arr);
        return $arr;
    }
}
