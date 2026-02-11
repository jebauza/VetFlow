<?php

namespace App\Modules\Schedule\Controllers;

use Illuminate\Http\Request;
use App\Modules\User\Models\User;
use App\Common\Responses\ApiResponse;
use App\Common\Controllers\ApiController;
use App\Modules\Schedule\Resources\ConfigResource;
use App\Modules\Schedule\Services\ScheduleService;

class ScheduleApiController extends ApiController
{
    public function __construct(
        protected readonly ScheduleService $service
    ) {}

    public function config()
    {
        $dto = $this->service->config();

        return ApiResponse::successData(
            new ConfigResource($dto)
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
