<?php

namespace App\Models;

trait ClientScopes
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
        if($request->has('company') || $request->has('representative') || $request->has('email') || $request->has('created_at') || $request->has('updated_at'))
        {
            if($request->has('search'))
            {
                $searchParam = filter_var($request['search'], FILTER_SANITIZE_STRING);

                if($request->has('company'))
                {
                    $query = $query->where('company', 'like', '%'.$searchParam.'%');
                }
                if($request->has('representative'))
                {
                    $fullName = explode(' ', $searchParam);

                    if(sizeof($fullName) == 2)
                    {
                        $query = $query->where('first_name', 'like', '%'.$fullName[0].'%');
                        $query = $query->where('last_name', 'like', '%'.$fullName[1].'%');
                    }
                    else
                    {
                        $query = $query->where('first_name', 'like', '%'.$fullName[0].'%');
                    }
                }
                if($request->has('email'))
                {
                    $query = $query->where('email', 'like', '%'.$searchParam.'%');
                }
                if($request->has('created_at'))
                {
                    $query = $query->whereDate('created_at', 'like', '%'.$searchParam.'%');
                }
                if($request->has('updated_at'))
                {
                    $query = $query->whereDate('updated_at', 'like', '%'.$searchParam.'%');
                }
            }
        }
        else
        {
            if($request->has('search'))
            {
                $searchParam = filter_var($request['search'], FILTER_SANITIZE_STRING);

                $query = $query->where('company', 'like', '%'.$searchParam.'%')
                                ->orWhere('first_name', 'like', '%'.$searchParam.'%')
                                ->orWhere('last_name', 'like', '%'.$searchParam.'%')
                                ->orWhere('email', 'like', '%'.$searchParam.'%')
                                ->orWhere('created_at', 'like', '%'.$searchParam.'%')
                                ->orWhere('updated_at', 'like', '%'.$searchParam.'%');
            }
        }

        return $query;
    }

    /**
     *  Get clients available to a given user.
     *
     *  @param  \App\Models\User $user
     *  @return \Illuminate\Database\Eloquent\Model
     */
    public function scopeGetAccessibleClients($query, $user)
    {
        return $query->select(
                'clients.slug', 'clients.first_name', 'clients.last_name', 'clients.email', 
                'clients.created_at', 'clients.updated_at', 'clients.id', 'clients.company',
                'clients.contact_number', 'clients.building_number', 'clients.street_name',
                'clients.city', 'clients.postcode'
            )
            ->leftJoin('client_user', 'clients.id', '=', 'client_user.client_id')
            ->where('client_user.user_id', '=', $user->id)
            ->groupBy('clients.id');
    }
}