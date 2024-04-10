<?php

namespace Tests\Feature;

use Grafite\Cms\Models\Page;
use Tests\TestCase;

class PagesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        
        Page::factory()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    public function testIndex()
    {
        $response = $this->get('cms/pages');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('pages');
    }

    public function testCreate()
    {
        $response = $this->get('cms/pages/create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEdit()
    {
        $response = $this->get('cms/pages/1/edit');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('page');
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function testStore()
    {
        $page = Page::factory()->make(['id' => 2]);
        $page = $page->toArray();
        unset($page['translations']);
        $response = $this->post('cms/pages', $page);

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testSearch()
    {
        $response = $this->post('cms/pages/search', ['term' => 'wtf']);

        $response->assertViewHas('pages');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdate()
    {
        $page = ['id' => 2, 'title' => 'dumber', 'url' => 'dumber', 'entry' => 'okie dokie'];
        $response = $this->post('cms/pages', $page);

        $response = $this->patch('cms/pages/2', [
            'title' => 'smarter',
            'url' => 'smart',
            'blocks' => null,
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('pages', ['title' => 'smarter']);
    }

    public function testUpdateTranslation()
    {
        $page = ['id' => 2, 'title' => 'dumber', 'url' => 'dumber', 'entry' => 'okie dokie'];
        $response = $this->post('cms/pages', $page);

        $response = $this->patch('cms/pages/2', [
            'title' => 'smarter',
            'url' => 'smart',
            'lang' => 'fr',
            'blocks' => null,
        ]);

        $this->assertDatabaseHas('translations', [
            'entity_type' => 'Grafite\\Cms\\Models\\Page',
        ]);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testDelete()
    {
        $response = $this->delete('cms/pages/1');
        $this->assertEquals(302, $response->getStatusCode());
        $response->assertRedirect('cms/pages');
    }
}
