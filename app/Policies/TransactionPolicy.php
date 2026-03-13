<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id || $user->isAdmin();
    }

    public function create(User $user): bool { return $user->is_active; }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id && $user->is_active;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id && $user->is_active;
    }
}
