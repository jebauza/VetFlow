<?php

namespace App\Common\Helpers;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DbExceptionHelper
{
    public static function handle(string $context, QueryException $e): never
    {
        self::handlePostgreSQL($context, $e);

        Log::error("[$context] " . $e->getMessage());
        throw new HttpException(500, 'Internal server error');
    }

    private static function handlePostgreSQL(string $context, QueryException $e): void
    {
        // 23505: unique constraint violation
        if ($e->getCode() === '23505') {
            Log::error("[$context] " . $e->getMessage());
            throw new BadRequestHttpException(self::extractDetail($e, 'Duplicate entry.'), $e);
        }

        // 23503: foreign key constraint violation
        if ($e->getCode() === '23503') {
            Log::error("[$context] " . $e->getMessage());
            throw new NotFoundHttpException(self::extractDetail($e, 'Related record not found.'), $e);
        }
    }

    private static function extractDetail(QueryException $e, string $fallback): string
    {
        preg_match('/DETAIL:\s*Key\s*(.+?)(?=\s*\(Connection:|$)/i', $e->getMessage(), $matches);

        return isset($matches[1]) ? trim($matches[1]) : $fallback;
    }
}
