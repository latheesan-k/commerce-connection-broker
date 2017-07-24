<?php

namespace EComConnBroker\Controllers;

class MagentoV1Controller extends RestController
{
    /**
     * Todo
     *
     * @return mixed
     */
    public function index()
    {
        // Finished
        return $this->di->get('json_response')
            ->sendSuccess('Test');
    }
}
