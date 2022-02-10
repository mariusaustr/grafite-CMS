<?php

namespace Tests;

use Illuminate\Database\Eloquent\Factories\Factory;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('cms.load-modules', false);
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('minify.config.ignore_environments', ['local', 'testing']);
        $app->make('Illuminate\Contracts\Http\Kernel')->pushMiddleware('Illuminate\Session\Middleware\StartSession');

        $app['Illuminate\Contracts\Auth\Access\Gate']->define('cms', function ($user) {
            return true;
        });
    }

    /**
     * getPackageProviders.
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Grafite\Cms\GrafiteCmsProvider::class,
            \Collective\Html\HtmlServiceProvider::class,
            \Grafite\Forms\FormsProvider::class,
        ];
    }

    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Factory::useNamespace('Grafite\Database\Factories\\');

        $this->artisan('vendor:publish', [
            '--provider' => 'Grafite\Cms\GrafiteCmsProvider',
            '--force' => true,
        ]);
        $this->artisan('migrate', [
            '--database' => 'testbench',
        ]);
        $this->withoutMiddleware();
        $this->withoutEvents();
    }
}
