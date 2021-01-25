<?php

namespace App\Model;


class User {


    public function insertUser($user){
        \dibi::query('INSERT INTO [users]', $user);
    }

    public function checkUsername($username){
        return \dibi::query('SELECT `id` FROM [users] WHERE `username` LIKE %s', $username)->fetch();
    }

    public function getUsername($id){
        return \dibi::query("SELECT `username` FROM [users] WHERE `id` = %i", $id)->fetchSingle();
    }

    public function getEmail($id){
        return \dibi::query("SELECT `email` FROM [users] WHERE `id` = %i", $id)->fetchSingle();
    }

    public function getPermission($username)
    {
        return \dibi::query("SELECT permission_id FROM [users] WHERE username=?",$username)->fetchSingle();
    }

    public function getPasswordHash($id){
        return \dibi::query("SELECT `password` FROM [users] WHERE `id` = %i", $id)->fetchSingle();
    }


    public function getUserByUsername($username){
        return \dibi::query("SELECT `id`, `username`, `password` FROM [users] WHERE `username` LIKE %s", $username)->fetch();
    }

    public function getUsersByUsername($username){
        return \dibi::query("SELECT `id`, `email`, `username`  FROM [users] WHERE `username` LIKE %~like~", $username)->fetchAll();
    }

}