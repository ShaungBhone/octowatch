<?php

namespace App\Models\Octo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $octo_connection_id
 * @property int $octo_repository_id
 * @property int $issue_id
 * @property int $number
 * @property string $title
 * @property string|null $body
 * @property string $state
 * @property string|null $author_login
 * @property string|null $author_avatar_url
 * @property array<array-key, mixed>|null $labels
 * @property string|null $assignees
 * @property-read int|null $comments_count
 * @property string|null $created_at_github
 * @property string|null $updated_at_github
 * @property string|null $closed_at_github
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Octo\Comment> $comments
 * @property-read \App\Models\Octo\Repository $repository
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereAssignees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereAuthorAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereAuthorLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereClosedAtGithub($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereCommentsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereCreatedAtGithub($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereIssueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereLabels($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereOctoConnectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereOctoRepositoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Issues whereUpdatedAtGithub($value)
 * @mixin \Eloquent
 */
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
