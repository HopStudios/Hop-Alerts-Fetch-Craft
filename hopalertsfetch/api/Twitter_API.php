<?php

namespace Craft;

class Twitter_API
{

	private $twitter_api_exchange;

	public function __construct($access_token, $access_token_secret, $consumer_key, $consumer_secret)
	{
		require_once PATH_THIRD.'hop_alerts_fetch/lib/TwitterAPIExchange.php';

		$settings = array(
			'oauth_access_token' => $access_token,
			'oauth_access_token_secret' => $access_token_secret,
			'consumer_key' => $consumer_key,
			'consumer_secret' => $consumer_secret
		);

		$this->twitter_api_exchange = new TwitterAPIExchange($settings);
	}

	public function get_traffic_tweets($since = NULL)
	{
		$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		$getfield = '?screen_name=WTOPtraffic&exclude_replies=true&include_rts=false&count=30';
		$requestMethod = 'GET';

		if ($since != NULL)
		{
			$getfield .= '&since_id='.$since;
		}

		$results = $this->twitter_api_exchange->setGetfield($getfield)
			->buildOauth($url, $requestMethod)
			->performRequest();

		return $results;
	}

	public function get_dc_circulator_tweets($since = NULL)
	{
		$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		$getfield = '?screen_name=DCCirculator&exclude_replies=true&include_rts=false&count=30';
		$requestMethod = 'GET';

		if ($since != NULL)
		{
			$getfield .= '&since_id='.$since;
		}

		$results = $this->twitter_api_exchange->setGetfield($getfield)
			->buildOauth($url, $requestMethod)
			->performRequest();

		return $results;
	}

}