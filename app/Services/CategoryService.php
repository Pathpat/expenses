<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\DataObjects\DataTableQueryParams;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\TransactionRequiredException;

class CategoryService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManager)
    {
    }

    /**
     * @throws ORMException
     */
    public function create(string $name, User $user): Category
    {
        $category = new Category();

        $category->setUser($user);

        return $this->update($category, $name);
    }

    public function getPaginatedCategories(DataTableQueryParams $params): Paginator
    {
        $query = $this->entityManager->getRepository(Category::class)
            ->createQueryBuilder('c')
            ->setFirstResult($params->start)
            ->setMaxResults($params->length);

        $orderBy = in_array($params->orderBy, ['name', 'createdAt', 'updatedAt']) ? $params->orderBy : 'updatedAt';
        $orderDir = strtolower($params->orderDir) === 'asc' ? 'asc' : 'desc';

        if (!empty($params->searchTerm)) {
            $query->where('c.name LIKE :name')
                ->setParameter('name', '%'.addcslashes($params->searchTerm, '%_').'%');
        }

        $query->orderBy('c.'.$orderBy, $orderDir);

        return new Paginator($query);
    }

    /**
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws ORMException
     */
    public function getById(int $id): ?Category
    {
        return $this->entityManager->find(Category::class, $id);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function update(Category $category, string $name): Category
    {
        $category->setName($name);

        return $category;
    }

    /**
     * @return array
     */
    public function getCategoryNames(): array
    {
        return $this->entityManager->getRepository(Category::class)->createQueryBuilder('c')
            ->select('c.id','c.name')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param  string  $name
     *
     * @return Category|null
     */
    public function findByName(string $name): ?Category
    {
        return $this->entityManager->getRepository(Category::class)->findBy(['name' => $name])[0] ?? null;
    }

    /**
     * @return array
     */
    public function getAllKeyedByName(): array
    {
        $categories = $this->entityManager->getRepository(Category::class)->findAll();
        $categoryMap = [];

        foreach ($categories as $category) {
            $categoryMap[strtolower($category->getName())] = $category;
        }

        return $categoryMap;
    }
}