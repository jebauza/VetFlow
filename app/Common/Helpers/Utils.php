<?php

namespace App\Common\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Utils
{
    public static function dumpLog(string $message, string $type = 'info')
    {
        dump($message);
        Log::$type($message);
    }

    public static function logElapsedTime($timeStart, $task = "Elapsed Time")
    {
        $now = Carbon::now();
        $elapsedTime = $now->diffInMilliseconds($timeStart);
        Utils::dumpLog($task . ': ' . gmdate("H:i:s", $elapsedTime / 1000) . " (" . $elapsedTime . ")");
    }

    public static function message($message)
    {
        dump($message);
        Log::info($message);
    }

    public static function urlMerge(string ...$parts)
    {
        $url = trim($parts[0], '/');

        for ($i = 1; $i < count($parts); $i++) {
            $url .= '/' . trim($parts[$i], '/');
        }

        return $url;
    }
}
