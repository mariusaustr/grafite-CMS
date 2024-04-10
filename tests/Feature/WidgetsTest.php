<?php

namespace Tests\Feature;

use Grafite\Cms\Models\Widget;
use Tests\TestCase;

class WidgetsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        
        Widget::factory()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    public function testIndex()
    {
        $response = $this->get('cms/widgets');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('widgets');
    }

    public function testCreate()
    {
        $response = $this->get('cms/widgets/create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEdit()
    {
        $response = $this->get('cms/widgets/1/edit');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('widget');
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function testStore()
    {
        $widget = Widget::factory()->make(['id' => 2]);
        $widget = $widget->toArray();
        unset($widget['translations']);
        $response = $this->post('cms/widgets', $widget);

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testUpdate()
    {
        $widget = ['id' => 2, 'name' => 'dumber', 'slug' => 'dumber'];
        $response = $this->post('cms/widgets', $widget);

        $response = $this->patch('cms/widgets/2', [
            'name' => 'whacky',
            'slug' => 'whacky',
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertDatabaseHas('widgets', ['name' => 'whacky']);
    }

    public function testUpdateTranslation()
    {
        $widget = ['id' => 2, 'name' => 'dumber', 'slug' => 'dumber'];
        $response = $this->post('cms/widgets', $widget);

        $response = $this->patch('cms/widgets/2', [
            'name' => 'whacky',
            'slug' => 'whacky',
            'lang' => 'fr',
        ]);

        $this->assertDatabaseHas('translations', [
            'entity_type' => 'Grafite\\Cms\\Models\\Widget',
        ]);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testDelete()
    {
        $response = $this->delete('cms/widgets/1');
        $this->assertEquals(302, $response->getStatusCode());
        $response->assertRedirect('cms/widgets');
    }
}
