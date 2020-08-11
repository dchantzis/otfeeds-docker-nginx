<?php


namespace App\Models;

use App\Traits\ModelGetTableNameTrait;
use Illuminate\Database\Eloquent\Model;

class TaxBand extends Model
{
    use ModelGetTableNameTrait;

    protected $table = 'tax_bands';

    public $timestamps = false;
}
