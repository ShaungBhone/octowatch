<?php

declare(strict_types=1);

namespace App\Models\Octo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Comment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'octo_comments';

    protected $casts = [
        'created_at_github' => 'datetime',
        'updated_at_github' => 'datetime',
    ];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }
}
