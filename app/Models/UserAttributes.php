<?php

namespace App\Models;

use Carbon\Carbon;

trait UserAttributes
{
    /**
     * Set a publicily accessible identifier to get the path for this unique instance.
     * 
     * @return  string
     */
    public function getPathAttribute()
    {
        return url('/').'/users/'.$this->id;
    }

    /**
     * Set a publicily accessible identifier to get the last login for this unique instance.
     * 
     * @return  string
     */
    public function getLastLoginAttribute()
    {
        if(empty($this->attributes['last_login']))
        {
            return '';
        }

        return Carbon::parse($this->attributes['last_login'])->diffForHumans();
    }

    /**
     * Set a publicily accessible identifier to get the name for this unique instance.
     * 
     * @return  string
     */
    public function getNameAttribute()
    {
        return $this->attributes['first_name'] . ' ' . $this->attributes['last_name'];
    }
}