<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;

trait UsesTestingSchema
{
    /**
     * Cambia el schema de PostgreSQL a 'testing'.
     */
    public function useTestingSchema(): void
    {
        DB::statement('SET search_path TO testing');
    }
}
