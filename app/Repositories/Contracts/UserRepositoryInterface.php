<?php


namespace App\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function findById(int $id): ?object;
    
    public function findByEmail(string $email): ?object;
    
    public function createUser(array $data): object;
    
    public function isAdmin(int $id): bool;
}