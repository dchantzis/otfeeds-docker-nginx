<?php

namespace App\Models\Simply;

use App\Models\AbstractModel;
use App\Traits\ModelGetTableNameTrait;

class Terms extends AbstractModel
{
    use ModelGetTableNameTrait;

    protected $table = 'tx_simply_termsandconditions';

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
            $this->generateDwellingTermsPivotTableName(),
            Dwelling::getTable(),
            $this->table
        )->withPivot('details');
    }

    private function generateDwellingTermsPivotTableName()
    {
        return $this->addDatabasePrefix(DwellingTermsAndConditions::getTable());
    }

}
