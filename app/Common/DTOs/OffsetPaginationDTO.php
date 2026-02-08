<?php

namespace App\Common\DTOs;

use Illuminate\Support\Collection;

class OffsetPaginationDTO
{
    public Collection $items;
    public int $total;
    public int $limit;
    public int $offset;

    /**
     * @param Collection $items Los registros paginados
     * @param int $total Total de registros sin paginar
     * @param int $limit Cantidad de registros devueltos por página
     * @param int $offset Desplazamiento desde el inicio
     */
    public function __construct(Collection $items, int $total, int $limit, int $offset)
    {
        $this->items = $items;
        $this->total = $total;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * Convertir a array para enviarlo a un Resource o API
     */
    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'total' => $this->total,
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];
    }
}
