<?php

namespace Tests\Feature;

use Grafite\Cms\Models\Menu;
use Tests\TestCase;

class MenuTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->withoutEvents();
        Menu::factory()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    public function testIndex()
    {
        $response = $this->get('/cms/menus');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('menus');
    }

    public function testCreate()
    {
        $response = $this->get('/cms/menus/create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEdit()
    {
        $response = $this->get('/cms/menus/1/edit');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('menu');
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function testStore()
    {
        $menu = Menu::factory()->make(['id' => 2]);
        $response = $this->post('/cms/menus', $menu->toArray());

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testSearch()
    {
        $response = $this->post('cms/menus/search', ['term' => 'wtf']);

        $response->assertViewHas('menus');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdate()
    {
        $response = $this->patch('/cms/menus/1', [
            'name' => 'awesome',
        ]);

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testDelete()
    {
        $response = $this->delete('/cms/menus/1');
        $this->assertEquals(302, $response->getStatusCode());
        $response->assertRedirect('/cms/menus');
    }
}
