<?php

namespace App;

class UserRepository
{
    public function __construct()
    {
        setcookie();
    }

    public function all()
    {
        return array_values($_COOKIES);
    }

    public function find($id)
    {
        return $_COOKIES[$id];
    }

    public function destroy($id)
    {
        unset($_COOKIES[$id]);
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
        $_COOKIES[$item['id']] = $item;
    }
}
