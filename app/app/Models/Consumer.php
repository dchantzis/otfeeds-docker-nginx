<?php

namespace App\Models;

use App\Traits\ModelGetTableNameTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property string company_name
 * @property string contact_name
 * @property string contact_email
 * @property string contact_phone
 * @property string utm_source
 * @property string utm_medium
 * @property string utm_campaign
 * @property string access_key
 */
class Consumer extends Model
{
    use ModelGetTableNameTrait;

    protected $table = 'consumers';

    public static $exclusionTable = 'excluded_feeds';

    protected $guarded = array(
        'access_key',
    );

    public function __construct(array $attributes = array())
    {
        $this->access_key = bin2hex(openssl_random_pseudo_bytes(20));

        parent::__construct($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthIdentifier()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthPassword()
    {
        return $this->access_key;
    }

    /**
     * {@inheritdoc}
     */
    public function getRememberToken()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function setRememberToken($value)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getRememberTokenName()
    {
        return;
    }

    /**
     * @return HasMany
     */
    public function ipTrace()
    {
        return $this->hasMany(IPTrace::class);
    }

    public function apiAuditLog()
    {
        return $this->hasMany(ApiAuditLog::class);
    }

}
