<?php

namespace Vuetified\Observers;

use Vuetified\Vuetified;
use Illuminate\Database\Eloquent\Model;
use Keygen;
use Hash;

class UserObserver
{
    protected $user;

    /**
     * Listen to the User created event.
     *
     * @param  User  $user
     * @return void
     */
    public function creating(Model $user)
    {
        $usermodel = Vuetified::userModel();
        if($user instanceOf $usermodel){
            // If We Didnt Passed Any  Id On user Creation then We Generate One
            if(is_null($user->id) && !is_numeric($user->id)){
                $user->id = User::generateUniqueID();
            }
        }
    }

    /**
     * Listen to the User deleting event.
     *
     * @param  User  $user
     * @return void
     */
    public function deleting(User $user)
    {
        //
    }

}