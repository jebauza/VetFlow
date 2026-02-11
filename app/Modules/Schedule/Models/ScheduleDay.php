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

    const TABLE = 'schedule_days';

    protected $table = self::TABLE;
    protected $primaryKey = self::ID; // Or your UUID column name
    public $incrementing = false;
    protected $keyType = 'string'; // UUIDs are strings

    const ID = 'id';
    const USER_ID = 'user_id';
    const DATE = 'date';

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
            'schedule_day_hour',
            'schedule_day_id',
            'schedule_hour_id'
        )
            ->withPivot('created_at', 'updated_at')
            ->withTimestamps();
    }
}
