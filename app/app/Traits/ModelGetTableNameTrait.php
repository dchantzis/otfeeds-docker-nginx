<?php

namespace App\Traits;

trait ModelGetTableNameTrait
{

    protected static function getTableName()
    {
        return ((new self)->getTable());
    }

}
