<?php

declare(strict_types=1);

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

class EntityManagerService
{
    public function __construct(protected readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * @param  string|null  $entityName
     *
     * @return void
     */
    public function clear(?string $entityName = null): void
    {
        if ($entityName === null) {
            $this->entityManager->clear();
            return;
        }
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $entities = $unitOfWork->getIdentityMap()[$entityName] ?? [];

        foreach ($entities as $entity) {
            $this->entityManager->detach($entity);
        }
    }
}