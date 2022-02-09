<?php

namespace Grafite\Cms\Controllers;

use Grafite\Cms\Services\CmsService;
use Illuminate\Http\Response;

class SiteMapController extends GrafiteCmsController
{
    public function __construct(protected CmsService $service)
    {
        parent::construct();
    }

    public function index(): Response
    {
        $items = $this->service->collectSiteMapItems();

        $contents = view('cms::site-map', compact('items'));

        return new Response($contents, 200, [
            'Content-Type' => 'application/xml;charset=UTF-8',
        ]);
    }
}
