<?php

namespace App\Models\Octo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $octo_connection_id
 * @property int $repo_id
 * @property string $name
 * @property string $full_name
 * @property string|null $description
 * @property int $forks_count
 * @property int $stargazers_count
 * @property string|null $language
 * @property int $private
 * @property int $open_issues_count
 * @property int $watchers_count
 * @property string|null $updated_at_github
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Octo\Connection $connection
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereForksCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereOctoConnectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereOpenIssuesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository wherePrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereRepoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereStargazersCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereUpdatedAtGithub($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Repository whereWatchersCount($value)
 * @mixin \Eloquent
 */
class Repository extends Model
{
    use HasFactory;
    
    protected $table = 'octo_repositories';

    protected $guarded = [];

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
