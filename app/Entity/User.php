<?php

declare(strict_types=1);

namespace App\Entity;

use App\Contracts\OwnableInterface;
use App\Contracts\UserInterface;
use App\Entity\Traits\HasTimestamps;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;

#[Entity, table(name: 'users')]
#[HasLifecycleCallbacks]
class User implements UserInterface
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column(type: 'string', length: 255)]
    private string $name;

    #[Column(type: 'string', length: 255)]
    private string $email;

    #[Column(type: 'string', length: 255)]
    private string $password;

    #[Column(name: 'two_factor', options: ['default' => false])]
    private bool $twoFactor;

    #[Column(name: 'verified_at', nullable: true)]
    private ?\DateTime $verifiedAt;

    #[OneToMany(mappedBy: 'user', targetEntity: Transaction::class)]
    private Collection $transactions;

    #[OneToMany(mappedBy: 'user', targetEntity: Category::class)]
    private Collection $categories;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->twoFactor = false;
    }

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string  $name
     * @return User
     */
    public function setName(string $name): User
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param  string  $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param  string  $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
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
     * @return User
     */
    public function setCreatedAt(\DateTime $createdAt): User
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @param  Category  $category
     * @return User
     */
    public function addCategory(Category $category): User
    {
        $this->categories->add($category);
        return $this;
    }

    /**
     * @return Collection
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    /**
     * @param  Transaction  $transaction
     * @return User
     */
    public function addTransaction(Transaction $transaction): User
    {
        $this->transactions->add($transaction);
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param  \DateTime  $updatedAt
     * @return User
     */
    public function setUpdatedAt(\DateTime $updatedAt): User
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @param  OwnableInterface  $entity
     *
     * @return bool
     */
    public function canManage(OwnableInterface $entity): bool
    {
        return $this->getId() === $entity->getUser()->getId();
    }

    /**
     * @return \DateTime|null
     */
    public function getVerifiedAt(): ?\DateTime
    {
        return $this->verifiedAt;
    }

    /**
     * @param  \DateTime  $verifiedAt
     *
     * @return $this
     */
    public function setVerifiedAt(\DateTime $verifiedAt): static
    {
        $this->verifiedAt = $verifiedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasTwoFactorAuthEnabled(): bool
    {
        return $this->twoFactor;
    }

    /**
     * @param  bool  $twoFactor
     *
     * @return $this
     */
    public function setTwoFactor(bool $twoFactor): User
    {
        $this->twoFactor = $twoFactor;

        return $this;
    }
}