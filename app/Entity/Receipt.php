<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity, table(name: 'receipts')]
class Receipt
{
    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column(name: 'filename')]
    private string $filename;

    #[Column(name: 'storage_filename')]
    private string $storageFilename;

    #[Column(name: 'media_type')]
    private string $mediaType;

    #[Column(name: 'created_at')]
    private \DateTime $createdAt;


    #[ManyToOne(inversedBy: 'receipts')]
    private Transaction $transaction;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param  string  $filename
     * @return Receipt
     */
    public function setFilename(string $filename): Receipt
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param  \DateTime  $createdAt
     * @return Receipt
     */
    public function setCreatedAt(\DateTime $createdAt): Receipt
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Transaction
     */
    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    /**
     * @param  Transaction  $transaction
     * @return Receipt
     */
    public function setTransaction(Transaction $transaction): Receipt
    {
        $transaction->addReceipt($this);
        $this->transaction = $transaction;
        return $this;
    }

    /**
     * @return string
     */
    public function getStorageFilename(): string
    {
        return $this->storageFilename;
    }

    /**
     * @param  string  $storageFilename
     *
     * @return $this
     */
    public function setStorageFilename(string $storageFilename): Receipt
    {
        $this->storageFilename = $storageFilename;

        return $this;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function setMediaType(string $mediaType): Receipt
    {
        $this->mediaType = $mediaType;

        return $this;
    }


}