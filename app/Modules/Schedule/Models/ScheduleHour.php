<?php

namespace App\Modules\Schedule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ScheduleHour extends Model
{
    use HasUuids;

    const TABLE = 'schedule_hours';

    protected $table = self::TABLE;
    protected $primaryKey = self::ID;
    public $incrementing = false;
    protected $keyType = 'string';

    const ID = 'id';
    const START = 'start';
    const END = 'end';
    const HOUR = 'hour';

    protected $fillable = [self::START, self::END, self::HOUR];
}
