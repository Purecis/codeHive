<?php
namespace App\System;

abstract class Auth
{

    public static $current;
    public static $table = "users";
    public static $sessId = "userId";

    public static $fields = [
        'primary' => 'id',
        'token' => ['token'],
        'login' => ['email', 'name'],
        'password' => 'password',
        'permissions' => 'permissions'
    ];
    
    /**
     * login once
     *
     * @param  array ['login'=>'', 'password'=>'']
     * @return bool
     */
    public static function once()
    {
        $credentials = func_get_arg(0);

        $query = (new Query)
            ->table(static::$table)
            ->where(function ($e) use ($credentials) {
                $fields = static::$fields['login'];
                $e->where(array_shift($fields), $credentials['login']);
                foreach ($fields as $value) {
                    $e->orWhere($value, $credentials['login']);
                }
            })->where(static::$fields['password'], self::encpass($credentials['password']))
            ->get();

        if ($query->count) {
            static::$current = $query->record[0];
            return true;
        } else {
            static::$current = null;
            return false;
        }
    }

    /**
     * login once by id
     *
     * @param  int
     * @return bool
     */
    public static function onceById()
    {
        $id = func_get_arg(0);

        $query = (new Query)
            ->table(static::$table)
            ->where(static::$fields['primary'], $id)
            ->get();

        if ($query->count) {
            static::$current = $query->record[0];
            return true;
        } else {
            static::$current = null;
            return false;
        }
    }
    
    /**
     * login and save to session
     *
     * @param  array ['login'=>'', 'password'=>'']
     * @return bool
     */
    public static function login()
    {
        $credentials = func_get_arg(0);

        if (static::once($credentials)) {
            Session::set(static::$table.static::$sessId, static::$current->{static::$fields['primary']});
            return true;
        } else {
            static::logout();
            return false;
        }
    }
    
    /**
     * login by id and save to session
     *
     * @param  int
     * @return bool
     */
    public static function loginById()
    {
        $id = func_get_arg(0);
        if (static::onceById($id)) {
            Session::set(static::$table.static::$sessId, static::$current->{static::$fields['primary']});
            return true;
        } else {
            static::logout();
            return false;
        }
    }
    
    /**
     * get logged user data
     *
     * @return bool | object
     */
    public static function user()
    {
        if (static::logged() && !static::$current) {
            $query = (new Query)
            ->table(static::$table)
            ->where(static::$fields['primary'], static::id())
            ->get();

            if ($query->count) {
                static::$current = $query->record[0];
                return static::$current;
            } else {
                static::$current = null;
                return false;
            }
        }

        return static::$current;
    }
    
    /**
     * get logged user id or return zero
     *
     * @return bool | int
     */
    public static function id()
    {
        $id = Session::get(static::$table.static::$sessId);
        return $id ?: 0;
    }
    
    /**
     * logout and clear sessions
     *
     * @return void
     */
    public static function logout()
    {
        static::$current = null;
        Session::remove(static::$table.static::$sessId);
    }

    /**
     * check wheither logged or not
     *
     * @return bool
     */
    public static function logged()
    {
        return Session::exists(static::$table.static::$sessId);
    }

    /**
     * password encryption
     *
     * @param   string $password
     * @return  string
     */
    public static function encpass($pass)
    {
        return md5(Str::encrypt($pass)).sha1(Str::encrypt($pass));
    }
    
    /**
     * return kill screen, called when fail
     *
     * @return Response
     */
    public static function orFail()
    {
        return (new Response())->code(401)->body(['error'=>'Unauthorized']); // Unauthenticated when rules errors
    }
}
/*
    Ideas ..
    
    * JWT
    * register
    * update
    * exist
    * oauth
    * guard
    * user
    * id
    * token
    * profile
    * check, logged
    * middleware
    * attempt
    * remember
    * loginById(1);
    * onceById(1);
    * hasPermission with custom arguments saved
    * defaultPermission
        {
            'show-advanced-list' : "1,2,3,4",
            'show-advanced' : "*"
        }
*/
