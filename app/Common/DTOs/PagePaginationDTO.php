<?php

namespace App\Common\DTOs;

use Illuminate\Support\Collection;

class PagePaginationDTO
{
    public Collection $items;
    public int $total;
    public int $perPage;
    public int $currentPage;
    public int $lastPage;

    public function __construct(Collection $items, int $total, int $perPage, int $currentPage)
    {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
        $this->lastPage = (int) ceil($total / $perPage);
    }

    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'total' => $this->total,
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
        ];
    }
}
