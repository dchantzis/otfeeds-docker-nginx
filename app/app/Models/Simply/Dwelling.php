<?php


namespace App\Models\Simply;

use App\Models\AbstractModel;
use App\Traits\ModelGetTableNameTrait;

class Dwelling extends AbstractModel
{

    use ModelGetTableNameTrait;

    protected $table = 'tx_simply_dwellings';

    protected function generateTableName()
    {
        return $this->addDatabasePrefix($this->table);
    }

}
