<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Signature extends Model
{
    use SoftDeletes;

    /**
     * The database table name.
     *
     * @return string
     */
    protected $table = 'signatures';

    /**
     * Mass-assign fields.
     *
     * @return array
     */
    protected $fillable = ['publish', 'name', 'email', 'city_id', 'country_id'];

    /**
     * @todo: Implement docblock.
     */
    public function cityRel()
    {
        return $this->belongsTo('Cities', 'city_id');
    }

    /**
     * Return the country information through the relation. 
     * 
     * @return belongsTo instance. 
     */
    public function countryRel()
    {
        return $this->belongsTo('Countries', 'country_id');
    }
}
