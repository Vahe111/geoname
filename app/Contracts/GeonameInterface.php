<?php namespace App\Contracts;

interface GeonameInterface{

    /**
     * @param $query
     * @return mixed
     */
    public function getByAsciiname($query);

    /**
     * @param $geonameid
     * @return mixed
     */
    public function getLatLngByGeonameId($geonameid);

    /**
     * @param $latLng
     * @return mixed
     */
    public function getNearestCities($latLng);

}