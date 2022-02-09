<?php

namespace Grafite\Cms\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ApiController extends GrafiteCmsController
{
    protected $model;

    protected $routeName;

    public function __construct(Request $request)
    {
        parent::construct();

        $url = $request->segment(3) ?? 'page';

        $this->modelName = Str::singular($url);

        if (! empty($this->modelName)) {
            $this->model = app('Grafite\Cms\Models\\'.ucfirst($this->modelName));
        }
    }

    /**
     * Find an item in the API.
     */
    public function find(string $id): JsonResponse
    {
        return response()->json(
            $this->model->find($id)
        );
    }

    /**
     * Collect all items of a resource.
     */
    public function all(): JsonResponse
    {
        $query = $this->model;

        if (Schema::hasColumn(Str::plural($this->modelName), 'is_published')) {
            $query = $query->where('is_published', true);
        }

        if (Schema::hasColumn(Str::plural($this->modelName), 'published_at')) {
            $query = $query->where('published_at', '<=', Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s'));
        }

        if (Schema::hasColumn(Str::plural($this->modelName), 'finished_at')) {
            $query = $query->where('finished_at', '>=', Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s'));
        }

        return response()->json($query
            ->orderBy('created_at', 'desc')
            ->paginate(Config::get('cms.pagination', 24))
        );
    }
}
