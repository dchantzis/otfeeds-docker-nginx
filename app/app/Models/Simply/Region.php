<?php


namespace App\Models\Simply;

use App\Models\AbstractModel;
use App\Traits\ModelGetTableNameTrait;

class Region extends AbstractModel
{

    use ModelGetTableNameTrait;

    protected $table = 'tx_simply_regions';

    protected $primaryKey = 'uid';

    public $timestamps = false;

    public function ParentRegion()
    {
        return $this->hasOne(
            Region::class,
            'uid',
            'parent_region'
        );
    }

    protected function generateTableName()
    {
        return $this->addDatabasePrefix($this->table);
    }

}
