@extends('layout')
@section('content')
    <div class="container box">
        <div class="form-group">
            <label for="city_name">Enter City Name</label>
            <div id="errors" style="color: red;"></div>
            <input type="text" class="form-control" id="city_name" name="city_name" placeholder="Search City">
            <div id="cityList"></div>
        </div>
        {{ csrf_field() }}
    </div>

    <div id="map"></div>
@stop



