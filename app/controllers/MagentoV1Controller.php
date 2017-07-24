<?php

namespace EComConnBroker\Controllers;

use EComConnBroker\Exceptions\ApiException;

class MagentoV1Controller extends BaseRestController
{
    /**
     * Magento soap api client instance.
     *
     * @var \SoapClient|null
     */
    private $apiClient;

    /**
     * Magento soap api session id.
     *
     * @var string
     */
    private $sessionId;

    /**
     * MagentoV1Controller constructor.
     */
    public function __construct()
    {
        // Construct base class
        parent::__construct();

        // Pre-auth user first
        $this->apiLogin();
    }

    /**
     * Magento sales_order_shipment.create implementation
     * http://devdocs.magento.com/guides/m1x/api/soap/sales/salesOrderShipment/sales_order_shipment.create.html
     *
     * @return mixed
     * @throws ApiException
     */
    public function sales_order_shipment_create()
    {
        // Load request body
        $requestBody = file_get_contents('php://input');
        if (empty($requestBody))
            throw new ApiException('Request body appears to be empty.', 400);

        // Decode request object
        $requestBody = json_decode($requestBody, true);
        if ($requestBody === null)
            throw new ApiException('Could not json decode request body.', 400);

        // Validate orderIncrementId
        if (empty($requestBody['orderIncrementId']))
            throw new ApiException('The orderIncrementId field not found on json request body.', 400);

        // Validate itemsQty
        if (empty($requestBody['itemsQty']))
            throw new ApiException('The itemsQty field not found on json request body.', 400);
        if (!sizeof($requestBody['itemsQty']))
            throw new ApiException('The itemsQty field is empty on json request body.', 400);

        // Create shipment
        $shipmentIncrementId = $this->apiClient->call(
            $this->sessionId,
            'order_shipment.create',
            $requestBody['orderIncrementId'],
            $requestBody['itemsQty']
        );

        // Finished
        return $this->di->get('json_response')
            ->sendSuccess([
                'message' => 'Shipment Created',
                'shipmentIncrementId' => $shipmentIncrementId
            ], 201);
    }

    /**
     * Helper method to login initiate soap api client & authenticate.
     */
    private function apiLogin()
    {
        // Load request
        $req = $this->di->get('request');

        // Parse login credentials
        $apiUrl = $req->getHeader('X-MAGENTO-API-URL');
        $apiUser = $req->getHeader('X-MAGENTO-API-USER');
        $apiPass = $req->getHeader('X-MAGENTO-API-PASS');

        // Check if required header fields are present on the request
        if (empty($apiUrl)) throw new ApiException('The X-MAGENTO-API-URL field is empty in request header.');
        if (empty($apiUser)) throw new ApiException('The X-MAGENTO-API-USER field is empty in request header.');
        if (empty($apiPass)) throw new ApiException('The X-MAGENTO-API-PASS field is empty in request header.');

        // Create a new soap api client
        $this->apiClient = new \SoapClient($apiUrl);

        // Login to magento api
        $this->sessionId = $this->apiClient->login($apiUser, $apiPass);
    }

    /**
     * MagentoV1Controller destructor.
     */
    function __destruct()
    {
        // If logged in, logout
        if ($this->apiClient && !empty($this->sessionId))
            $this->apiClient->endSession($this->sessionId);
    }
}
