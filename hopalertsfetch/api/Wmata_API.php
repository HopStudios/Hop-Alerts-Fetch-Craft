<?php

namespace Craft;

class Wmata_API
{
	private $api_key;

	const WMATA_API_URL = 'https://api.wmata.com';

	function __construct($api_key)
	{
		$this->api_key = $api_key;
	}

	public function get_bus_incidents()
	{
		return $this->call_api('/Incidents.svc/json/BusIncidents');
	}

	public function get_rail_incidents()
	{
		return $this->call_api('/Incidents.svc/json/Incidents');
	}

	private function call_api($end_point, $parameters = array())
	{
		$call_url = self::WMATA_API_URL.'/'.$end_point;

		if (!empty($parameters))
		{
			$query = http_build_query($parameters);
			$call_url .= '?' . $query;
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HTTPHEADER, array('api_key: ' . $this->api_key));
		curl_setopt($ch, CURLOPT_URL, $call_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		/* Execute cURL, Return Data */
		$data = curl_exec($ch);

		/* Check HTTP Code */
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		// print_r($data);

		if ($status == 200)
		{
			//We got our data, YAY !
			return $data;
		}
		else
		{
			//Error
			// echo '<h2>Error WMATA API</h2>';
			// print_r($data);
			return $data;
		}
	}
}