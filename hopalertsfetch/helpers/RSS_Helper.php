<?php

namespace Craft;

class RSS_Helper
{

	/*
		For trains, we get data from RSS feeds.
		Parsing of the feed is done in the api class
	*/

	private $rss_api;
	private $active_section_handle;
	private $inactive_section_handle;
	private $author_id;
	private $type_handle;
	private $custom_id_handle;
	private $content_handle;
	private $refreshDelay;
	private $last_update_train_marc;
	private $last_update_train_vavre;
	private $last_update_bus_art;
	private $last_update_bus_montgomery_rideon;
	private $time_expired;

	public function __construct()
	{
		$this->rss_api							= new RSS_api();
		$settings								= craft()->plugins->getPlugin('HopAlertsFetch')->getSettings();
		$this->active_section_handle			= $settings->alerts_section_handle;
		$this->inactive_section_handle			= $settings->alerts_expired_section_handle;
		$this->author_id						= $settings->author_id;
		$this->type_handle						= $settings->field_handle_alert_type;
		$this->custom_id_handle					= $settings->field_handle_alert_ext_id;
		$this->content_handle					= $settings->field_handle_alert_body;
		$this->refreshDelay						= $settings->time_refresh;
		$this->last_update_train_marc			= $settings->last_update_train_marc;
		$this->last_update_train_vavre			= $settings->last_update_train_vavre;
		$this->last_update_bus_art 				= $settings->last_update_bus_art;
		$this->last_update_bus_montgomery_rideon= $settings->last_update_bus_montgomery_rideon;
		$this->time_expired						= $settings->time_expired;
	}

	public function update_alerts()
	{
		$dt = new DateTime();
		$now = $dt->getTimestamp();

		if ($this->last_update_train_marc != NULL && $this->last_update_train_marc != '')
		{
			if ($now - intval($this->last_update_train_marc) > ($this->refreshDelay))
			{
				$this->_update_train_marc_alerts();
			}
		}
		else
		{
			$this->_update_train_marc_alerts();
		}

		if ($this->last_update_train_vavre != NULL && $this->last_update_train_vavre != '')
		{
			$this->last_update_train_vavre = intval($this->last_update_train_vavre);
			if ($now - $this->last_update_train_vavre > ($this->refreshDelay))
			{
				$this->_update_train_vavre_alerts();
			}
		}
		else
		{
			$this->_update_train_vavre_alerts();
		}

		if ($this->last_update_bus_art != NULL && $this->last_update_bus_art != '')
		{
			$this->last_update_bus_art = intval($this->last_update_bus_art);
			if ($now - $this->last_update_bus_art > ($this->refreshDelay))
			{
				$this->_update_bus_art_alerts();
			}
		}
		else
		{
			$this->_update_bus_art_alerts();
		}

		if ($this->last_update_bus_montgomery_rideon != NULL && $this->last_update_bus_montgomery_rideon != '')
		{
			$this->last_update_bus_montgomery_rideon = intval($this->last_update_bus_montgomery_rideon);
			if ($now - $this->last_update_bus_montgomery_rideon > ($this->refreshDelay))
			{
				$this->_update_bus_montgomery_rideon_alerts();
			}
		}
		else
		{
			$this->_update_bus_montgomery_rideon_alerts();
		}
	}

	private function _update_train_marc_alerts()
	{
		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->section = $this->active_section_handle;
		$criteria->status = null; // Get all entries in this section
		$criteria->order = 'postDate desc';
		$criteria->limit = 500000; // This is to make sure we got max results possible

		$results = $criteria->find(array($this->type_handle => 'train_marc'));
		$entries = array();
		foreach ($results as $entry_res)
		{
			$entries[$entry_res->{$this->custom_id_handle}] = $entry_res;
		}

		$results = $this->rss_api->get_marc_train_updates();

		if (is_array($results) && count($results) > 0)
		{
			foreach ($results as $incident)
			{
				if (array_key_exists($incident['guid'], $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$incident['guid']]);
				}
				else
				{
					$title = 'MARC Train: '.$incident['title'];

					// Datetime specified in GMT
					// It's automatically converted to the local Craft timezone
					$dt = new DateTime($incident['date']);

					// Create a new entry with alert data
					Main_Helper::createAlertEntry(
						$this->active_section_handle,
						$this->author_id,
						$title,
						'train_marc',
						$this->type_handle,
						$incident['guid'],
						$this->custom_id_handle,
						$incident['desc'],
						$this->content_handle,
						$dt
					);
				}
			}

			$inactive_section_id = craft()->sections->getSectionByHandle($this->inactive_section_handle)->id;
			foreach ($entries as $entry)
			{
				// Close remaining entries, as that means they're not going anymore
				$entry->sectionId = $inactive_section_id;
				$success = craft()->entries->saveEntry($entry);
			}

			// I'm not sure how the feed is cleaned up, it seems that old/not valid alerts are removed from the feed.
			// That means old entries will be automatically closed so we should be fine
			$dt = new DateTime();
			$now = $dt->getTimestamp();

			$haf = craft()->plugins->getPlugin( 'HopAlertsFetch' );
			craft()->plugins->savePluginSettings($haf, array('last_update_train_marc' => $now));
		}
	}

	private function _update_train_vavre_alerts()
	{
		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->section = $this->active_section_handle;
		$criteria->status = null; // Get all entries in this section
		$criteria->order = 'postDate desc';
		$criteria->limit = 500000; // This is to make sure we got max results possible

		$results = $criteria->find(array($this->type_handle => 'train_vavre'));
		$entries = array();
		foreach ($results as $entry_res)
		{
			$entries[$entry_res->{$this->custom_id_handle}] = $entry_res;
		}

		$results = $this->rss_api->get_vavre_train_updates();

		if (is_array($results) && count($results) > 0)
		{
			foreach ($results as $incident)
			{
				if (array_key_exists($incident['guid'], $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$incident['guid']]);
				}
				else
				{
					$title = 'VAVRE Train: '.$incident['title'];

					// Datetime has timezone in it (YAY !)
					// It's automatically converted to the local Craft timezone
					$entry_dt = new DateTime($incident['date']);

					// Check if the entry isn't already too old to be saved
					$dt = new DateTime();
					$now = $dt->getTimestamp();
					if ( ( $now - intval($entry_dt->format('U')) ) > $this->time_expired )
					{
						continue;
					}

					// Create a new entry with alert data
					Main_Helper::createAlertEntry(
						$this->active_section_handle,
						$this->author_id,
						$title,
						'train_vavre',
						$this->type_handle,
						$incident['guid'],
						$this->custom_id_handle,
						$incident['desc'],
						$this->content_handle,
						$dt
					);
				}
			}

			// The feed is definitely not cleaned up and show old/not accurate info.
			// In doubt, clean all older than setting defined
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
			craft()->plugins->savePluginSettings($haf, array('last_update_train_vavre' => $now));
		}
	}

	private function _update_bus_art_alerts()
	{
		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->section = $this->active_section_handle;
		$criteria->status = null; // Get all entries in this section
		$criteria->order = 'postDate desc';
		$criteria->limit = 500000; // This is to make sure we got max results possible

		$results = $criteria->find(array($this->type_handle => 'bus_art'));
		$entries = array();
		foreach ($results as $entry_res)
		{
			$entries[$entry_res->{$this->custom_id_handle}] = $entry_res;
		}

		$results = $this->rss_api->get_art_bus_updates();

		if (is_array($results) && count($results) > 0)
		{
			foreach ($results as $incident)
			{
				if (array_key_exists($incident['guid'], $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$incident['guid']]);
				}
				else
				{
					$title = 'ART Bus: '.$incident['title'];
					
					// Datetime has timezone in it (YAY !)
					// It's automatically converted to the local Craft timezone
					$dt = new DateTime($incident['date']);
					// Create a new entry with alert data
					Main_Helper::createAlertEntry(
						$this->active_section_handle,
						$this->author_id,
						$title,
						'bus_art',
						$this->type_handle,
						$incident['guid'],
						$this->custom_id_handle,
						$incident['desc'],
						$this->content_handle,
						$dt
					);
				}
			}

			// I'm not sure how the feed is cleaned up, it seems that old/not valid alerts are removed from the feed.
			// That means old entries will be automatically closed so we should be fine

			$inactive_section_id = craft()->sections->getSectionByHandle($this->inactive_section_handle)->id;
			foreach ($entries as $entry)
			{
				// Close remaining entries, as that means they're not going anymore
				$entry->sectionId = $inactive_section_id;
				$success = craft()->entries->saveEntry($entry);
			}

			$dt = new DateTime();
			$now = $dt->getTimestamp();
			$haf = craft()->plugins->getPlugin( 'HopAlertsFetch' );
			craft()->plugins->savePluginSettings($haf, array('last_update_bus_art' => $now));
		}
	}

	private function _update_bus_montgomery_rideon_alerts()
	{
		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->section = $this->active_section_handle;
		$criteria->status = null; // Get all entries in this section
		$criteria->order = 'postDate desc';
		$criteria->limit = 500000; // This is to make sure we got max results possible

		$results = $criteria->find(array($this->type_handle => 'bus_montgomery_rideon'));
		$entries = array();
		foreach ($results as $entry_res)
		{
			$entries[$entry_res->{$this->custom_id_handle}] = $entry_res;
		}

		$results = $this->rss_api->get_montgomery_rideon_bus_updates();

		if (is_array($results) && count($results) > 0)
		{
			foreach ($results as $incident)
			{
				if (array_key_exists($incident['guid'], $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$incident['guid']]);
				}
				else
				{
					$title = 'Montgomery RideOn: '.$incident['title'];

					// Datetime has timezone in it (YAY !)
					// It's automatically converted to the local Craft timezone
					$dt = new DateTime($incident['date']);
					// Create a new entry with alert data
					Main_Helper::createAlertEntry(
						$this->active_section_handle,
						$this->author_id,
						$title,
						'bus_montgomery_rideon',
						$this->type_handle,
						$incident['guid'],
						$this->custom_id_handle,
						$incident['desc'],
						$this->content_handle,
						$dt
					);
				}
			}

			// I'm not sure how the feed is cleaned up, it seems that old/not valid alerts are removed from the feed.
			// That means old entries will be automatically closed so we should be fine
			$inactive_section_id = craft()->sections->getSectionByHandle($this->inactive_section_handle)->id;
			foreach ($entries as $entry)
			{
				// Close remaining entries, as that means they're not going anymore
				$entry->sectionId = $inactive_section_id;
				$success = craft()->entries->saveEntry($entry);
			}

			$dt = new DateTime();
			$now = $dt->getTimestamp();
			$haf = craft()->plugins->getPlugin( 'HopAlertsFetch' );
			craft()->plugins->savePluginSettings($haf, array('last_update_bus_montgomery_rideon' => $now));
		}
	}
}