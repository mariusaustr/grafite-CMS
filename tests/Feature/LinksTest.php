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
        $this->withoutMiddleware();
        $this->withoutEvents();
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
        $response = $this->call('GET', '/cms/links/create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEdit()
    {
        $response = $this->call('GET', '/cms/links/1/edit');
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
        $response = $this->call('POST', '/cms/links', $link->toArray());

        $this->assertEquals(302, $response->getStatusCode());
        $response->assertRedirect('/cms/menus/1/edit');
    }

    public function testUpdate()
    {
        $response = $this->call('PATCH', '/cms/links/1', [
            'name' => 'wtf',
        ]);

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testDelete()
    {
        $response = $this->call('DELETE', '/cms/links/1');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
