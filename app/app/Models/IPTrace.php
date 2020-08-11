<?php


namespace App\Models;

use App\Traits\Encryptable;
use App\Traits\ModelGetTableNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\IPTrace
 *
 * @property int $id
 * @property string $ip_address
 * @property int|null $consumer_id
 * @property string $request_method
 * @property string $route
 * @property string|null $request_parameters
 * @property string|null $request_header
 * @property string $host
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ApiAuditLog[] $apiAuditLog
 * @property-read int|null $api_audit_log_count
 * @property-read \App\Models\Consumer|null $consumer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace whereConsumerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace whereRequestHeader($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace whereRequestMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace whereRequestParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IPTrace whereUpdatedAt($value)
 */
class IPTrace extends Model
{

    use ModelGetTableNameTrait;

    use Encryptable;

    protected $table = 'feeds_ip_trace';

    protected $fillable = [
        'ip_address',
        'consumer_id',
        'request_method',
        'route',
        'request_parameters',
        'request_header',
        'host',
    ];

    protected $encryptable = [
        'request_parameters',
        'request_header',
    ];

    const REQUEST_METHOD_GET = 'GET';
    const REQUEST_METHOD_HEAD = 'HEAD';
    const REQUEST_METHOD_POST = 'POST';
    const REQUEST_METHOD_PUT = 'PUT';
    const REQUEST_METHOD_DELETE = 'DELETE';
    const REQUEST_METHOD_CONNECT = 'CONNECT';
    const REQUEST_METHOD_OPTIONS = 'OPTIONS';
    const REQUEST_METHOD_TRACE = 'TRACE';
    const REQUEST_METHOD_PATCH = 'PATCH';

    /**
     * @return HasMany
     */
    public function apiAuditLog()
    {
        return $this->hasMany(ApiAuditLog::class);
    }

    /**
     * @return HasMany
     */
    public function systemLog()
    {
        return $this->hasMany(SystemLog::class);
    }

    /**
     * @return BelongsTo
     */
    public function consumer()
    {
        return $this->belongsTo(Consumer::class);
    }

}
