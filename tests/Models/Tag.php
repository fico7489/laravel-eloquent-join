<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models;

class Tag extends BaseModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function taggable()
    {
    	return $this->morphTo();
    }
}
