<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Receipt;
use Doctrine\ORM\EntityManagerInterface;

class ReceiptService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param                  $transaction
     * @param  string          $filename
     * @param  string          $storageFilename
     *
     * @return Receipt
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    public function create($transaction, string $filename, string $storageFilename, string $mediaType): Receipt
    {
        $receipt = new Receipt();

        $receipt->setTransaction($transaction);
        $receipt->setFilename($filename);
        $receipt->setStorageFilename($storageFilename);
        $receipt->setMediaType($mediaType);
        $receipt->setCreatedAt(new \DateTime());

        return $receipt;
    }

    /**
     * @param  int  $id
     *
     * @return Receipt|object|null
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getById(int $id)
    {
        return $this->entityManager->find(Receipt::class, $id);
    }
}