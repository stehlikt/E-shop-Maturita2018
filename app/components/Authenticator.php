<?php


namespace App\components;

use App\Model\Role;
use App\Model\User;
use Nette\Object;
use Nette\Security as NS;


class Authenticator extends Object implements NS\IAuthenticator
{

    function __construct()
    {

    }

    function authenticate(array $credentials)
    {

        $username = $credentials[0]['username'];
        $password = $credentials[0]['password'];


        $user = new User();

        $row = $user->getUserByUsername($username);

        if (!$row) {
            throw new NS\AuthenticationException('Neexistující uživatel.');
        }


        if (!NS\Passwords::verify($password, $row->password)) {
            throw new NS\AuthenticationException('Chybné přihlašovací jméno nebo heslo.');
        }

        return new NS\Identity($row->id,['username' => $row->username]);

    }
}