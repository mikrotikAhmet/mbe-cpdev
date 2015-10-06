<?php
/**
 *  use with a "setOptions(array)"
 *
 *  array (
 *      "apikey" => ...,
 *      "version" => ...,
 *      "gateway" => ..., // gateway should include the session reference
 *  )
 *
 *
 */

class Vbw_Procurement_Link_Client {


    /**
     * @var url
     */
    protected $_gateway = null;

    /**
     * @var string
     */
    protected $_apikey = null;

    /*
     * @var decimal
     */
    protected $_version = null;

    /**
     * @var  mixed
     */
    protected $_instructions = null;

    /**
     * @var Zend_Http_Client
     */
    protected $_httpClient = null;

    /**
     *
     * @var Zend_Http_Client_Response
     */
    protected $_httpResponse = null;

    protected $_response = null;

    public function __construct ($options = array())
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     *
     * @param array $options
     */
    public function setOptions($options = array())
    {
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $method_name = "set". $key;
                if (method_exists($this, $method_name)) {
                    $this->{$method_name}($value);
                }
            }
        }
    }

    /**
     * 
     * @param mixed array|string to send with payload.
     * @return mixed (data or null on error)
     */
    public function request($data = null)
    {
        
        $client = $this->getHttpClient(true);

        $client->setUri($this->getGateway());

        $payload = array (
            "apikey" => $this->getApikey(),
            "version" => $this->getVersion(),
            "params" => $data
        );

        $client->setRawData(Zend_Json::encode($payload));

        $response = $client->request($client::POST);

        if ($response->getStatus() == "200") {
            if ($response->getHeader('content-type') == "application/json") {
                $this->setResponse(Zend_Json::decode($response->getBody()));
                return $this->getResults();
            } else {
                $this->setResponse(array("errors"=>"Invalid response.","results"=>$response->getBody()));
            }
        } else {
            $this->setResponse(array("errors"=>"Type {$response->getStatus()} Error : ". htmlentities(strip_tags($response->getBody()))));
        }
        return null;
    }

    /**
     * @param array $data {"errors"=>"","results"=>""}
     */
    public function setResponse ($data = array())
    {
        $this->_response = $data;
    }

    public function getResponse ()
    {
        return $this->_response;
    }

    /**
     * test for error from results.
     *
     * @return bool
     */
    public function hasError ()
    {
        if (isset($this->_response['errors'])
                && !empty($this->_response['errors'])) {
            return true;
        }
        return false;
    }

    /**
     * get error from results.
     *
     * @return string
     */
    public function getError ()
    {
        if ($this->hasError()) {
            return $this->_response['errors'];
        }
        return null;
    }

    /**
     * return result data.
     *
     * @return mixed
     */
    public function getResults ()
    {
        if (isset($this->_response['results'])) {
            return $this->_response['results'];
        }
        return null;
    }

    /**
     *
     * @param bool $new if you want to reset the http client
     * @return Zend_Http_Client
     */
    public function getHttpClient($new = false)
    {
        if ($this->_httpClient == null
                || $new == true) {
            $this->_httpClient = new Zend_Http_Client();
        }
        return $this->_httpClient;
    }

    public function setGateway ($gateway)
    {
        $this->_gateway = $gateway;
    }

    public function getGateway ()
    {
        return $this->_gateway;
    }

    public function setApikey ($apikey)
    {
        $this->_apikey = $apikey;
    }

    public function getApikey ()
    {
        return $this->_apikey;
    }

    public function setVersion ($version)
    {
        $this->_version = $version;
    }

    public function getVersion ()
    {
        return $this->_version;
    }

    public function setInstructions($instructions)
    {
        $this->_instructions = $instructions;
    }

    public function getInstructions()
    {
        return $this->_instructions;
    }

}