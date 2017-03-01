<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Countries extends Model
{
    use SoftDeletes;

    protected $table = 'countries';

    /**
     *
     * @return 
     */
    protected $fillable = [];
}
