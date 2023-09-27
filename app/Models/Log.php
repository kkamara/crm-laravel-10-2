<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Validator;

class Log extends Model
{
    use HasFactory;
    use LogAttributes;

    /** 
     * This models immutable values.
     *
     * @var array 
     */
    protected $guarded = [];

    /**
     * Set a publicily accessible identifier to get the path for this unique instance.
     * 
     * @return  string
     */
    public function getPathAttribute()
    {
        return url('/').'/logs/'.$this->slug;
    }

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

    /**
     *  Get store data
     *
     *  @param  \Illuminate\Http\Request  $request
     *  @return array
     */
    public static function getStoreData($request, $user)
    {
        return [
            'client_id' => $request->input('client_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'body' => $request->input('body'),
            'notes' => $request->input('notes'),
            'user_created' => $user->id,
        ];
    }

    /**
     *  Get errors for store request of this resource.
     *
     *  @param  array  $data
     *  @return \Illuminate\Support\MessageBag
     */
    public static function getStoreErrors($data, $user)
    {
        $errors = [];

        $validator = Validator::make($data, [
            'client_id' => 'integer|not_in:0',
            'title' => 'required|max:191|min:3',
            'description'  => 'required|min:10',
            'body'  => 'required|min:10',
            'notes' => 'max:1000',
        ]);

        // if(!$user->isClientAssigned($data['client_id']))
        if(!Client::getAccessibleClients($user)
            ->where('clients.id', '=', $data['client_id'])
            ->first())
        {
            $errors[] = 'No client selected.';
        }

        return $validator->messages()->merge($errors);
    }

    /**
     *  Create an db instance of this resource.
     *
     *  @param array $data
     */
    public static function createLog($data)
    {
        return self::create([
            'slug' => strtolower(str_slug($data['title'], '-')),
            'client_id' => $data['client_id'],
            'user_created' => $data['user_created'],
            'title' => $data['title'],
            'description' => $data['description'],
            'body' => $data['body'],
            'notes' => $data['notes'],
        ]);
    }

    /**
     *  Sanitize create data
     *
     *  @param  array $data
     *  @return array
     */
    public static function cleanStoreData($data)
    {
        return [
            'client_id' => filter_var($data['client_id'], FILTER_SANITIZE_NUMBER_INT),
            'title' => filter_var($data['title'], FILTER_SANITIZE_STRING),
            'description' => filter_var($data['description'], FILTER_SANITIZE_STRING),
            'body' => filter_var($data['body'], FILTER_SANITIZE_STRING),
            'notes' => filter_var($data['notes'], FILTER_SANITIZE_STRING),
            'user_created' => $data['user_created'],
        ];
    }

    /**
     *  Get log data

     *  @param \Illuminate\Http\Request $request
     *  @return array
     */
    public static function getUpdateData($request)
    {
        return [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'body' => $request->input('body'),
            'notes' => $request->input('notes'),
        ];
    }

    /**
     *  Get update errors for this resource.
     *
     *  @param \Illuminate\Http\Request $request
     *  @return \Illuminate\Support\MessageBag
     */
    public static function getUpdateErrors($data)
    {
        $validator = Validator::make($data, [
            'title' => 'required|max:191|min:5',
            'description'  => 'required|min:20',
            'body'  => 'required|min:20'
        ]);

        return $validator->messages();
    }

    /**
     *  Sanitize update data
     *
     *  @param array $raw
     *  @return array
     */
    public static function cleanUpdateData($raw)
    {
        return [
            'title' => filter_var($raw['title'], FILTER_SANITIZE_STRING),
            'description' => filter_var($raw['description'], FILTER_SANITIZE_STRING),
            'body' => filter_var($raw['body'], FILTER_SANITIZE_STRING),
            'notes' => filter_var($raw['notes'], FILTER_SANITIZE_STRING),
        ];
    }

    /**
     *  Create db instance of this resource
     *
     *  @param  array $data
     *  @param  \App\Models\User $user 
     *  @return Log
     */
    public function updateLog($data, $user)
    {
        $data['user_modified'] = $user->id;

        $this->update($data);

        return $this;
    }
}
