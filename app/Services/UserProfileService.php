<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\DataObjects\UserProfileData;
use App\Entity\User;

class UserProfileService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManagerService)
    {
    }

    /**
     * @param  User             $user
     * @param  UserProfileData  $data
     *
     * @return void
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function update(User $user, UserProfileData $data): void
    {
        $user->setName($data->name);
        $user->setTwoFactor($data->twoFactor);

        $this->entityManagerService->sync($user);
    }

    /**
     * @param  int  $userId
     *
     * @return UserProfileData
     */
    public function get(int $userId): UserProfileData
    {
        $user = $this->entityManagerService->find(User::class, $userId);

        return new UserProfileData($user->getEmail(), $user->getName(), $user->hasTwoFactorAuthEnabled());
    }
}