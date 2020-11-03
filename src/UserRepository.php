<?php

namespace App;

class UserRepository
{
    public function __construct()
    {
        session_start();
    }

    public function all()
    {
        return array_values($_SESSION);
    }

    public function find(int $id)
    {
        return $_SESSION[$id];
    }

    public function save(array $item)
    {
        if (empty($item['title'])) {
            $json = json_encode($item);
            throw new \Exception("Wrong data: {$json}");
        }
        $item['id'] = uniqid();
        $_SESSION[$item['id']] = $item;
    }
}

