<?php

namespace Iqbalatma\LaravelAudit\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string id
 * @property string message
 * @property string action
 * @property string ip_address
 * @property string endpoint
 * @property string method
 * @property string user_agent
 * @property string actor_table
 * @property string actor_id
 * @property string actor_name
 * @property string actor_email
 * @property string actor_phone
 * @property string object_table
 * @property string object_id
 * @property string trail
 * @property string tag
 * @property string additional
 * @property string app_name
 */
class Audit extends Model
{
    use HasUuids, HasUuids;

    protected $table = "audits";

    protected $fillable = [
        "message", "action", "ip_address", "endpoint", "method", "user_agent", "actor_table",
        "actor_id", "actor_name", "actor_email", "actor_phone", "object_table", "object_id", "trail",
        "tag", "additional", "app_name"
    ];
}
