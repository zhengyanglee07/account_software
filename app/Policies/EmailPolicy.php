<?php

namespace App\Policies;

use App\Email;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\Email  $email
     * @return mixed
     */
    public function view(User $user, Email $email)
    {
        return isUserOwningModel($user, $email);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        // no restriction currently, apply whatever you want later if needed
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Email  $email
     * @return mixed
     */
    public function update(User $user, Email $email)
    {
        return isUserOwningModel($user, $email);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Email  $email
     * @return mixed
     */
    public function delete(User $user, Email $email)
    {
        return isUserOwningModel($user, $email);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Email  $email
     * @return mixed
     */
    public function restore(User $user, Email $email)
    {
        return isUserOwningModel($user, $email);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Email  $email
     * @return mixed
     */
    public function forceDelete(User $user, Email $email)
    {
        return isUserOwningModel($user, $email);
    }
}
