<?php

namespace App\Models\Octo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Issues extends Model
{
    use HasFactory;
    
    protected $table = 'octo_issues';

    protected $guarded = [];

    protected $casts = [
        'labels' => 'array',
    ];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(
            related: Repository::class,
            foreignKey: 'octo_repository_id'
        );
    }

    public function comments(): HasMany
    {
        return $this->hasMany(
            related: Comment::class,
            foreignKey: 'repository_id',
            localKey: 'octo_repository_id'
        )
            ->where('type', 'issue');
    }
}
