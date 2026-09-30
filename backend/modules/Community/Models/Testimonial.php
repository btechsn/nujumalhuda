<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasUlids;

    protected $fillable = ['user_id', 'author_name', 'relation', 'content_i18n', 'status'];

    protected $casts = ['content_i18n' => 'array'];
}
