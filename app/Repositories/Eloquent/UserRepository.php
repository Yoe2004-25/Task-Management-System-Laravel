<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    protected User $model;
    
    public function __construct(User $model)
    {
        $this->model = $model;
    }
    
    public function findById(int $id): ?object
    {
        return $this->model->find($id);
    }
    
    public function findByEmail(string $email): ?object
    {
        return $this->model->where('email', $email)->first();
    }
    
    public function createUser(array $data): object
    {
        $data['password'] = Hash::make($data['password']);
        return $this->model->create($data);
    }
    
    public function isAdmin(int $id): bool
    {
        $user = $this->findById($id);
        return $user ? $user->isAdmin() : false;
    }
}