<?php

/*
 * Weather (https://github.com/delight-im/Weather)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

\header('Content-Type: text/html; charset=utf-8');

\error_reporting(\E_ALL);
\ini_set('display_errors', 'stdout');

require __DIR__ . '/config.php';

\define('API_URL_DARK_SKY', 'https://api.darksky.net/forecast/%1$s/%2$s,%3$s?exclude=minutely,daily,alerts,flags&lang=en&units=ca');
\define('API_URL_OPEN_WEATHER_MAP_CURRENT', 'https://api.openweathermap.org/data/2.5/weather?lat=%2$s&lon=%3$s&appid=%1$s&units=metric&lang=en');
\define('API_URL_OPEN_WEATHER_MAP_FORECAST', 'https://api.openweathermap.org/data/2.5/forecast?lat=%2$s&lon=%3$s&appid=%1$s&units=metric&lang=en');
\define('LATITUDE_DEFAULT', 51.507222);
\define('LONGITUDE_DEFAULT', -0.1275);
\define('PROVIDER_DARK_SKY', 'darksky');
\define('PROVIDER_OPEN_WEATHER_MAP', 'openweathermap');
\define('PROVIDER_DEFAULT', \PROVIDER_DARK_SKY);
\define('ROOM_TEMPERATURE_DEFAULT', 21);

$provider = isset($_GET['provider']) ? (string) $_GET['provider'] : \PROVIDER_DEFAULT;
$latitude = isset($_GET['latitude']) && \is_numeric($_GET['latitude']) ? (float) $_GET['latitude'] : \LATITUDE_DEFAULT;
$longitude = isset($_GET['longitude']) && \is_numeric($_GET['longitude']) ? (float) $_GET['longitude'] : \LONGITUDE_DEFAULT;
$roomTemperature = isset($_GET['roomTemperature']) && \is_numeric($_GET['roomTemperature']) ? (float) $_GET['roomTemperature'] : \ROOM_TEMPERATURE_DEFAULT;

$normalized = [];

if ($provider === \PROVIDER_DARK_SKY) {
	$url = \sprintf(\API_URL_DARK_SKY, \API_KEY_DARK_SKY, $latitude, $longitude);
	$json = @\file_get_contents($url);

	if (!empty($json)) {
		$response = \json_decode($json, true);

		if (!empty($response)) {
			$normalized['latitude'] = isset($response['latitude']) ? (float) $response['latitude'] : null;
			$normalized['longitude'] = isset($response['longitude']) ? (float) $response['longitude'] : null;
			$normalized['timezone'] = !empty($response['timezone']) ? (string) $response['timezone'] : null;

			$normalized['current'] = [];
			$normalized['current']['timestamp'] = !empty($response['currently']['time']) ? (int) $response['currently']['time'] : null;
			$normalized['current']['temperature'] = [];
			$normalized['current']['temperature']['air'] = isset($response['currently']['temperature']) ? (float) $response['currently']['temperature'] : null;
			$normalized['current']['temperature']['apparent'] = isset($response['currently']['apparentTemperature']) ? (float) $response['currently']['apparentTemperature'] : null;
			$normalized['current']['humidity'] = [];
			$normalized['current']['humidity']['relative'] = isset($response['currently']['humidity']) ? (float) $response['currently']['humidity'] : null;
			$normalized['current']['humidity']['absolute'] = \AbsoluteHumidity::fromTemperatureAndRelativeHumidity($normalized['current']['temperature']['air'], $normalized['current']['humidity']['relative']);
			$normalized['current']['dewPoint'] = isset($response['currently']['dewPoint']) ? (float) $response['currently']['dewPoint'] : \DewPoint::fromTemperatureAndRelativeHumidity($normalized['current']['temperature']['air'], $normalized['current']['humidity']['relative']);
			$normalized['current']['cloudiness'] = isset($response['currently']['cloudCover']) ? (float) $response['currently']['cloudCover'] : null;
			$normalized['current']['wind'] = [];
			$normalized['current']['wind']['direction'] = isset($response['currently']['windBearing']) ? (float) $response['currently']['windBearing'] : null;
			$normalized['current']['wind']['speed'] = isset($response['currently']['windSpeed']) ? (float) $response['currently']['windSpeed'] : null;
			$normalized['current']['wind']['symbol'] = \Wind::toSymbol($normalized['current']['wind']['direction']);
			$normalized['current']['precipitation'] = [];
			$normalized['current']['precipitation']['probability'] = isset($response['currently']['precipProbability']) ? (float) $response['currently']['precipProbability'] : null;
			$normalized['current']['precipitation']['intensity'] = isset($response['currently']['precipIntensity']) ? (float) $response['currently']['precipIntensity'] : null;
			$normalized['current']['uvIndex'] = [];
			$normalized['current']['uvIndex']['value'] = isset($response['currently']['uvIndex']) ? (float) $response['currently']['uvIndex'] : null;
			$normalized['current']['uvIndex']['label'] = \UvIndex::toLabel($normalized['current']['uvIndex']['value']);
			$normalized['current']['visibility'] = isset($response['currently']['visibility']) ? (float) $response['currently']['visibility'] : null;
			$normalized['current']['pressure'] = isset($response['currently']['pressure']) ? (float) $response['currently']['pressure'] : null;
			$normalized['current']['now'] = true;
			$normalized['current']['future'] = false;

			$normalized['forecast'] = [];

			if (!empty($response['hourly']) && !empty($response['hourly']['data']) && \is_array($response['hourly']['data'])) {
				$f = 0;

				foreach ($response['hourly']['data'] as $forecast) {
					$normalized['forecast'][$f] = [];
					$normalized['forecast'][$f]['timestamp'] = !empty($forecast['time']) ? (int) $forecast['time'] : null;
					$normalized['forecast'][$f]['temperature'] = [];
					$normalized['forecast'][$f]['temperature']['air'] = isset($forecast['temperature']) ? (float) $forecast['temperature'] : null;
					$normalized['forecast'][$f]['temperature']['apparent'] = isset($forecast['apparentTemperature']) ? (float) $forecast['apparentTemperature'] : null;
					$normalized['forecast'][$f]['humidity'] = [];
					$normalized['forecast'][$f]['humidity']['relative'] = isset($forecast['humidity']) ? (float) $forecast['humidity'] : null;
					$normalized['forecast'][$f]['humidity']['absolute'] = \AbsoluteHumidity::fromTemperatureAndRelativeHumidity($normalized['forecast'][$f]['temperature']['air'], $normalized['forecast'][$f]['humidity']['relative']);
					$normalized['forecast'][$f]['dewPoint'] = isset($forecast['dewPoint']) ? (float) $forecast['dewPoint'] : \DewPoint::fromTemperatureAndRelativeHumidity($normalized['forecast'][$f]['temperature']['air'], $normalized['forecast'][$f]['humidity']['relative']);
					$normalized['forecast'][$f]['cloudiness'] = isset($forecast['cloudCover']) ? (float) $forecast['cloudCover'] : null;
					$normalized['forecast'][$f]['wind'] = [];
					$normalized['forecast'][$f]['wind']['direction'] = isset($forecast['windBearing']) ? (float) $forecast['windBearing'] : null;
					$normalized['forecast'][$f]['wind']['speed'] = isset($forecast['windSpeed']) ? (float) $forecast['windSpeed'] : null;
					$normalized['forecast'][$f]['wind']['symbol'] = \Wind::toSymbol($normalized['forecast'][$f]['wind']['direction']);
					$normalized['forecast'][$f]['precipitation'] = [];
					$normalized['forecast'][$f]['precipitation']['probability'] = isset($forecast['precipProbability']) ? (float) $forecast['precipProbability'] : null;
					$normalized['forecast'][$f]['precipitation']['intensity'] = isset($forecast['precipIntensity']) ? (float) $forecast['precipIntensity'] : null;
					$normalized['forecast'][$f]['uvIndex'] = [];
					$normalized['forecast'][$f]['uvIndex']['value'] = isset($forecast['uvIndex']) ? (float) $forecast['uvIndex'] : null;
					$normalized['forecast'][$f]['uvIndex']['label'] = \UvIndex::toLabel($normalized['forecast'][$f]['uvIndex']['value']);
					$normalized['forecast'][$f]['visibility'] = isset($forecast['visibility']) ? (float) $forecast['visibility'] : null;
					$normalized['forecast'][$f]['pressure'] = isset($forecast['pressure']) ? (float) $forecast['pressure'] : null;
					$normalized['forecast'][$f]['now'] = false;
					$normalized['forecast'][$f]['future'] = true;

					$f++;
				}
			}
			else {
				echo 'No forecast data received from “' . $provider . '”';
				exit(4);
			}
		}
		else {
			echo 'Invalid data received from “' . $provider . '”';
			exit(3);
		}
	}
	else {
		echo 'Could not fetch data from “' . $provider . '”';
		exit(2);
	}
}
elseif ($provider === \PROVIDER_OPEN_WEATHER_MAP) {
	$url = \sprintf(\API_URL_OPEN_WEATHER_MAP_CURRENT, \API_KEY_OPEN_WEATHER_MAP, $latitude, $longitude);
	$json = @\file_get_contents($url);

	if (!empty($json)) {
		$response = \json_decode($json, true);

		if (!empty($response)) {
			$normalized['latitude'] = isset($response['coord']['lat']) ? (float) $response['coord']['lat'] : null;
			$normalized['longitude'] = isset($response['coord']['lon']) ? (float) $response['coord']['lon'] : null;
			$normalized['timezone'] = null;

			$normalized['current'] = [];
			$normalized['current']['timestamp'] = !empty($response['dt']) ? (int) $response['dt'] : null;
			$normalized['current']['temperature'] = [];
			$normalized['current']['temperature']['air'] = isset($response['main']['temp']) ? (float) $response['main']['temp'] : null;
			$normalized['current']['temperature']['apparent'] = null;
			$normalized['current']['humidity'] = [];
			$normalized['current']['humidity']['relative'] = isset($response['main']['humidity']) ? ((float) $response['main']['humidity']) / 100 : null;
			$normalized['current']['humidity']['absolute'] = \AbsoluteHumidity::fromTemperatureAndRelativeHumidity($normalized['current']['temperature']['air'], $normalized['current']['humidity']['relative']);
			$normalized['current']['dewPoint'] = \DewPoint::fromTemperatureAndRelativeHumidity($normalized['current']['temperature']['air'], $normalized['current']['humidity']['relative']);
			$normalized['current']['cloudiness'] = isset($response['clouds']['all']) ? ((float) $response['clouds']['all']) / 100 : null;
			$normalized['current']['wind'] = [];
			$normalized['current']['wind']['direction'] = isset($response['wind']['deg']) ? (float) $response['wind']['deg'] : null;
			$normalized['current']['wind']['speed'] = isset($response['wind']['speed']) ? (float) $response['wind']['speed'] : null;
			$normalized['current']['wind']['symbol'] = \Wind::toSymbol($normalized['current']['wind']['direction']);
			$normalized['current']['precipitation'] = [];
			$normalized['current']['precipitation']['probability'] = null;
			$normalized['current']['precipitation']['intensity'] = null;
			$normalized['current']['uvIndex'] = [];
			$normalized['current']['uvIndex']['value'] = null;
			$normalized['current']['uvIndex']['label'] = \UvIndex::toLabel($normalized['current']['uvIndex']['value']);
			$normalized['current']['visibility'] = isset($response['visibility']) ? ((float) $response['visibility']) / 1000 : null;
			$normalized['current']['pressure'] = isset($response['main']['pressure']) ? (float) $response['main']['pressure'] : null;
			$normalized['current']['now'] = true;
			$normalized['current']['future'] = false;

			$normalized['forecast'] = [];

			$url = \sprintf(\API_URL_OPEN_WEATHER_MAP_FORECAST, \API_KEY_OPEN_WEATHER_MAP, $latitude, $longitude);
			$json = @\file_get_contents($url);

			if (!empty($json)) {
				$response = \json_decode($json, true);

				if (!empty($response)) {
					if (!empty($response['list']) && \is_array($response['list'])) {
						$f = 0;

						foreach ($response['list'] as $forecast) {
							$normalized['forecast'][$f] = [];
							$normalized['forecast'][$f]['timestamp'] = !empty($forecast['dt']) ? (int) $forecast['dt'] : null;
							$normalized['forecast'][$f]['temperature'] = [];
							$normalized['forecast'][$f]['temperature']['air'] = isset($forecast['main']['temp']) ? (float) $forecast['main']['temp'] : null;
							$normalized['forecast'][$f]['temperature']['apparent'] = null;
							$normalized['forecast'][$f]['humidity'] = [];
							$normalized['forecast'][$f]['humidity']['relative'] = isset($forecast['main']['humidity']) ? ((float) $forecast['main']['humidity']) / 100 : null;
							$normalized['forecast'][$f]['humidity']['absolute'] = \AbsoluteHumidity::fromTemperatureAndRelativeHumidity($normalized['forecast'][$f]['temperature']['air'], $normalized['forecast'][$f]['humidity']['relative']);
							$normalized['forecast'][$f]['dewPoint'] = \DewPoint::fromTemperatureAndRelativeHumidity($normalized['forecast'][$f]['temperature']['air'], $normalized['forecast'][$f]['humidity']['relative']);
							$normalized['forecast'][$f]['cloudiness'] = isset($forecast['clouds']['all']) ? ((float) $forecast['clouds']['all']) / 100 : null;
							$normalized['forecast'][$f]['wind'] = [];
							$normalized['forecast'][$f]['wind']['direction'] = isset($forecast['wind']['deg']) ? (float) $forecast['wind']['deg'] : null;
							$normalized['forecast'][$f]['wind']['speed'] = isset($forecast['wind']['speed']) ? (float) $forecast['wind']['speed'] : null;
							$normalized['forecast'][$f]['wind']['symbol'] = \Wind::toSymbol($normalized['forecast'][$f]['wind']['direction']);
							$normalized['forecast'][$f]['precipitation'] = [];
							$normalized['forecast'][$f]['precipitation']['probability'] = null;
							$normalized['forecast'][$f]['precipitation']['intensity'] = null;
							$normalized['forecast'][$f]['uvIndex'] = [];
							$normalized['forecast'][$f]['uvIndex']['value'] = null;
							$normalized['forecast'][$f]['uvIndex']['label'] = \UvIndex::toLabel($normalized['forecast'][$f]['uvIndex']['value']);
							$normalized['forecast'][$f]['visibility'] = null;
							$normalized['forecast'][$f]['pressure'] = isset($forecast['main']['pressure']) ? (float) $forecast['main']['pressure'] : null;
							$normalized['forecast'][$f]['now'] = false;
							$normalized['forecast'][$f]['future'] = true;

							$f++;
						}
					}
					else {
						echo 'No forecast data received from “' . $provider . '”';
						exit(9);
					}
				}
				else {
					echo 'Invalid forecast data received from “' . $provider . '”';
					exit(8);
				}
			}
			else {
				echo 'Could not fetch forecast data from “' . $provider . '”';
				exit(7);
			}
		}
		else {
			echo 'Invalid current data received from “' . $provider . '”';
			exit(6);
		}
	}
	else {
		echo 'Could not fetch current data from “' . $provider . '”';
		exit(5);
	}
}
else {
	echo 'Unknown provider “' . $provider . '”';
	exit(1);
}

echo '<!DOCTYPE html>' . \PHP_EOL;
echo '<html lang="en">' . \PHP_EOL;
echo "\t" . '<head>' . \PHP_EOL;
echo "\t\t" . '<meta charset="utf-8">' . \PHP_EOL;
echo "\t\t" . '<meta http-equiv="X-UA-Compatible" content="IE=edge">' . \PHP_EOL;
echo "\t\t" . '<meta name="viewport" content="width=device-width, initial-scale=1">' . \PHP_EOL;
echo "\t\t" . '<link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAACklEQVR4nGMAAQAABQABDQottAAAAABJRU5ErkJggg==">' . \PHP_EOL;
echo "\t\t" . '<meta name="robots" content="noindex,nofollow">' . \PHP_EOL;
echo "\t\t" . '<meta name="referrer" content="origin">' . \PHP_EOL;
echo "\t\t" . '<title>Weather</title>' . \PHP_EOL;
echo "\t\t" . '<style type="text/css">' . \PHP_EOL;
echo "\t\t\t" . 'html, body {' . \PHP_EOL;
echo "\t\t\t\t" . 'font-family: "Roboto", -apple-system, "San Francisco", "Segoe UI", "Helvetica Neue", sans-serif;' . \PHP_EOL;
echo "\t\t\t\t" . 'font-size: 16px;' . \PHP_EOL;
echo "\t\t\t\t" . 'color: #333;' . \PHP_EOL;
echo "\t\t\t" . '}' . \PHP_EOL;
echo "\t\t\t" . 'table {' . \PHP_EOL;
echo "\t\t\t\t" . 'border-collapse: collapse;' . \PHP_EOL;
echo "\t\t\t" . '}' . \PHP_EOL;
echo "\t\t\t" . 'th, td {' . \PHP_EOL;
echo "\t\t\t\t" . 'padding: 4px 8px;' . \PHP_EOL;
echo "\t\t\t\t" . 'border: 1px solid #ccc;' . \PHP_EOL;
echo "\t\t\t" . '}' . \PHP_EOL;
echo "\t\t\t" . 'th {' . \PHP_EOL;
echo "\t\t\t\t" . 'text-align: center;' . \PHP_EOL;
echo "\t\t\t" . '}' . \PHP_EOL;
echo "\t\t\t" . 'td {' . \PHP_EOL;
echo "\t\t\t\t" . 'text-align: left;' . \PHP_EOL;
echo "\t\t\t" . '}' . \PHP_EOL;
echo "\t\t\t" . 'td.text-center {' . \PHP_EOL;
echo "\t\t\t\t" . 'text-align: center;' . \PHP_EOL;
echo "\t\t\t" . '}' . \PHP_EOL;
echo "\t\t\t" . 'td.text-right {' . \PHP_EOL;
echo "\t\t\t\t" . 'text-align: right;' . \PHP_EOL;
echo "\t\t\t" . '}' . \PHP_EOL;
echo "\t\t" . '</style>' . \PHP_EOL;
echo "\t" . '</head>' . \PHP_EOL;
echo "\t" . '<body>' . \PHP_EOL;
echo "\t\t" . '<table>' . \PHP_EOL;
echo "\t\t\t" . '<tr>' . \PHP_EOL;
echo "\t\t\t\t" . '<th>Day</th>' . \PHP_EOL;
echo "\t\t\t\t" . '<th>Time</th>' . \PHP_EOL;
echo "\t\t\t\t" . '<th>Temperature</th>' . \PHP_EOL;
echo "\t\t\t\t" . '<th colspan="2">Humidity</th>' . \PHP_EOL;
echo "\t\t\t\t" . '<th>Dew point</th>' . \PHP_EOL;
echo "\t\t\t\t" . '<th>Clouds</th>' . \PHP_EOL;
echo "\t\t\t\t" . '<th colspan="2">Wind</th>' . \PHP_EOL;
echo "\t\t\t\t" . '<th colspan="2">Precipitation</th>' . \PHP_EOL;
echo "\t\t\t\t" . '<th>Airing</th>' . \PHP_EOL;
echo "\t\t\t\t" . '<th colspan="2">UV index</th>' . \PHP_EOL;
echo "\t\t\t\t" . '<th>Visibility</th>' . \PHP_EOL;
echo "\t\t\t" . '</tr>' . \PHP_EOL;

$timeZone = new \DateTimeZone(!empty($normalized['timezone']) ? $normalized['timezone'] : 'UTC');

/** @var string|null $lastDay */
$lastDay = null;

$rows = \array_merge([ 0 => $normalized['current'] ], $normalized['forecast']);
\usort($rows, function ($a, $b) {
	return $a['timestamp'] < $b['timestamp'] ? -1 : ($a['timestamp'] > $b['timestamp'] ? 1 : 0);
});

foreach ($rows as $row) {
	/** @var \DateTime $dateTime */
	$dateTime = (new \DateTime('@' . $row['timestamp']))->setTimeZone($timeZone);

	if ($lastDay !== null && $lastDay !== $dateTime->format('Y-m-d')) {
		echo "\t\t\t" . '<tr>' . \PHP_EOL;
		echo "\t\t\t\t" . '<td colspan="15">&nbsp;</td>' . \PHP_EOL;
		echo "\t\t\t" . '</tr>' . \PHP_EOL;
	}

	echo "\t\t\t" . '<tr>' . \PHP_EOL;

	if (!$row['now'] && $row['future']) {
		echo "\t\t\t\t" . '<td>' . $dateTime->format('l') . '</td>' . \PHP_EOL;
		echo "\t\t\t\t" . '<td>' . $dateTime->format(!empty($normalized['timezone']) ? 'H:i' : 'H:i T') . '</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td colspan="2" class="text-center">Now</td>' . \PHP_EOL;
	}

	if ($row['temperature']['air'] !== null) {
		echo "\t\t\t\t" . '<td class="text-right">' . \number_format($row['temperature']['air'], 1) . '&#x202F;°C</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	if ($row['humidity']['relative'] !== null) {
		echo "\t\t\t\t" . '<td class="text-right">' . \number_format($row['humidity']['relative'] * 100, 0) . '&#x202F;%</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	if ($row['humidity']['absolute'] !== null) {
		echo "\t\t\t\t" . '<td class="text-right">' . \number_format($row['humidity']['absolute'], 1) . '&#x202F;g/m³</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	if ($row['dewPoint'] !== null) {
		echo "\t\t\t\t" . '<td class="text-right">' . \number_format($row['dewPoint'], 1) . '&#x202F;°C</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	if ($row['cloudiness'] !== null) {
		echo "\t\t\t\t" . '<td class="text-right">' . \number_format($row['cloudiness'] * 100, 0) . '&#x202F;%</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	if ($row['wind']['symbol'] !== null) {
		echo "\t\t\t\t" . '<td class="text-center">' . $row['wind']['symbol'] . '</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	if ($row['wind']['speed'] !== null) {
		echo "\t\t\t\t" . '<td class="text-right">' . \number_format($row['wind']['speed'], 0) . '&#x202F;km/h</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	if ($row['precipitation']['probability'] !== null) {
		echo "\t\t\t\t" . '<td class="text-right">' . \number_format($row['precipitation']['probability'] * 100, 0) . '&#x202F;%</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	if ($row['precipitation']['intensity'] !== null) {
		echo "\t\t\t\t" . '<td class="text-right">' . \number_format($row['precipitation']['intensity'], 3) . '&#x202F;mm/h</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	if ($row['humidity']['absolute'] !== null && !empty($roomTemperature)) {
		$rhAfterAiring = \RelativeHumidity::fromTemperatureAndAbsoluteHumidity($roomTemperature, $row['humidity']['absolute']);

		echo "\t\t\t\t" . '<td class="text-right">' . \number_format($rhAfterAiring, 0) . '&#x202F;%&#x202F;RH</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	if ($row['uvIndex']['value'] !== null) {
		echo "\t\t\t\t" . '<td class="text-right">' . \number_format($row['uvIndex']['value'], 0) . '</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	if ($row['uvIndex']['label'] !== null) {
		echo "\t\t\t\t" . '<td>' . $row['uvIndex']['label'] . '</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	if ($row['visibility'] !== null) {
		echo "\t\t\t\t" . '<td class="text-right">' . \number_format($row['visibility'], 1) . '&#x202F;km</td>' . \PHP_EOL;
	}
	else {
		echo "\t\t\t\t" . '<td>&nbsp;</td>' . \PHP_EOL;
	}

	echo "\t\t\t" . '</tr>' . \PHP_EOL;

	$lastDay = $dateTime->format('Y-m-d');
}

echo "\t\t" . '</table>' . \PHP_EOL;

if ($provider === \PROVIDER_DARK_SKY || $provider === \PROVIDER_OPEN_WEATHER_MAP) {
	echo "\t\t" . '<p>Powered by <a href="';

	if ($provider === \PROVIDER_DARK_SKY) {
		echo 'https://darksky.net/poweredby/';
	}
	elseif ($provider === \PROVIDER_OPEN_WEATHER_MAP) {
		echo 'https://openweathermap.org/';
	}

	echo '" target="_blank" rel="noopener noreferrer">';

	if ($provider === \PROVIDER_DARK_SKY) {
		echo 'Dark Sky';
	}
	elseif ($provider === \PROVIDER_OPEN_WEATHER_MAP) {
		echo 'OpenWeatherMap';
	}

	echo '</a></p>' . \PHP_EOL;
}

echo "\t" . '</body>' . \PHP_EOL;
echo '</html>' . \PHP_EOL;

exit(0);

final class Weather {

	const ABSOLUTE_ZERO = 273.15;
	const BOLTON_A = 6.112;
	const BOLTON_B = 17.67;
	const BOLTON_C = 243.5;
	const BUCK_D = 234.5;
	const MOLAR_GAS_CONSTANT = 8.3145;
	const MOLAR_MASS_WATER = 18.01528;

}

final class DewPoint {

	public static function fromTemperatureAndRelativeHumidity($temperature, $relativeHumidity) {
		if ($temperature === null || $relativeHumidity === null) {
			return null;
		}

		$gamma = \log($relativeHumidity * \exp((\Weather::BOLTON_B - $temperature / \Weather::BUCK_D) * ($temperature / ($temperature + \Weather::BOLTON_C))));

		return (\Weather::BOLTON_C * $gamma) / (\Weather::BOLTON_B - $gamma);
	}

}

final class AbsoluteHumidity {

	public static function fromTemperatureAndRelativeHumidity($temperature, $relativeHumidity) {
		if ($temperature === null || $relativeHumidity === null) {
			return null;
		}

		return (\Weather::BOLTON_A * \exp(((\Weather::BOLTON_B * $temperature) / ($temperature + \Weather::BOLTON_C))) * $relativeHumidity * 100 * \Weather::MOLAR_MASS_WATER / \Weather::MOLAR_GAS_CONSTANT) / (\Weather::ABSOLUTE_ZERO + $temperature);
	}

}

final class RelativeHumidity {

	public static function fromTemperatureAndAbsoluteHumidity($temperature, $absoluteHumidity) {
		if ($temperature === null || $absoluteHumidity === null) {
			return null;
		}

		return $absoluteHumidity / \Weather::BOLTON_A / \exp(((\Weather::BOLTON_B * $temperature) / ($temperature + \Weather::BOLTON_C))) / \Weather::MOLAR_MASS_WATER * \Weather::MOLAR_GAS_CONSTANT * (\Weather::ABSOLUTE_ZERO + $temperature);
	}
}

final class Wind {

	const DEGREES_CIRCLE = 360;
	const DEGREES_PER_SYMBOL = 360 / self::NUMBER_OF_SYMBOLS;
	const DEGREES_SEMICIRCLE = self::DEGREES_CIRCLE / 2;
	const NUMBER_OF_SYMBOLS = 8;

	public static function toSymbol($direction) {
		if ($direction === null) {
			return null;
		}

		$targetDegreesFromNorth = ($direction + self::DEGREES_SEMICIRCLE) % self::DEGREES_CIRCLE;
		$symbolIndex = ((int) \ceil($targetDegreesFromNorth / self::DEGREES_PER_SYMBOL - 0.5)) % self::NUMBER_OF_SYMBOLS;

		switch ($symbolIndex) {
			case 1: return '↗';
			case 2: return '→';
			case 3: return '↘';
			case 4: return '↓';
			case 5: return '↙';
			case 6: return '←';
			case 7: return '↖';
			default: return '↑';
		}
	}

}

final class UvIndex {

	public static function toLabel($value) {
		if ($value === null) {
			return null;
		}

		if ($value < 3) {
			return 'Low';
		}
		elseif ($value >= 3 && $value < 6) {
			return 'Moderate';
		}
		elseif ($value >= 6 && $value < 8) {
			return 'High';
		}
		elseif ($value >= 8 && $value < 11) {
			return 'Very high';
		}
		else {
			return 'Extreme';
		}
	}

}
