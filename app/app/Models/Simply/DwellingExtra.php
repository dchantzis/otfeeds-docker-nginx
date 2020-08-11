<?php


namespace App\Models\Simply;

use App\Models\AbstractModel;
use App\Traits\ModelGetTableNameTrait;

class DwellingExtra extends AbstractModel
{

    use ModelGetTableNameTrait;

    protected $table = 'tx_simply_dwellings_extras_mm';

    protected function generateTableName()
    {
        return $this->addDatabasePrefix($this->table);
    }

}
