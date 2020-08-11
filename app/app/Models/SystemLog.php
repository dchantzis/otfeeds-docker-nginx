<?php

namespace App\Models;

use App\Traits\Encryptable;
use App\Traits\ModelGetTableNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\SystemLog
 *
 * @property int $id
 * @property int $ip_trace_id
 * @property string $message
 * @property string $level
 * @property string $level_name
 * @property string $context
 * @property string $channel
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SystemLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SystemLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SystemLog query()
 * @mixin \Eloquent
 */
class SystemLog extends Model
{

    use ModelGetTableNameTrait;

    use Encryptable;

    protected $table = 'feeds_system_logs';

    protected $fillable = [
        'ip_trace_id',
        'message',
        'level',
        'level_name',
        'context',
        'channel'
    ];

    protected $encryptable = [
        'context',
    ];

    /**
     * @return BelongsTo
     */
    public function ipTrace()
    {
        return $this->belongsTo(IPTrace::class);
    }

}
