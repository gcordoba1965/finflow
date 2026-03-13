<?php

namespace App\Policies;

use App\Models\IncomeSource;
use App\Models\User;

class IncomeSourcePolicy
{
    public function viewAny(User $user): bool { return true; }

    public function view(User $user, IncomeSource $source): bool
    {
        return $user->id === $source->user_id || $user->isAdmin();
    }

    public function create(User $user): bool { return $user->is_active; }

    public function update(User $user, IncomeSource $source): bool
    {
        return $user->id === $source->user_id;
    }

    public function delete(User $user, IncomeSource $source): bool
    {
        return $user->id === $source->user_id;
    }
}
