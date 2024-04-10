<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | Landing
    |--------------------------------------------------------------------------
    */

    public function testIndex()
    {
        $response = $this->get('/cms/dashboard');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
