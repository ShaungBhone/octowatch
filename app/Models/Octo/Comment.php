<?php

namespace App\Models\Octo;

use App\Events\CommentCreated;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string|null $octo_id
 * @property int $repository_id
 * @property string $body
 * @property string|null $author_login
 * @property string|null $author_avatar_url
 * @property string|null $html_url
 * @property \Illuminate\Support\Carbon|null $created_at_github
 * @property \Illuminate\Support\Carbon|null $updated_at_github
 * @property string $type
 * @property-read \App\Models\Octo\Repository $repository
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereAuthorAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereAuthorLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereCreatedAtGithub($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereHtmlUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereRepositoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment whereUpdatedAtGithub($value)
 * @mixin \Eloquent
 */
class Comment extends Model
{
    use HasFactory;

    protected $table = 'octo_comments';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'created_at_github' => 'datetime',
        'updated_at_github' => 'datetime',
    ];

    protected $dispatchesEvents = [
        'created' => CommentCreated::class,
    ];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }
}
