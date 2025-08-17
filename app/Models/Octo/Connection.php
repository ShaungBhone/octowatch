<?php

namespace App\Models\Octo;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $github_email
 * @property string $github_id
 * @property string $access_token
 * @property string|null $refresh_token
 * @property string $username
 * @property string|null $avatar_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereGithubEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereGithubId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereUsername($value)
 * @mixin \Eloquent
 */
class Connection extends Model
{
    use HasFactory;
    
    protected $table = 'octo_connections';
    
    protected $fillable = [
        'user_id',
        'github_id',
        'github_email',
        'access_token',
        'refresh_token',
        'username',
        'avatar_url',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
