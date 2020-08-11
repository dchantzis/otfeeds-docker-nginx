<?php

namespace App\Models\Simply;

use App\Models\AbstractModel;
use App\Traits\ModelGetTableNameTrait;

class Extra extends AbstractModel
{

    use ModelGetTableNameTrait;

    protected $table = 'tx_simply_extras';

    protected $primaryKey = 'uid';

    public $timestamps = false;

    public function Dwellings()
    {
        return $this->belongsToMany(
            AdditionalInformation::class,
            $this->generateDwellingExtrasPivotTableName(),
            'uid_local',
            'uid_foreign'
        );
    }

    protected function generateTableName()
    {
        return $this->addDatabasePrefix($this->table);
    }

    private function generateDwellingExtrasPivotTableName()
    {
        return $this->addDatabasePrefix(DwellingExtra::getTable());
    }

}
