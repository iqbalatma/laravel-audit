<?php

namespace Iqbalatma\LaravelAudit\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string id
 */
class User extends Model
{
    use HasUuids;

    protected $table = "users";

    protected $fillable = [
    ];
}
