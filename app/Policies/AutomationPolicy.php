<?php

namespace App\Policies;

use App\Automation;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AutomationPolicy
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
     * @param  \App\Automation  $automation
     * @return mixed
     */
    public function view(User $user, Automation $automation)
    {
        return isUserOwningModel($user, $automation);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Automation  $automation
     * @return mixed
     */
    public function update(User $user, Automation $automation)
    {
        return isUserOwningModel($user, $automation);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Automation  $automation
     * @return mixed
     */
    public function delete(User $user, Automation $automation)
    {
        return isUserOwningModel($user, $automation);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Automation  $automation
     * @return mixed
     */
    public function restore(User $user, Automation $automation)
    {
        return isUserOwningModel($user, $automation);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Automation  $automation
     * @return mixed
     */
    public function forceDelete(User $user, Automation $automation)
    {
        return isUserOwningModel($user, $automation);
    }
}
