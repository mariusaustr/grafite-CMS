<?php

namespace Tests\Feature;

use Grafite\Cms\Models\Image;
use Tests\TestCase;

class ImagesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->withoutEvents();
        Image::factory()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    public function testIndex()
    {
        $response = $this->get('cms/images');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('images');
    }

    public function testCreate()
    {
        $response = $this->get('cms/images/create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEdit()
    {
        $response = $this->get('cms/images/1/edit');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('images');
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function testStore()
    {
        $uploadedFile = new \Symfony\Component\HttpFoundation\File\UploadedFile(__DIR__.'/../fixtures/test-pic.jpg', 'test-pic.jpg');
        $image = (array) Image::factory()->make(['id' => 2]);
        $image['location'] = [
            [
                'name' => \CryptoService::encrypt('test-pic.jpg'),
                'original' => 'what.jpg',
            ],
            [
                'name' => \CryptoService::encrypt('test-pic.jpg'),
                'original' => 'what.jpg',
            ],
        ];
        $response = $this->post('cms/images', ['location' => $image['location']], [], []);

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testUpdate()
    {
        $image = (array) Image::factory()->make(['id' => 3, 'title' => 'dumber']);
        $response = $this->patch('cms/images/3', $image);

        $this->assertEquals(302, $response->getStatusCode());
        $response->assertRedirect('/cms/images');
    }

    public function testDelete()
    {
        $uploadedFile = new \Symfony\Component\HttpFoundation\File\UploadedFile(__DIR__.'/../fixtures/test-pic.jpg', 'test-pic.jpg');
        $image = (array) Image::factory()->make(['id' => 2]);
        $image['location'] = [
            [
                'name' => \CryptoService::encrypt('files/dumb'),
                'original' => 'what.jpg',
            ],
            [
                'name' => \CryptoService::encrypt('files/dumb'),
                'original' => 'what.jpg',
            ],
        ];
        $this->post('cms/images', $image, [], ['location' => ['image' => $uploadedFile]]);

        $response = $this->delete('cms/images/2');
        $this->assertEquals(302, $response->getStatusCode());
        $response->assertRedirect('cms/images');
    }
}
