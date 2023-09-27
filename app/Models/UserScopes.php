<?php

namespace App\Models;

trait UserScopes
{
    /**
     * Adds onto a query parameters provided in request to search for items of this instance.
     * 
     * @param  \Illuminate\Database\Eloquent\Model  $query
     * @param  \Illuminate\Http\Request             $request
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function scopeSearch($query, $request)
    {
        if($request->has('search'))
        {   
            $searchParam = filter_var($request['search'], FILTER_SANITIZE_STRING);
            
            if($request->has('username') || $request->has('name') || $request->has('email') || $request->has('created_at') || $request->has('updated_at'))
            {
                if($request->has('username'))
                {
                    $query = $query->where('users.username', 'like', '%'.$searchParam.'%');
                }
                if($request->has('name'))
                {
                    $fullName = explode(' ', $searchParam);

                    if(sizeof($fullName) == 2)
                    {
                        $query = $query->where('users.first_name', 'like', '%'.$fullName[0].'%');
                        $query = $query->where('users.last_name', 'like', '%'.$fullName[1].'%');
                    }
                    else
                    {
                        $query = $query->where('users.first_name', 'like', '%'.$fullName[0].'%');
                    }
                }
                if($request->has('email'))
                {
                    $query = $query->where('users.email', 'like', '%'.$searchParam.'%');
                }
                if($request->has('created_at'))
                {
                    $query = $query->whereDate('users.created_at', 'like', '%'.$searchParam.'%');
                }
                if($request->has('updated_at'))
                {
                    $query = $query->whereDate('users.updated_at', 'like', '%'.$searchParam.'%');
                }
            }
            else
            {
                $query = $query->where('users.username', 'like', '%'.$searchParam.'%')
                                ->orWhere('users.first_name', 'like', '%'.$searchParam.'%')
                                ->orWhere('users.last_name', 'like', '%'.$searchParam.'%')
                                ->orWhere('users.email', 'like', '%'.$searchParam.'%')
                                ->orWhere('users.created_at', 'like', '%'.$searchParam.'%')
                                ->orWhere('users.updated_at', 'like', '%'.$searchParam.'%');
            }
        }

        return $query;
    }

    /**
     *  Get users available to a given user.
     *
     *  @param  \Illuminate\Database\Eloquent\Model $query
     *  @param  \App\Models\User $user
     *  @return \Illuminate\Database\Eloquent\Model
     */
    public function scopeGetAccessibleUsers($query, $user)
    {
        return $query->select(
                'users.username', 'users.first_name', 'users.last_name', 'users.email', 
                'users.created_at', 'users.updated_at', 'users.id'
            )
            ->leftJoin('client_user', 'users.id', '=', 'client_user.user_id')
            ->where('users.id', '!=', $user->id)
            ->groupBy('users.id');
    }
}