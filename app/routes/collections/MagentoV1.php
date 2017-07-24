<?php

/**
 * Construct a route collection.
 */
return call_user_func(function()
{
    // Initialise a new route collection
    $magentoV1Routes = new \Phalcon\Mvc\Micro\Collection();

    // Configure route collection
    $magentoV1Routes
        ->setPrefix('/MagentoV1')
        ->setHandler('\EComConnBroker\Controllers\MagentoV1Controller')
        ->setLazy(true);

    // Configure routes
    $magentoV1Routes->post('/sales_order_shipment.create', 'sales_order_shipment_create');

    // Finished
    return $magentoV1Routes;
});
