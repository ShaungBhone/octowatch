<?php

namespace App\Models\Octo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repository extends Model
{
    use HasFactory;
    
    protected $table = 'octo_repositories';

    protected function casts(): array
    {
        return [
            'private' => 'boolean',
            'updated_at_github' => 'datetime',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(Connection::class, 'octo_connection_id');
    }
}
