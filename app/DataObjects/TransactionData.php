<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Entity\Category;
use DateTime;

class TransactionData
{
    /**
     * @param  string    $description
     * @param  float     $amount
     * @param  DateTime  $date
     * @param  Category  $category
     */
    public function __construct(
        public readonly string $description,
        public readonly float $amount,
        public readonly DateTime $date,
        public readonly ?Category $category
    ) {
    }
}