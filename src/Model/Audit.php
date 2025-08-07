<?php

namespace Iqbalatma\LaravelAudit\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string id
 * @property string message
 * @property string action
 * @property string ip_address
 * @property string endpoint
 * @property string method
 * @property string user_agent
 * @property string user_request
 * @property string actor_table
 * @property string actor_id
 * @property string actor_name
 * @property string actor_email
 * @property string actor_phone
 * @property string entry_object_table
 * @property string entry_object_id
 * @property string trail
 * @property string tag
 * @property string additional
 * @property string app_name
 * @property Collection<AuditTrail> audit_trails
 */
class Audit extends Model
{
    use HasUuids, HasUuids;

    protected $table = "audits";

    protected $fillable = [
        "message", "action", "ip_address", "endpoint", "method", "user_agent", "actor_table",
        "actor_id", "actor_name", "actor_email", "actor_phone", "entry_object_table", "entry_object_id",
        "tag", "additional", "app_name", "user_request"
    ];

    /**
     * @return HasMany
     */
    public function audit_trails(): HasMany
    {
        return $this->hasMany(AuditTrail::class, "audit_id", "id");
    }
}
