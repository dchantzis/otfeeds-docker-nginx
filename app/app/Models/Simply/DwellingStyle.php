<?php


namespace App\Models\Simply;

use App\Models\AbstractModel;
use App\Traits\ModelGetTableNameTrait;

class DwellingStyle extends AbstractModel
{

    use ModelGetTableNameTrait;

    protected $table = 'tx_simply_dwellingstyles';

    protected $primaryKey = 'uid';

    public $timestamps = false;

    protected function generateTableName()
    {
        return $this->addDatabasePrefix($this->table);
    }

}
