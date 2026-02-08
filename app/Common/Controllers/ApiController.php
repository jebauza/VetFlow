<?php

namespace App\Common\Controllers;

use App\Common\DTOs\PagePaginationDTO;
use App\Common\DTOs\OffsetPaginationDTO;
use Illuminate\Contracts\Pagination\CursorPaginator;

abstract class ApiController
{
    public function getMetaPagination($data): array|null
    {
        $meta = null;

        if ($data instanceof PagePaginationDTO) {
            $meta = [
                'current_page'  => $data->currentPage,
                'per_page'      => $data->perPage,
                'last_page'     => $data->lastPage,
                'total'         => $data->total,
            ];
        } elseif ($data instanceof OffsetPaginationDTO) {
            $meta = [
                'offset'    => $data->offset,
                'limit'     => $data->limit,
                'total'     => $data->total,
            ];
        } elseif ($data instanceof CursorPaginator) {
            $meta = [
                'per_page'    => $data->perPage(),
                'next_cursor' => optional($data->nextCursor())->encode(),
                'prev_cursor' => optional($data->previousCursor())->encode(),
            ];
        }

        return $meta;
    }
}
