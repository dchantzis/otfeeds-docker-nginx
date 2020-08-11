<?php


namespace App\Models\Simply;

use App\Models\AbstractModel;
use App\Traits\ModelGetTableNameTrait;

class DwellingFacility extends AbstractModel
{

    use ModelGetTableNameTrait;

    protected $table = 'tx_simply_dwellings_facility_mm';

    protected function generateTableName()
    {
        return $this->addDatabasePrefix($this->table);
    }

}
