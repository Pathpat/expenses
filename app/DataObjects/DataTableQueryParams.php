<?php

declare(strict_types=1);

namespace App\DataObjects;

class DataTableQueryParams
{

    /**
     * @param  int  $start
     * @param  int  $length
     * @param  string  $orderBy
     * @param  string  $orderDir
     * @param  string  $searchTerm
     * @param  int  $draw
     */
    public function __construct(
        public readonly int $start,
        public readonly int $length,
        public readonly string $orderBy,
        public readonly string $orderDir,
        public readonly string $searchTerm,
        public readonly int $draw
    ) {
    }
}