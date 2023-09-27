<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Soved\Laravel\Gdpr\Portable;
use Soved\Laravel\Gdpr\Contracts\Portable as PortableContract;
use Soved\Laravel\Gdpr\EncryptsAttributes;
use Soved\Laravel\Gdpr\Retentionable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Validator;

class User extends Authenticatable implements PortableContract
{
    use HasApiTokens, HasFactory, Notifiable, Portable; 
    use EncryptsAttributes, Retentionable;
    use HasRoles;
    use UserAttributes, UserScopes;

    /**
     * The attributes that should be visible in the downloadable data.
     *
     * @var array
     */
    protected $gdprVisible = [
        'first_name', 
        'last_name', 
        'email',
        'created_at',
    ];
    
    /**
     * The attributes that should be encrypted and decrypted on the fly.
     *
     * @var array
     */
    protected $encrypted = [];

    /**
     * The relations to include in the downloadable data.
     *
     * @var array
     */
    protected $gdprWith = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_created',
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'contact_number',
        'street_name',
        'building_number',
        'city',
        'postcode',
        'last_login'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get clients that belong to an instance of this model.
     * 
     * @return  \Illuminate\Support\Collection
     */
    public function getUserClients()
    {
        $userClients = DB::table('client_user')
            ->select('client_id', 'user_id')
            ->where('user_id', $this->id)
            ->get();

        return $userClients;
    }

    /**
     * Gets a list of users that are also assigned to clients that are linked to an instance of this model.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getClientUsers()
    {
        /** get client ids user is assigned to */
        $userClients = $this->getUserClients();

        /** store client ids */
        $clientIds = array();

        foreach($userClients as $userClient)
        {
            array_push($clientIds, $userClient->client_id);
        }

        /** query client users again to get all user ids except user currently logged in */
        $clientUsers = DB::table('client_user')->where('user_id', '!=', $this->id)->get();

        /** get unique users assigned to clients */
        $userIds = array();

        foreach($clientUsers as $clientUser)
        {
            if(! in_array($clientUser->user_id, $userIds))
            {
                array_push($userIds, $clientUser->user_id);
            }
        }

        $clientUsers = User::whereIn('id', $userIds)->orderBy('first_name', 'ASC')->get();

        return $clientUsers;
    }

    /**
     * Returns a list of clients assigned to an instance of this model.
     * 
     * @return  \Illuminate\Support\Collection
     */
    public function getClientsAssigned()
    {
        $userClients = DB::table('client_user')->select('client_id', 'user_id')->where('user_id', $this->id)->get();

        $clients = DB::table('clients')->select('id', 'first_name', 'last_name','company');

        $clientKeys = array();

        foreach($userClients as $cu)
        {
            array_push($clientKeys, $cu->client_id);
        }

        $clients = $clients->whereIn('id', $clientKeys)->get();

        return $clients;
    }

    /**
     * Finds whether an instance of this model is assigned to a given client id.
     * 
     * @param  \App\Models\Client  $clientId
     * @return bool
     */
    public function isClientAssigned($clientId)
    {
        $clientUser = DB::table('client_user')
            ->select('id')
            ->where(['user_id'=>$this->id, 'client_id'=>$clientId])->get();

        return !$clientUser->isEmpty() ? TRUE : FALSE;
    }

    /**
     * Returns option values for html input select tags for edit users request.
     * 
     * @param  \App\Models\User  $user
     * @return string
     */
    public function getEditClientOptions($user)
    {
        $options = "";
        $clientAssignedIds = [];

        $givenUserClients = Client::getAccessibleClients($user)->get();

        foreach($givenUserClients as $userClient)
        {
            $clientAssignedIds[] = $userClient->id;
        }

        $authUserClients = Client::getAccessibleClients(auth()->user())
            ->orderBy('clients.company', 'ASC')
            ->get();

        foreach($authUserClients as $client)
        {
            if(in_array($client->id, $clientAssignedIds))
            {
                $options .= "<option selected value='".$client->id."'>".$client->company."</option>";
            }
            else
            {
                $options .= "<option value='".$client->id."'>".$client->company."</option>";
            }
        }

        return $options;
    }
    
    /**
     * Update an instance of this resource.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    /*
    public function updateUser($request)
    {
        $this->first_name = !empty($request->input('first_name')) ? trim(filter_var($request->input('first_name'), FILTER_SANITIZE_STRING)) : NULL;
        $this->last_name = !empty($request->input('last_name')) ? trim(filter_var($request->input('last_name'), FILTER_SANITIZE_STRING)) : NULL;
        $this->email = trim(filter_var($request->input('email'), FILTER_SANITIZE_EMAIL));
        $this->building_number = $request->input('building_number') !== NULL ? trim(filter_var($request->input('building_number'), FILTER_SANITIZE_STRING)) : NULL;
        $this->street_address = $request->input('street_name') !== NULL ? trim(filter_var($request->input('street_name'), FILTER_SANITIZE_STRING)) : NULL;
        $this->postcode = $request->input('postcode') !== NULL ? trim(filter_var($request->input('postcode'), FILTER_SANITIZE_STRING)) : NULL;
        $this->city = $request->input('city') !== NULL ? trim(filter_var($request->input('city'), FILTER_SANITIZE_STRING)) : NULL;
        $this->contact_number = $request->input('contact_number') !== NULL ? trim(filter_var($request->input('contact_number'), FILTER_SANITIZE_STRING)) : NULL;
        
        return $this->save() ? TRUE : FALSE;
    }
    */

    /**
     *  Get errors for store request of this resource.
     *
     *  @param  array  $data
     *  @param  \App\Models\User $user
     *  @return \Illuminate\Support\MessageBag
     */
    public static function getStoreErrors($data, $user)
    {
        $errors = [];

        if(isset($data['client_ids']))
        {
            $hasAccessToClients = Client::hasAccessToClients($data['client_ids'], $user);
        }

        if(!isset($data['client_ids']) || !$hasAccessToClients)
        {
            $errors[] = 'No client selected.';
        }

        $role = strtolower(request('role'));
        $permissionToAssignRole = true;

        switch($role)
        {
            case 'admin':
            case 'client admin':
                if(!$user->hasRole('admin'))
                {
                    $permissionToAssignRole = false;
                }
            break;
            case 'client user':
            break;
            default:
                $permissionToAssignRole = false;
            break;
        }

        if(empty($data['role']) || !$permissionToAssignRole)
        {
            $errors[] = 'No Role selected';
        }

        $validator = Validator::make($data, [
            'first_name' => 'required|max:191|min:3',
            'last_name' => 'required|max:191|min:3',
            'email' => 'required|email|unique:users|max:191|min:3',
            'password' => 'required|confirmed',
        ]);

        return $validator->messages()->merge($errors);
    }

    /**
     *  Get store data
     *
     *  @param  \Illuminate\Http\Request  $request
     *  @return array
     */
    public static function getStoreData($request)
    {
        return [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'password_confirmation' => $request->input('password_confirmation'),
            'client_ids' => $request->input('clients'),
            'role' => $request->input('role'),
        ];
    }

    /**
     *  Sanitize store data
     *
     *  @param  array $data
     *  @return array
     */
    public static function cleanStoreData($data)
    {
        $clientIds = array();
        for($i=0;$i<count($data['client_ids']);$i++)
        {
            $id = $data['client_ids'][$i];
            $clientIds[] = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        }

        return [
            'first_name' => filter_var($data['first_name'], FILTER_SANITIZE_STRING),
            'last_name' => filter_var($data['last_name'], FILTER_SANITIZE_STRING),
            'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            'password' => $data['password'],
            'password_confirmation' => $data['password_confirmation'],
            'client_ids' => $clientIds,
            'role' => filter_var($data['role'], FILTER_SANITIZE_STRING),
        ];
    }

    /**
     *  Create db instance of this model.
     *
     *  @param  array $data
     *  @param  \App\Models\User $user
     *  @return \App\Models\User
     */
    public function createUser($data, $user)
    {
        $data['username'] = Str::slug($data['first_name'].' '.$data['last_name'], '-');
        $existingUsernames = User::where('username', $data['username'])->first();

        if($existingUsernames !== null)
        {
            $param = str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
            $data['username'] .= substr($param, 0, 4);
        }

        $createdUser = User::create([
            'user_created' => $user->id,
            'username' => $data['username'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        // assign user to clients
        foreach($data['client_ids'] as $id)
        {
            DB::table('client_user')->insert([
                'user_id' => $createdUser->id,
                'client_id' => $id,
            ]);
        }

        // assign role
        switch($data['role'])
        {
            case 'admin':
                $createdUser->assignRole('admin');
            break;
            case 'client admin':
                $createdUser->assignRole('client_admin');
            break;
            case 'client user':
                $createdUser->assignRole('client_user');
            break;
        }

        return $createdUser;
    }

    /**
     *  Get errors for update request of this resource.
     *
     *  @param  array  $data
     *  @param  \App\Models\User $user
     *  @return \Illuminate\Support\MessageBag
     */
    public static function getUpdateErrors($data, $user)
    {
        $errors = new MessageBag;

        if(isset($data['client_ids']))
        {
            $hasAccessToClients = Client::hasAccessToClients($data['client_ids'], $user);
        }

        if(!isset($data['client_ids']) || !$hasAccessToClients)
        {
            $errors->add('client_ids', 'No client selected.');
        }

        return $errors;
    }

    /**
     *  Get update data
     *
     *  @param  \Illuminate\Http\Request  $request
     *  @return array
     */
    public static function getUpdateData($request)
    {
        return [
            'client_ids' => $request->input('clients'),
        ];
    }

    /**
     *  Sanitize update data
     *
     *  @param  array $data
     *  @return array
     */
    public static function cleanUpdateData($data)
    {
        $clientIds = array();
        for($i=0;$i<count($data['client_ids']);$i++)
        {
            $id = $data['client_ids'][$i];
            $clientIds[] = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        }

        return [
            'client_ids' => $clientIds,
        ];
    }

    /**
     *  Create db instance of this model.
     *
     *  @param array $data
     */
    public function updateUser($data)
    {
        // assign user to clients
        foreach($data['client_ids'] as $id)
        {
            DB::table('client_user')->insert([
                'user_id' => $this->id,
                'client_id' => $id,
            ]);
        }
    }
}
