<?php

namespace App\Modules\User\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Common\Responses\ApiResponse;
use App\Common\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Modules\User\Services\UserService;
use App\Modules\User\Resources\UserResource;

class UserPaginateApiController extends ApiController
{
    public function __construct(
        protected readonly UserService $userService
    ) {}

    /**
     * Paginate and retrieve a list of users.
     *
     * Validates pagination parameters, delegates to `userService`, and returns a paginated
     * collection of `UserResource` objects via an `ApiResponse`.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing 'search', 'page', and 'per_page' parameters.
     * @return \Illuminate\Http\JsonResponse Returns a paginated list of users (200) or validation errors (422).
     */
    /**
     * @LRDparam search nullable|string
     * @LRDparam page nullable|integer|min:1
     * @LRDparam per_page nullable|integer|min:1|max:100
     *
     * @lrd:start
     *
     * **Set Global Headers**
     * ```json
     *{"Authorization": "Bearer <access_token>", "Content-Type": "application/json", "Accept": "application/json"}
     * ```
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":[{"id":"a103086c-e405-4812-96c4-1201605ba282","name":"Brisa Kilback","surname":"Sipes","email":"dicki.violette@example.org","avatar":null,"phone":"+18542539801","type_document":"dni","n_document":"68591182F","birth_date":"1973-09-18","roles":[{"id":"a1030860-2a5d-482d-b4d2-8450ea436186","name":"Vet"}],"all_permissions":[{"id":"a103085f-e554-433d-9e6d-405bdcbb7efd","name":"veterinary.profile"},{"id":"a103085f-e8cd-4dce-b79e-eac218b05493","name":"pet.register"},{"id":"a103085f-eb4c-48c1-a57e-cd8bc56e2e5b","name":"pet.list"},{"id":"a103085f-ed71-4f0a-b40e-8125d1ab3d93","name":"pet.edit"},{"id":"a103085f-ef40-4643-b1ad-112a1a2b712d","name":"pet.delete"},{"id":"a103085f-f0dd-40b4-aad1-5d9587f82716","name":"pet.profile"},{"id":"a103085f-f250-45b4-9daa-deb62e36ca32","name":"staff.register"},{"id":"a103085f-f3ac-40ea-b7fb-701a6f815beb","name":"staff.list"},{"id":"a103085f-f53d-4cb7-aca3-11ee5304c673","name":"staff.edit"}]}],"meta":{"per_page":1,"current_page":1,"last_page":41,"total":41}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"page":["The page field must be at least 1."]}}
     * ```
     *
     * **500 Internal Server Error**
     * ```json
     *{"message":"Internal Server Error"}
     * ```
     *
     * @lrd:end
     *
     * @LRDresponses 200|401|422|500
     */
    public function paginate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string',
            'page'  => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails())
            return ApiResponse::validation($validator->errors()->toArray());

        $pagePaginationDTO = $this->userService->pagePaginate(
            $request->input('search'),
            $request->input('page'),
            $request->input('per_page')
        );

        return ApiResponse::successData(
            UserResource::collection($pagePaginationDTO->items),
            200,
            $this->getMetaPagination($pagePaginationDTO)
        );
    }

    /**
     * @LRDparam search nullable|string
     * @LRDparam offset nullable|integer|min:0
     * @LRDparam limit nullable|integer|min:1|max:100
     *
     * @lrd:start
     *
     * **Set Global Headers**
     * ```json
     *{"Authorization": "Bearer <access_token>", "Content-Type": "application/json", "Accept": "application/json"}
     * ```
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":[{"id":"a103086c-e8f9-47a8-a8ce-450d856d461b","name":"Buster Windler MD","surname":"Heaney","email":"meggie83@example.org","avatar":null,"phone":"+15179208822","type_document":"nie","n_document":"Z8774832S","birth_date":"1976-09-01","roles":[{"id":"a1030860-2b5c-447b-a33b-9437fe8b7acc","name":"Receptionist"}],"all_permissions":[{"id":"a1030860-13ee-44c2-97c6-fbb1c282ee1e","name":"vaccionation.list"},{"id":"a1030860-16da-41f8-925f-a3296974d634","name":"vaccionation.edit"},{"id":"a1030860-1963-4128-8e23-610920510a56","name":"vaccionation.delete"},{"id":"a1030860-1b78-4393-b2e7-b09579205d5c","name":"surgeries.register"},{"id":"a1030860-1d57-429b-a1d2-4d03156b41c2","name":"surgeries.list"},{"id":"a1030860-1fa8-4f5c-8ea8-1e5fdb8a0934","name":"surgeries.edit"},{"id":"a1030860-223c-4ecc-bbf9-124071044f62","name":"surgeries.delete"},{"id":"a1030860-2462-4004-a6a4-b9a595aa46b4","name":"medical_records.show"},{"id":"a1030860-2644-4550-9e5b-36c63aaa7f11","name":"report_grafics.show"}]}],"meta":{"offset":1,"limit":1,"total":41}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"limit":["The limit field must be at least 1."]}}
     * ```
     *
     * **500 Internal Server Error**
     * ```json
     *{"message":"Internal Server Error"}
     * ```
     *
     * @lrd:end
     *
     * @LRDresponses 200|401|422|500
     */
    public function offsetPaginate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string',
            'offset' => 'nullable|integer|min:0',
            'limit'  => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails())
            return ApiResponse::validation($validator->errors()->toArray());

        $offsetPaginationDTO = $this->userService->offsetPaginate(
            $request->input('search'),
            $request->input('offset'),
            $request->input('limit'),
        );

        return ApiResponse::successData(
            UserResource::collection($offsetPaginationDTO->items),
            200,
            $this->getMetaPagination($offsetPaginationDTO)
        );
    }

    /**
     * @LRDparam search nullable|string
     * @LRDparam cursor nullable|string
     * @LRDparam per_page nullable|integer|min:1|max:100
     *
     * @lrd:start
     *
     * **Set Global Headers**
     * ```json
     *{"Authorization": "Bearer <access_token>", "Content-Type": "application/json", "Accept": "application/json"}
     * ```
     *
     * **200 OK**
     * ```json
     *{"message":"OK","data":[{"id":"a103086c-e8f9-47a8-a8ce-450d856d461b","name":"Buster Windler MD","surname":"Heaney","email":"meggie83@example.org","avatar":null,"phone":"+15179208822","type_document":"nie","n_document":"Z8774832S","birth_date":"1976-09-01","roles":[{"id":"a1030860-2b5c-447b-a33b-9437fe8b7acc","name":"Receptionist"}],"all_permissions":[{"id":"a1030860-13ee-44c2-97c6-fbb1c282ee1e","name":"vaccionation.list"},{"id":"a1030860-16da-41f8-925f-a3296974d634","name":"vaccionation.edit"},{"id":"a1030860-1963-4128-8e23-610920510a56","name":"vaccionation.delete"},{"id":"a1030860-1b78-4393-b2e7-b09579205d5c","name":"surgeries.register"},{"id":"a1030860-1d57-429b-a1d2-4d03156b41c2","name":"surgeries.list"},{"id":"a1030860-1fa8-4f5c-8ea8-1e5fdb8a0934","name":"surgeries.edit"},{"id":"a1030860-223c-4ecc-bbf9-124071044f62","name":"surgeries.delete"},{"id":"a1030860-2462-4004-a6a4-b9a595aa46b4","name":"medical_records.show"},{"id":"a1030860-2644-4550-9e5b-36c63aaa7f11","name":"report_grafics.show"}]}],"meta":{"per_page":1,"next_cursor":"eyJ1c2Vycy5uYW1lIjoiQnVzdGVyIFdpbmRsZXIgTUQiLCJ1c2Vycy5zdXJuYW1lIjoiSGVhbmV5IiwiX3BvaW50c1RvTmV4dEl0ZW1zIjp0cnVlfQ","prev_cursor":"eyJ1c2Vycy5uYW1lIjoiQnVzdGVyIFdpbmRsZXIgTUQiLCJ1c2Vycy5zdXJuYW1lIjoiSGVhbmV5IiwiX3BvaW50c1RvTmV4dEl0ZW1zIjpmYWxzZX0"}}
     * ```
     *
     * **401 Unauthorized**
     * ```json
     *{"message":"Unauthorized","errors":{"auth":["Authentication token is invalid or expired"]}}
     * ```
     *
     * **422 Unprocessable Entity**
     * ```json
     *{"message":"Validation errors","errors":{"per_page":["The per page field must be at least 1."]}}
     * ```
     *
     * **500 Internal Server Error**
     * ```json
     *{"message":"Internal Server Error"}
     * ```
     *
     * @lrd:end
     *
     * @LRDresponses 200|401|422|500
     */
    public function cursorPaginate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string',
            'cursor' => 'nullable|string',
            'per_page'  => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails())
            return ApiResponse::validation($validator->errors()->toArray());

        $cursorPaginate = $this->userService->cursorPaginate(
            $request->input('search'),
            $request->input('per_page')
        );

        return ApiResponse::successData(
            UserResource::collection($cursorPaginate->items()),
            200,
            $this->getMetaPagination($cursorPaginate)
        );
    }
}
