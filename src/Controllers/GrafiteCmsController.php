<?php

namespace Grafite\Cms\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class GrafiteCmsController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $routeBase;

    protected $repository;

    public function construct()
    {
        $this->routeBase = config('cms.backend-route-prefix', 'cms');
    }
}
