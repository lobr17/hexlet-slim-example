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

    public function find($id)
    {
        return $_SESSION[$id];
    }

    public function destroy($id)
    {
        unset($_SESSION[$id]);
    }

    public function save(array &$item)
    {
        if (empty($item['name']) || empty($item['sex'])) {
            $json = json_encode($item);
            throw new \Exception("Wrong data: {$json}");
        }
	if (!isset($item['id'])) {
	    $item['id'] = uniqid();
	}
        $_SESSION[$item['id']] = $item;
    }
}

