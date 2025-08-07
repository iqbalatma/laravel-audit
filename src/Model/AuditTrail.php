<?php

namespace Iqbalatma\LaravelAudit\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string id
 * @property string audit_id
 * @property string object_table
 * @property string object_id
 * @property string before
 * @property string after
 * @property string tag
 * @property string additional
 * @property Audit audit
 */
class AuditTrail extends Model
{
    use HasUuids, HasUuids;

    protected $table = "audit_trails";

    protected $fillable = [
        "audit_id", "object_table", "object_id", "before", "after",
        "tag", "additional"
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, "audit_id", "id");
    }
}
