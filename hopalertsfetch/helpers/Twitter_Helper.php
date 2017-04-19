<?php

namespace Craft;

class Twitter_Helper
{
	/*
		We fetch traffic updates from a Twitter account https://twitter.com/WTOPtraffic

		Each tweet will become an incident entry
		We have no clue about when to close them though...
	*/

	private $twitter_api;
	private $active_section_handle;
	private $inactive_section_handle;
	private $author_id;
	private $type_handle;
	private $custom_id_handle;
	private $content_handle;
	private $refreshDelay;
	private $last_update_car;
	private $last_update_bus_dc_circulator;
	private $time_expired;

	public function __construct()
	{
		$settings								= craft()->plugins->getPlugin('HopAlertsFetch')->getSettings();
		$this->twitter_api						= new Twitter_api(
													$settings->twitter_oauth_access_token,
													$settings->twitter_oauth_access_token_secret,
													$settings->twitter_consumer_key,
													$settings->twitter_consumer_secret
												);
		$this->active_section_handle			= $settings->alerts_section_handle;
		$this->inactive_section_handle			= $settings->alerts_expired_section_handle;
		$this->author_id						= $settings->author_id;
		$this->type_handle						= $settings->field_handle_alert_type;
		$this->custom_id_handle					= $settings->field_handle_alert_ext_id;
		$this->content_handle					= $settings->field_handle_alert_body;
		$this->refreshDelay						= $settings->time_refresh;
		$this->last_update_car					= $settings->last_update_car;
		$this->last_update_bus_dc_circulator	= $settings->last_update_bus_dc_circulator;
		$this->time_expired						= $settings->time_expired;
	}

	public function update_alerts()
	{
		$dt = new DateTime();
		$now = $dt->getTimestamp();

		if ($this->last_update_car != NULL && $this->last_update_car != '')
		{
			$this->last_update_car = intval($this->last_update_car);
			if ($now - $this->last_update_car > ($this->refreshDelay))
			{
				$this->_update_car_alerts();
			}
		}
		else
		{
			$this->_update_car_alerts();
		}

		if ($this->last_update_bus_dc_circulator != NULL && $this->last_update_bus_dc_circulator != '')
		{
			$this->last_update_bus_dc_circulator = intval($this->last_update_bus_dc_circulator);
			if ($now - $this->last_update_bus_dc_circulator > ($this->refreshDelay))
			{
				$this->_update_bus_dc_circulator_alerts();
			}
		}
		else
		{
			$this->_update_bus_dc_circulator_alerts();
		}
	}

	private function _update_car_alerts()
	{
		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->section = $this->active_section_handle;
		$criteria->status = null; // Get all entries in this section
		$criteria->order = 'postDate desc';
		$criteria->limit = 500000; // This is to make sure we got max results possible

		$results = $criteria->find(array($this->type_handle => 'car'));
		$entries = array();
		foreach ($results as $entry_res)
		{
			// $entries[$entry_res->{'field_id_'.$field_id_ext_id}] = $entry_res;
			$entries[$entry_res->{$this->custom_id_handle}] = $entry_res;
		}

		$results = json_decode($this->twitter_api->get_traffic_tweets());
		if ($results == NULL)
		{
			HopAlertsFetchPlugin::log('json received for car is not valid', LogLevel::Error);
			return;
		}

		if (is_array($results) && count($results) > 0)
		{
			foreach ($results as $tweet)
			{
				if (array_key_exists($tweet->id_str, $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$tweet->id_str]);
				}
				else
				{
					$title = 'Traffic: ';
					if (strlen($tweet->text) > 50)
					{
						$title .= substr($tweet->text, 0, 47) . '...';
					}
					else
					{
						$title .= $tweet->text;
					}

					// Twitter gives datetime including timezone (YAY !)
					// It's automatically converted to the local Craft timezone
					$tweet_dt = new DateTime($tweet->created_at);

					// If the tweet is too old, no need to save it
					$dt = new DateTime();
					$now = $dt->getTimestamp();
					if ( ( $now - intval($tweet_dt->format('U')) ) > $this->time_expired )
					{
						continue;
					}

					// Create a new entry with alert data
					Main_Helper::createAlertEntry(
						$this->active_section_handle,
						$this->author_id,
						$title,
						'car',
						$this->type_handle,
						$tweet->id_str,
						$this->custom_id_handle,
						$tweet->text,
						$this->content_handle,
						$tweet_dt
					);
				}
			}

			// Handle remaining opened entries
			// There's no real way to tell if still active or not
			// We're using the time_expired setting to determine if an entry must go to the Expired section
			// entry_date is GMT, needs to be converted to locale
			$dt = new DateTime();
			$now = $dt->getTimestamp();
			$inactive_section_id = craft()->sections->getSectionByHandle($this->inactive_section_handle)->id;
			foreach ($entries as $entry)
			{
				if ( ( $now - intval($entry->dateCreated->format('U')) ) > $this->time_expired )
				{
					$entry->sectionId = $inactive_section_id;
					$success = craft()->entries->saveEntry($entry);
				}
			}

			$haf = craft()->plugins->getPlugin( 'HopAlertsFetch' );
			craft()->plugins->savePluginSettings($haf, array('last_update_car' => $now));
		}
	}

	private function _update_bus_dc_circulator_alerts()
	{
		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->section = $this->active_section_handle;
		$criteria->status = null; // Get all entries in this section
		$criteria->order = 'postDate desc';
		$criteria->limit = 500000; // This is to make sure we got max results possible

		$results = $criteria->find(array($this->type_handle => 'bus_dc_circulator'));
		$entries = array();
		foreach ($results as $entry_res)
		{
			// $entries[$entry_res->{'field_id_'.$field_id_ext_id}] = $entry_res;
			$entries[$entry_res->{$this->custom_id_handle}] = $entry_res;
		}

		$results = json_decode($this->twitter_api->get_dc_circulator_tweets());
		if ($results == NULL)
		{
			HopAlertsFetchPlugin::log('json received for bus dc circulator is not valid', LogLevel::Error);
			return;
		}

		if (is_array($results) && count($results) > 0)
		{
			foreach ($results as $tweet)
			{
				if (array_key_exists($tweet->id_str, $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$tweet->id_str]);
				}
				else
				{
					$title = 'DC Circulator: ';
					if (strlen($tweet->text) > 50)
					{
						$title .= substr($tweet->text, 0, 47) . '...';
					}
					else
					{
						$title .= $tweet->text;
					}

					// Twitter gives datetime including timezone (YAY !)
					// It's automatically converted to the local Craft timezone
					$tweet_dt = new DateTime($tweet->created_at);

					// If the tweet is too old, no need to save it
					$dt = new DateTime();
					$now = $dt->getTimestamp();
					if ( ( $now - intval($tweet_dt->format('U')) ) > $this->time_expired )
					{
						continue;
					}

					// Create a new entry with alert data
					Main_Helper::createAlertEntry(
						$this->active_section_handle,
						$this->author_id,
						$title,
						'bus_dc_circulator',
						$this->type_handle,
						$tweet->id_str,
						$this->custom_id_handle,
						$tweet->text,
						$this->content_handle,
						$tweet_dt
					);
				}
			}

			// Handle remaining opened entries
			// There's no real way to tell if still active or not
			// We're using the time_expired setting to determine if an entry must go to the Expired section
			// entry_date is GMT, needs to be converted to locale
			$dt = new DateTime();
			$now = $dt->getTimestamp();
			$inactive_section_id = craft()->sections->getSectionByHandle($this->inactive_section_handle)->id;
			foreach ($entries as $entry)
			{
				if ( ( $now - intval(ee()->localize->format_date('%U', $entry->entry_date)) ) > $this->time_expired )
				{
					$entry->sectionId = $inactive_section_id;
					$success = craft()->entries->saveEntry($entry);
				}
			}

			$haf = craft()->plugins->getPlugin( 'HopAlertsFetch' );
			craft()->plugins->savePluginSettings($haf, array('last_update_bus_dc_circulator' => $now));
		}
	}
}