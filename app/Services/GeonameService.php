<?php namespace App\Services;

use App\Contracts\GeonameInterface;
use App\Geoname;
use Illuminate\Support\Facades\DB;

class GeonameService implements GeonameInterface
{

    /**
     * @var Geoname
     */
    private $geoname;


    /**
     * GeonameService constructor.
     */
    public function __construct()
    {
        $this->geoname = new Geoname();
    }

    /**
     * @param $query
     * @return mixed
     */
    public function getByAsciiname($query)
    {
        return $this->geoname->where('asciiname', 'LIKE', "$query%")->select('geonameid', 'asciiname')->get();
    }

    /**
     * @param $geonameid
     * @return mixed
     */
    public function getLatLngByGeonameId($geonameid)
    {
        return $this->geoname->where('geonameid', $geonameid)->select('latitude', 'longitude')->first();
    }

    /**
     * @param $latLng
     * @return mixed
     */
    public function getNearestCities($latLng)
    {
        return $this->geoname->select(DB::raw('*, ( 6367 * acos( cos( radians('.$latLng->latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$latLng->longitude.') ) + sin( radians('.$latLng->latitude.') ) * sin( radians( latitude ) ) ) ) AS distance'))
            ->where('feature_class', '=', 'P')->orderBy('distance')->take(20)->get();
    }


}