<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\GeonameInterface;
use Illuminate\Support\Facades\Validator;

class GeonameController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        $googleApiKey = config('app.googleApiKey');
        return view('index', ['googleApiKey' => $googleApiKey]);
    }

    /**
     * @param Request $request
     * @param GeonameInterface $geonameRepo
     * @return mixed
     */
    public function search(Request $request, GeonameInterface $geonameRepo) {
        $validator = Validator::make($request->all(), [
            'query' => 'required|alpha',
            '_token' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error']);
        }
        $query = $request->get('query');
        $data = $geonameRepo->getByAsciiname($query);
        return $data;

    }

    /**
     * @param Request $request
     * @param GeonameInterface $geonameRepo
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchOne(Request $request, GeonameInterface $geonameRepo) {
        $validator = Validator::make($request->all(), [
            'query' => 'required|numeric',
            '_token' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error']);
        }
        $geonameId = $request->get('query');
        $latLng = $geonameRepo->getLatLngByGeonameId($geonameId);
        $cities = $geonameRepo->getNearestCities($latLng);
        foreach($cities as $city) {
            $city->latitude = +$city->latitude;
            $city->longitude = +$city->longitude;
        }
        return response()->json(['cities' => $cities]);
    }

}
