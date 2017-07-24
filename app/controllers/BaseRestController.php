<?php

namespace EComConnBroker\Controllers;

class BaseRestController extends \Phalcon\DI\Injectable
{
    /**
     * RestController constructor.
     */
    public function __construct()
    {
        // Load default di
        $this->setDI(\Phalcon\DI::getDefault());
    }
}
