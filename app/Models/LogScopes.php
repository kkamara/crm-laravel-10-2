<?php

namespace App\Models;

trait LogScopes
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
                    
            if($request->has('title') || $request->has('desc') || $request->has('body') || 
                $request->has('created_at') || $request->has('updated_at'))
            {
                if($request->has('search'))
                {
                    if($request->has('title')) 
                    {
                        $query = $query->where('logs.title', 'like', '%'.$searchParam.'%');
                    }
                    if($request->has('desc')) 
                    {
                        $query = $query->where('logs.description', 'like', '%'.$searchParam.'%');
                    }
                    if($request->has('body')) 
                    {
                        $query = $query->where('logs.body', 'like', '%'.$searchParam.'%');
                    }
                    if($request->has('created_at')) 
                    {
                        $query = $query->whereDate('logs.created_at', 'like', '%'.$searchParam.'%');
                    }
                    if($request->has('updated_at')) 
                    {
                        $query = $query->whereDate('logs.updated_at', 'like', '%'.$searchParam.'%');
                    }
                }
            }
            else
            {
                $query = $query->where('logs.title', 'like', '%'.$searchParam.'%')
                    ->orWhere('logs.description', 'like', '%'.$searchParam.'%')
                    ->orWhere('logs.body', 'like', '%'.$searchParam.'%')
                    ->orWhere('logs.created_at', 'like', '%'.$searchParam.'%')
                    ->orWhere('logs.updated_at', 'like', '%'.$searchParam.'%');
            }
        }

        return $query;
    }

    /**
     *  Get logs available to a given user.
     *
     *  @param  \Illuminate\Database\Eloquent\Model  $query
     *  @param \App\Models\User $user
     *  @return \Illuminate\Database\Eloquent\Model
     */
    public function scopeGetAccessibleLogs($query, $user)
    {
        return $query->select(
                'logs.id', 'logs.slug', 'logs.title', 'logs.description', 'logs.created_at', 'logs.updated_at'
            )
            ->leftJoin('client_user', 'logs.client_id', '=', 'client_user.client_id')
            ->where('client_user.user_id', '=', $user->id)
            ->groupBy('logs.id');
    }
}