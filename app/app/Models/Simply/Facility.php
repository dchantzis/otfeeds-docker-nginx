<?php


namespace App\Models\Simply;

use App\Models\AbstractModel;
use App\Traits\ModelGetTableNameTrait;

class Facility extends AbstractModel
{

    use ModelGetTableNameTrait;

    protected $table = 'tx_simply_facilities';

    protected $primaryKey = 'uid';

    public $timestamps = false;

    protected function generateTableName()
    {
        return $this->addDatabasePrefix($this->table);
    }

    public function Dwellings()
    {
        return $this->belongsToMany(
            AdditionalInformation::class,
            DwellingFacility::getTable(),
            'uid_local',
            'uid_foreign'
        );
    }

}
