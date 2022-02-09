<?php

namespace Grafite\Cms\Repositories;

use Grafite\Cms\Models\CmsModel;
use Grafite\Cms\Models\Menu;

class MenuRepository extends CmsRepository
{
    public $table;

    public function __construct(public Menu $model)
    {
        $this->table = config('cms.db-prefix').'menus';
    }

    /**
     * Stores Menu into database.
     */
    public function store(array $payload): Menu
    {
        $payload['name'] = htmlentities($payload['name']);

        return $this->model->create($payload);
    }

    /**
     * Updates Menu into database.
     */
    public function update(CmsModel $menu, array $payload): Menu|bool
    {
        $payload['name'] = htmlentities($payload['name']);

        return $menu->update($payload);
    }

    /**
     * Set the order.
     */
    public function setOrder(Menu $menu, array $payload): bool
    {
        return $menu->update($payload);
    }
}
