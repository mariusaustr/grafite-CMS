<?php

namespace Grafite\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CmsModel extends Model
{
    /**
     * Model contructuor.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (! empty(config('cms.db-prefix', ''))) {
            $this->table = config('cms.db-prefix', '').$this->table;
        }
    }

    /**
     * Get a model as a translatable object.
     */
    public function asObject(): static
    {
        if (! is_null(request('lang')) && request('lang') !== config('cms.default-language', 'en')) {
            return $this->translationData(request('lang'));
        }

        return $this;
    }

    /**
     * After the item is saved to the database.
     */
    public function afterSaved(self $payload): void
    {
        if (! request()->is('cms/revert/*') && ! request()->is('cms/rollback/*/*')) {
            unset($payload->attributes['created_at']);
            unset($payload->attributes['updated_at']);
            unset($payload->original['created_at']);
            unset($payload->original['updated_at']);

            if ($payload->attributes != $payload->original) {
                Archive::create([
                    'token' => md5(time()),
                    'entity_id' => $payload->attributes['id'],
                    'entity_type' => get_class($payload),
                    'entity_data' => json_encode($payload->attributes),
                ]);
                Log::info(get_class($payload).' #'.$payload->attributes['id'].' was archived');
            }
        }
    }

    /**
     * When the item is being deleted.
     */
    public function beingDeleted(self $payload): void
    {
        $type = get_class($payload);
        $id = $payload->id;

        Translation::where('entity_id', $id)->where('entity_type', $type)->delete();
        Archive::where('entity_id', $id)->where('entity_type', $type)->delete();

        Archive::where('entity_type', 'Grafite\Cms\Models\Translation')
            ->where('entity_data', 'LIKE', '%"entity_id":'.$id.'%')
            ->where('entity_data', 'LIKE', '%"entity_type":"'.$type.'"%')
            ->delete();

        if ($type == 'Grafite\Cms\Models\Page') {
            Link::where('page_id', $id)->delete();
        }
    }

    /**
     * A method for getting / setting blocks.
     */
    public function block(string $slug): string
    {
        $block = $this->findABlock($slug);

        if ($block) {
            return $block;
        }

        $this->update([
            'blocks' => json_encode(array_merge($this->blocks, [$slug => ''])),
        ]);

        return '';
    }

    /**
     * Find a block based on slug.
     */
    public function findABlock(string $slug): ?string
    {
        if (isset($this->blocks[$slug])) {
            return $this->blocks[$slug];
        }

        return null;
    }
}
