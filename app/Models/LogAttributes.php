<?php

namespace App\Models;

trait LogAttributes
{
    /**
     * Set a publicily accessible identifier to get the description for this unique instance.
     * 
     * @return  string
     */
    public function getDescriptionAttribute()
    {
        return nl2br(e($this->attributes['description']));
    }

    /**
     * Set a publicily accessible identifier to get the body for this unique instance.
     * 
     * @return  string
     */
    public function getBodyAttribute()
    {
        return nl2br(e($this->attributes['body']));
    }

    /**
     * Set a publicily accessible identifier to get the notes for this unique instance.
     * 
     * @return  string
     */
    public function getNotesAttribute()
    {
        return nl2br(e($this->attributes['notes']));
    }

    /**
     * Set a publicily accessible identifier to get the edit description for this unique instance.
     * 
     * @return  string
     */
    public function getEditDescriptionAttribute()
    {
        $desc = str_replace('<br/>', '', $this->attributes['description']);

        return e($desc);
    }

    /**
     * Set a publicily accessible identifier to get the edit body for this unique instance.
     * 
     * @return  string
     */
    public function getEditBodyAttribute()
    {
        $body = str_replace('<br/>', '', $this->attributes['body']);

        return e($body);
    }

    /**
     * Set a publicily accessible identifier to get the edit notes for this unique instance.
     * 
     * @return  string
     */
    public function getEditNotesAttribute()
    {
        $notes = str_replace('<br/>', '', $this->attributes['notes']);

        return e($notes);
    }

    /**
     * Set a publicily accessible identifier to get the short description for this unique instance.
     * 
     * @return  string
     */
    public function getShortDescriptionAttribute()
    {
        $desc = str_replace('<br/>', '', $this->attributes['description']);

        return strlen($desc) > 300 ? substr($desc,0,299).'...' : $desc;
    }
}