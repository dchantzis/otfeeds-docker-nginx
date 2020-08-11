<?php


namespace App\Traits;


trait Encryptable
{

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable)) {
            $value = customDecrypter($value);
        }

        return $value;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable)) {
            $value = customEncrypter($value);
        }

        return parent::setAttribute($key, $value);
    }

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray(); // call the parent method

        foreach ($this->encryptable as $key)
        {
            if (isset ($attributes[$key])) {
                $attributes[$key] = customDecrypter($attributes[$key]);
            }
        }

        return $attributes;
    }

}
