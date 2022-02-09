<?php

namespace Grafite\Cms\Console;

use Illuminate\Console\Command;

class ModuleCrud extends Command
{
    public $table;
    public $filesystem;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'module:crud {table} {--migration} {--schema=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a CRUD module for Cms [DEPRECATED]';

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
