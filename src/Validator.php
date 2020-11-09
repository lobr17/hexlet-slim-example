<?php

namespace App;

class Validator implements ValidatorInterface
{
    public function validate(array $course)
    {
        $errors = [];
        if (empty($course['name'])) {
            $errors['name'] = "Can't be blank";
        }

        if (empty($course['sex'])) {
            $errors['sex'] = "Can't be blank";
        }

        return $errors;
    }
}

