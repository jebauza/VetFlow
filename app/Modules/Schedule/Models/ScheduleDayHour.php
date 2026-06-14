<?php

namespace App\Modules\Schedule\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ScheduleDayHour extends Pivot
{
    use HasUuids;

    public const TABLE = 'schedule_day_hour';

    protected $table = self::TABLE;
    protected $primaryKey = self::ID;
    public $incrementing = false;
    protected $keyType = 'string';

    public const ID = 'id';
    public const SCHEDULE_DAY_ID = 'schedule_day_id';
    public const SCHEDULE_HOUR_ID = 'schedule_hour_id';
}
