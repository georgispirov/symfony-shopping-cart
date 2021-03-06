<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Product;
use AppBundle\Entity\User;

interface IUserRepository
{
    /**
     * @return array
     */
    public function getAllUsers(): array;

    /**
     * @param int $id
     * @return null|User
     */
    public function getUserByID(int $id);

    /**
     * @param User $user
     * @param array $roles
     * @return bool
     */
    public function updateUserRoles(User $user, array $roles): bool;

    /**
     * @param User $user
     * @param array $roles
     * @return bool
     */
    public function demoteUserRoles(User $user, array $roles): bool;
}