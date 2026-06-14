<?php

namespace App\Modules\Schedule\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Schedule\Models\ScheduleHour;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ScheduleDay extends Model
{
    use HasUuids;

    public const TABLE = 'schedule_days';

    protected $table = self::TABLE;
    protected $primaryKey = self::ID;
    public $incrementing = false;
    protected $keyType = 'string';

    public const ID = 'id';
    public const USER_ID = 'user_id';
    public const DATE = 'date';

    protected $fillable = [self::USER_ID, self::DATE];
    protected $casts = [self::DATE => 'date:Y-m-d'];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::USER_ID, User::ID);
    }

    public function hours(): BelongsToMany
    {
        return $this->belongsToMany(
            ScheduleHour::class,
            ScheduleDayHour::TABLE,
            ScheduleDayHour::SCHEDULE_DAY_ID,
            ScheduleDayHour::SCHEDULE_HOUR_ID
        )
            ->using(ScheduleDayHour::class)
            ->withTimestamps();
    }
}
