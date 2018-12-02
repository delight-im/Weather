# Weather

Current weather data and hourly weather forecasts

 * Temperature (°C)
 * Humidity
   * Relative (%)
   * Absolute (g/m³)
 * Dew point (°C)
 * Cloudiness (%)
 * Wind
   * Direction
   * Speed (km/h)
 * Precipitation
   * Probability (%)
   * Intensity (mm/h)
 * Airing
   * Relative humidity indoors after airing
 * UV index
 * Visibility (km)

## Requirements

 * PHP 7.0.0+

## Installation

 1. Choose a data provider

    * [Dark Sky](https://darksky.net/dev) (recommended)
    * or
    * [OpenWeatherMap](https://openweathermap.org/api)

 1. Go to the data provider’s website and sign up for an API key, which they might also call “secret key” or “App ID”

 1. Create a new configuration file from the provided example

    ```bash
    $ cp src/config.example.php src/config.php
    # sudo chown www-data:www-data src/config.php
    # sudo chmod 0400 src/config.php
    ```

 1. Add the API key from your data provider in the configuration file

## Usage

 1. Find the geographic latitude and longitude of your location, e.g. using [Nominatim](https://nominatim.openstreetmap.org/) or [GeoNames](https://www.geonames.org/)

 1. Navigate your browser to

    ```
    http://localhost/Weather/src/weather.php?latitude=51.507222&longitude=-0.1275
    ```

    or

    ```
    http://localhost/Weather/src/weather.php?latitude=30.016667&longitude=31.216667&roomTemperature=23
    ```

    or

    ```
    http://localhost/Weather/src/weather.php?latitude=30.016667&longitude=31.216667&provider=openweathermap
    ```

    or

    ```
    http://localhost/Weather/src/weather.php?latitude=51.507222&longitude=-0.1275&roomTemperature=20.5&provider=darksky
    ```

    where

    * `latitude` (required) is the latitude of your location in decimal degrees (where positive values indicate north and negative values indicate south)
    * `longitude` (required) is the longitude of your location in decimal degrees (where positive values indicate east and negative values indicate west)
    * `roomTemperature` (optional) is the room temperature in °C for estimates on the relative humidity that is expected indoors after airing
    * `provider` (optional) is either `darksky` (recommended) or `openweathermap`

## Contributing

All contributions are welcome! If you wish to contribute, please create an issue first so that your feature, problem or question can be discussed.

## License

This project is licensed under the terms of the [MIT License](https://opensource.org/licenses/MIT).
