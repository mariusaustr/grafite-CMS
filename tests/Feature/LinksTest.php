<?php

namespace Tests\Feature;

use Grafite\Cms\Models\Link;
use Grafite\Cms\Models\Menu;
use Tests\TestCase;

class LinksTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        
        Menu::factory()->create();
        Link::factory()->create();
        Link::factory()->make(['id' => 1]);
    }

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    public function testCreate()
    {
        $response = $this->get('/cms/links/create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEdit()
    {
        $response = $this->get('/cms/links/1/edit');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('links');
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function testStore()
    {
        $link = Link::factory()->make(['id' => 89]);
        $response = $this->post('/cms/links', $link->toArray());

        $this->assertEquals(302, $response->getStatusCode());
        $response->assertRedirect('/cms/menus/1/edit');
    }

    public function testUpdate()
    {
        $response = $this->patch('/cms/links/1', [
            'name' => 'wtf',
        ]);

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testDelete()
    {
        $response = $this->delete('/cms/links/1');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
