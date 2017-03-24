<?php

namespace Craft;

class RSS_api
{
	public function __construct()
	{
		
	}

	public function get_marc_train_updates()
	{
		$url = 'http://mtamarylandalerts.com/rss.aspx?ma';

		$rss_feed = $this->get_rss_content($url);

		if ($rss_feed == '')
		{
			// TODO: Change That ?
			// if (HAF_settings_helper::get_debug()) {
			// 	ee()->logger->developer('HAF: RSS Feed of MARC train empty');
			// }
			return null;
		}

		// Parse that thing to retrieve meaningful content
		$rss = new DOMDocument();
		$result = $rss->loadXML($rss_feed);

		if ($result === FALSE)
		{
			// TODO: Change That ?
			// if (HAF_settings_helper::get_debug()) {
			// 	ee()->logger->developer('HAF: Error parsing RSS Feed of MARC train');
			// }
			return null;
		}

		$items = array();
		foreach ($rss->getElementsByTagName('item') as $node) {
			$item = array ( 
				'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
				'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
				'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
				'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
				'guid' => $node->getElementsByTagName('guid')->item(0)->nodeValue,
			);
			$items[] = $item;
		}

		return $items;
	}

	public function get_vavre_train_updates()
	{
		$url = 'https://public.govdelivery.com/accounts/VAVRE/feed.rss';

		$rss_feed = $this->get_rss_content($url);

		if ($rss_feed == '')
		{
			// TODO: Change That ?
			// if (HAF_settings_helper::get_debug()) {
			// 	ee()->logger->developer('HAF: RSS Feed of VRE train empty');
			// }
			return null;
		}

		// Parse that thing to retrieve meaningful content
		$rss = new DOMDocument();
		$result = $rss->loadXML($rss_feed);

		if ($result === FALSE)
		{
			// TODO: Change That ?
			// if (HAF_settings_helper::get_debug()) {
			// 	ee()->logger->developer('HAF: Error parsing RSS Feed of VRE train');
			// }
			return null;
		}

		$items = array();
		foreach ($rss->getElementsByTagName('item') as $node) {
			$item = array ( 
				'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
				'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
				'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
				'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
				'guid' => $node->getElementsByTagName('guid')->item(0)->nodeValue,
			);
			$items[] = $item;
		}

		return $items;
	}

	public function get_art_bus_updates()
	{
		$url = 'http://www.commuterpage.com/RSS/artalert_rss.xml';

		$rss_feed = $this->get_rss_content($url);

		if ($rss_feed == '')
		{
			// TODO: Change That ?
			// if (HAF_settings_helper::get_debug()) {
			// 	ee()->logger->developer('HAF: RSS Feed of ART Bus empty');
			// }
			return null;
		}

		// Parse that thing to retrieve meaningful content
		$rss = new DOMDocument();
		$result = $rss->loadXML($rss_feed);

		if ($result === FALSE)
		{
			// TODO: Change That ?
			// if (HAF_settings_helper::get_debug()) {
			// 	ee()->logger->developer('HAF: Error parsing RSS Feed of ART Bus');
			// }
			return null;
		}

		$items = array();
		foreach ($rss->getElementsByTagName('item') as $node) {
			$item = array ( 
				'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
				'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
				'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
				'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
				'guid' => $node->getElementsByTagName('guid')->item(0)->nodeValue,
			);
			$items[] = $item;
		}

		return $items;
	}

	public function get_montgomery_rideon_bus_updates()
	{
		$url = 'http://www.montgomerycountymd.gov/dot-rss/resources/files/rss/rideonrss.xml';

		$rss_feed = $this->get_rss_content($url);

		if ($rss_feed == '')
		{
			// TODO: Change That ?
			// if (HAF_settings_helper::get_debug()) {
			// 	ee()->logger->developer('HAF: RSS Feed of Montgomery RideOn Bus empty');
			// }
			return null;
		}

		// Parse that thing to retrieve meaningful content
		$rss = new DOMDocument();
		$result = $rss->loadXML($rss_feed);

		if ($result === FALSE)
		{
			// TODO: Change That ?
			// if (HAF_settings_helper::get_debug()) {
			// 	ee()->logger->developer('HAF: Error parsing RSS Feed of Montgomery RideOn Bus');
			// }
			return null;
		}

		$items = array();
		// This feed isn't like the others...
		foreach ($rss->getElementsByTagName('entry') as $node) {
			$link = '';
			$link_node = $node->getElementsByTagName('link')->item(0);
			if ($link_node)
			{
				$link = $link_node->getAttribute('href');
			}
			// guid doesn't exist, let's create one, because it's fun
			$guid = $node->getElementsByTagName('title')->item(0)->nodeValue.''.$node->getElementsByTagName('updated')->item(0)->nodeValue;
			$item = array ( 
				'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
				'desc' => $node->getElementsByTagName('content')->item(0)->nodeValue,
				'link' => $link,
				'date' => $node->getElementsByTagName('updated')->item(0)->nodeValue,
				'guid' => $guid,
			);
			$items[] = $item;
		}

		return $items;
	}

	private function get_rss_content($url, $parameters = array())
	{

		if (!empty($parameters))
		{
			$query = http_build_query($parameters);
			$url .= '?' . $query;
		}

		$ch = curl_init();

		// curl_setopt($ch, CURLOPT_HTTPHEADER, array('api_key: ' . $this->api_key));
		// Cheating here, in order not to get kicked out for sites/servers
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		curl_setopt($ch, CURLOPT_URL, $url);
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
			// We should probably log the error here...
			// TODO: Change That ?
			// ee()->logger->developer('HTTP Error '.$status.' when getting content from '.$url);
			return $data;
		}
	}
}