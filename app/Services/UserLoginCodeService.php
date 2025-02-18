<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\Entity\User;
use App\Entity\UserLoginCode;

class UserLoginCodeService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManagerService)
    {
    }

    /**
     * @param  User  $user
     *
     * @return UserLoginCode
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Random\RandomException
     */
    public function generate(User $user): UserLoginCode
    {
        $userLoginCode = new UserLoginCode();

        $code = random_int(100000, 999999);

        $userLoginCode->setCode((string) $code);
        $userLoginCode->setExpiration(new \DateTime('+10 minutes'));
        $userLoginCode->setUser($user);

        $this->entityManagerService->sync($userLoginCode);

        return $userLoginCode;
    }

    /**
     * @param  User    $user
     * @param  string  $code
     *
     * @return bool
     */
    public function verify(User $user, string $code): bool
    {
        $userLoginCode = $this->entityManagerService->getRepository(UserLoginCode::class)->findOneBy(
            ['user' => $user, 'code' => $code, 'isActive' => true]
        );

        if (! $userLoginCode) {
            return false;
        }

        if ($userLoginCode->getExpiration() <= new \DateTime()) {
            return false;
        }

        return true;
    }

    /**
     * @param  User  $user
     *
     * @return void
     */
    public function deactivateAllActiveCodes(User $user): void
    {
        $this->entityManagerService->getRepository(UserLoginCode::class)
            ->createQueryBuilder('c')
            ->update()
            ->set('c.isActive', '0')
            ->where('c.user = :user')
            ->andWhere('c.isActive = 1')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}