<?php

namespace App\Contracts;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @mixin EntityManagerInterface
 */
interface EntityManagerServiceInterface
{
    /**
     * @param  string  $name
     * @param  array   $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments);

    /**
     * @return void
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function sync($entity = null): void;

    /**
     * @param        $entity
     * @param  bool  $sync
     *
     * @return void
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete($entity, bool $sync = false): void;

    /**
     * @param  string|null  $entityName
     *
     * @return void
     */
    public function clear(?string $entityName = null): void;

    public function enableUserAuthFilter(int $userId): void;


}