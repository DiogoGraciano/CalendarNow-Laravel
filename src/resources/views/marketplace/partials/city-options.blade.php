<option value="">{{ __('marketplace.allCities') }}</option>
@foreach($cities as $city)
    <option value="{{ $city }}">{{ $city }}</option>
@endforeach
