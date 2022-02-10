<?php

namespace Tests\Feature;

use Grafite\Cms\Models\File;
use Tests\TestCase;

class FilesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->withoutEvents();
        File::factory()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    */

    public function testIndex()
    {
        $response = $this->get('cms/files');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('files');
    }

    public function testCreate()
    {
        $response = $this->get('cms/files/create');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEdit()
    {
        $response = $this->get('cms/files/1/edit');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertViewHas('files');
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function testStore()
    {
        $uploadedFile = new \Symfony\Component\HttpFoundation\File\UploadedFile(__DIR__.'/../fixtures/test-file.txt', 'test-file.txt');
        $file = File::factory()->make([
            'id' => 2,
            'location' => [
                'file_a' => [
                    'name' => \CryptoService::encrypt('test-file.txt'),
                    'original' => 'test-file.txt',
                    'mime' => 'txt',
                    'size' => 24,
                ],
            ],
        ]);
        $response = $this->post('cms/files', $file->getAttributes());
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testSearch()
    {
        $response = $this->post('cms/files/search', ['term' => 'wtf']);

        $response->assertViewHas('files');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdate()
    {
        $file = (array) File::factory()->make(['id' => 3, 'title' => 'dumber']);
        $response = $this->patch('cms/files/3', $file);

        $this->assertEquals(302, $response->getStatusCode());
        $response->assertRedirect('/cms/files');
    }

    public function testDelete()
    {
        \Storage::put('test-file.txt', 'what is this');
        $file = File::factory()->make([
            'id' => 2,
            'location' => [
                'file_a' => [
                    'name' => \CryptoService::encrypt('test-file.txt'),
                    'original' => 'test-file.txt',
                    'mime' => 'txt',
                    'size' => 24,
                ],
            ],
        ]);
        $this->post('cms/files', $file->getAttributes());

        $response = $this->delete('cms/files/2');
        $this->assertEquals(302, $response->getStatusCode());
        $response->assertRedirect('cms/files');
    }
}
