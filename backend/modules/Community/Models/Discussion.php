<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Models\User;

class Discussion extends Model
{
    use HasUlids;

    protected $fillable = ['discussable_type', 'discussable_id', 'created_by', 'title'];

    public function discussable(): MorphTo
    {
        return $this->morphTo();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(DiscussionMessage::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
