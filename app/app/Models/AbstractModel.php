<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use League\Flysystem\Config;

abstract class AbstractModel extends Model
{
    protected $table;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        $this->table = $this->generateTableName();

    }

    public function addDatabasePrefix($tableName)
    {
        if (App::runningUnitTests()) {
            return $tableName;
        }

        return sprintf('%s.%s', config('app.typo_database'), $tableName);
    }

    abstract protected function generateTableName();

}
