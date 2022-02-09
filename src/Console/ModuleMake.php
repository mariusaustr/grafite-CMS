<?php

namespace Grafite\Cms\Console;

use Illuminate\Console\Command;

class ModuleMake extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'module:make {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a module for Cms [DEPRECATED]';

    /**
     * Generate a CRUD stack.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->error('Functionality is deprecated');
    }
}
