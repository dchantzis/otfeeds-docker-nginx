<?php

namespace App\Models;

use App\Traits\ModelGetTableNameTrait;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{

    use ModelGetTableNameTrait;

    protected $table = 'currencies';

    public $timestamps = false;

}
