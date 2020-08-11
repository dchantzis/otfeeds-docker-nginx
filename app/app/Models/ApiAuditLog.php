<?php


namespace App\Models;

use App\Traits\Encryptable;
use App\Traits\ModelGetTableNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ApiAuditLog
 *
 * @property int $id
 * @property int|null $ip_trace_id
 * @property int|null $consumer_id
 * @property array|null $content
 * @property string|null $type
 * @property array|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Consumer|null $consumer
 * @property-read \App\Models\IPTrace|null $ipTrace
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApiAuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApiAuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApiAuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApiAuditLog whereConsumerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApiAuditLog whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApiAuditLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApiAuditLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApiAuditLog whereIpTraceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApiAuditLog whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApiAuditLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ApiAuditLog whereUpdatedAt($value)
 */
class ApiAuditLog extends Model
{
    use ModelGetTableNameTrait;

    use Encryptable;

    protected $table = 'feeds_api_audit_logs';

    protected $fillable = [
        'ip_trace_id',
        'consumer_id',
        'content',
        'type',
        'meta'
    ];

    const REQUEST = 'request';
    const RESPONSE = 'response';

    protected $encryptable = [
        'content',
        'meta',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * @return BelongsTo
     */
    public function consumer()
    {
        return $this->belongsTo(Consumer::class);
    }

    /**
     * @return BelongsTo
     */
    public function ipTrace()
    {
        return $this->belongsTo(IPTrace::class);
    }

}
