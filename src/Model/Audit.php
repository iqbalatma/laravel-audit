<?php

namespace Iqbalatma\LaravelAudit\Model;

use App\Enums\Gender;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * @property string email
 * @property string phone
 * @property string object_table
 * @property string object_id
 * @property string trail
 * @property string tag
 * @property string additional_data
 * @property string app_name
 */
class Audit extends Model
{
    use HasUuids, HasUuids;

    protected $table = "audits";

    protected $fillable = [
        "message", "action", "ip_address", "endpoint", "method", "user_agent", "actor_table",
        "actor_id", "actor_name", "email", "phone", "object_table", "object_id", "trail",
        "tag", "additional_data", "app_name"
    ];
}
