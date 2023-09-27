<?php

namespace App\Models;

trait LogRelations
{
    /**
     * This model relationship belongs to \App\Models\User.
     * 
     * @return  \Illuminate\Database\Eloquent\Model
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_created');
    }

    /**
     * This model relationship belongs to \App\Models\User.
     * 
     * @return  \Illuminate\Database\Eloquent\Model
     */
    public function userUpdated()
    {
        return $this->belongsTo('App\Models\User', 'user_modified');
    }

    /**
     * This model relationship belongs to \App\Models\Client.
     * 
     * @return  \Illuminate\Database\Eloquent\Model
     */
    public function client()
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
    }
}