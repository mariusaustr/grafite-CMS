<?php

namespace Tests\Feature;

use Grafite\Cms\Models\Blog;
use Grafite\Cms\Services\CryptoService;
use Tests\TestCase;

class BlogTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->withoutEvents();
        Blog::factory()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    public function testIndex()
    {
        $response = $this->get('cms/blog');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('blogs');
    }

    public function testCreate()
    {
        $response = $this->get('cms/blog/create');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertSee('Title');
    }

    public function testEdit()
    {
        Blog::factory()->create(['id' => 4]);
        $response = $this->get('cms/blog/4/edit');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('blog');
        $response->assertSee('Title');
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function testStore()
    {
        $blog = ['title' => 'dumber', 'url' => 'dumber', 'entry' => 'okie dokie'];
        $response = $this->post('cms/blog', $blog);

        $this->assertDatabaseHas('blogs', ['id' => 2]);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testSearch()
    {
        $response = $this->post('cms/blog/search', ['term' => 'wtf']);

        $response->assertViewHas('blogs');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdate()
    {
        $blog = ['title' => 'dumber', 'url' => 'dumber', 'entry' => 'okie dokie'];
        $this->post('cms/blog', $blog);

        $response = $this->patch('cms/blog/1', [
            'title' => 'dumber and dumber',
            'url' => 'dumber-and-dumber',
        ]);

        $this->assertDatabaseHas('blogs', ['title' => 'dumber and dumber']);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testUpdateTranslation()
    {
        $blog = ['title' => 'dumber', 'url' => 'dumber', 'entry' => 'okie dokie'];
        $this->post('cms/blog', $blog);

        $response = $this->patch('cms/blog/1', [
            'title' => 'dumber and dumber',
            'url' => 'dumber-and-dumber',
            'lang' => 'fr',
        ]);

        $this->assertDatabaseHas('translations', [
            'entity_type' => 'Grafite\\Cms\\Models\\Blog',
        ]);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testDelete()
    {
        $response = $this->delete('cms/blog/'.app(CryptoService::class)->encrypt(1));
        $this->assertEquals(302, $response->getStatusCode());
        $response->assertRedirect('cms/blog');
    }
}
