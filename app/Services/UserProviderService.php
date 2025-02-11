<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;
use App\DataObjects\RegisterUserData;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class UserProviderService implements UserProviderServiceInterface
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManager)
    {
    }

    /**
     * @param  int  $userId
     * @return UserInterface|null
     */
    public function getById(int $userId): ?UserInterface
    {
        return $this->entityManager->find(User::class, $userId);
    }

    /**
     * @param  array  $credentials
     * @return UserInterface|null
     */
    public function getByCredentials(array $credentials): ?UserInterface
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
    }

    /**
     * @param  RegisterUserData  $data
     * @return UserInterface
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createUser(RegisterUserData $data): UserInterface
    {
        $user = new User();

        $user->setName($data->name);
        $user->setEmail($data->email);
        $user->setPassword(password_hash($data->password, PASSWORD_BCRYPT, ['cost' => 12]));

        $this->entityManager->sync($user);

        return $user;
    }

    /**
     * @param  UserInterface  $user
     *
     * @return void
     */
    public function verifyUser(UserInterface $user): void
    {
        $user->setVerifiedAt(new DateTime());
        $this->entityManager->sync($user);
    }
}