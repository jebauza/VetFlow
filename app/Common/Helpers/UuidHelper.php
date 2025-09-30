<?php


namespace App\Common\Helpers;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class UuidHelper
{
    public static function uuidSql(): string
    {
        return 'public.uuid_generate_v4()';
    }

    public static function uuidRaw(): Expression
    {
        return DB::raw(self::uuidSql());
    }

    public static function newBinaryUuid(): string
    {
        return Uuid::uuid4()->toString();
    }

    public static function toUuid(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        return $value;
    }
}
