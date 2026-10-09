<?php

namespace Modules\Community\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasUlids;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'subject',
        'message',
        'locale',
        'status',
        'read_at',
        'reply_message',
        'replied_at',
        'replied_by',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
    ];

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
