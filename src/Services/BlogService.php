<?php

namespace Grafite\Cms\Services;

class BlogService extends BaseService
{
    /**
     * Get templates as options.
     *
     * @return array
     */
    public function getTemplatesAsOptions()
    {
        return $this->getTemplatesAsOptionsArray('blog');
    }
}
