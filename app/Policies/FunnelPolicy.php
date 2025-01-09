<?php

namespace App\Policies;

use App\User;
use App\funnel;
use Illuminate\Auth\Access\HandlesAuthorization;

class FunnelPolicy
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
     * @param  \App\funnel  $funnel
     * @return mixed
     */
    public function view(User $user, funnel $funnel)
    {
        return isUserOwningModel($user, $funnel);
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
     * @param  \App\funnel  $funnel
     * @return mixed
     */
    public function update(User $user, funnel $funnel)
    {
        return isUserOwningModel($user, $funnel);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\funnel  $funnel
     * @return mixed
     */
    public function delete(User $user, funnel $funnel)
    {
        return isUserOwningModel($user, $funnel);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\funnel  $funnel
     * @return mixed
     */
    public function restore(User $user, funnel $funnel)
    {
        return isUserOwningModel($user, $funnel);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\funnel  $funnel
     * @return mixed
     */
    public function forceDelete(User $user, funnel $funnel)
    {
        return isUserOwningModel($user, $funnel);
    }
}
