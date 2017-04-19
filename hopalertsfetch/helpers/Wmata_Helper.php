<?php

namespace Craft;

class Wmata_Helper
{
	/*
	WMATA https://developer.wmata.com/ provide alerts/incidents for buses and rail
	We can fetch all incidents in one time.
	Once an incident isn't in the list anymore, it's supposed to be done/back to normal

	We create an entry for each alert, and set it to closed once it's done
	*/

	private $wmata_api;
	private $active_section_handle;
	private $inactive_section_handle;
	private $author_id;
	private $type_handle;
	private $custom_id_handle;
	private $content_handle;
	private $refreshDelay;
	private $lastUpdateBus;
	private $lastUpdateRail;

	public function __construct()
	{
		$settings						= craft()->plugins->getPlugin('HopAlertsFetch')->getSettings();
		$this->wmata_api				= new Wmata_API($settings->wmata_api_key);
		$this->active_section_handle	= $settings->alerts_section_handle;
		$this->inactive_section_handle	= $settings->alerts_expired_section_handle;
		$this->author_id				= $settings->author_id;
		$this->type_handle				= $settings->field_handle_alert_type;
		$this->custom_id_handle			= $settings->field_handle_alert_ext_id;
		$this->content_handle			= $settings->field_handle_alert_body;
		$this->refreshDelay				= $settings->time_refresh;
		$this->lastUpdateBus			= $settings->last_update_bus;
		$this->lastUpdateRail			= $settings->last_update_rail;
	}

	public function update_alerts()
	{
		$dt = new DateTime();
		$now = $dt->getTimestamp();

		if ($this->lastUpdateBus != NULL && $this->lastUpdateBus != '')
		{
			if ($now - $this->lastUpdateBus > $this->refreshDelay)
			{
				$this->_update_bus_alerts();
			}
		}
		else
		{
			$this->_update_bus_alerts();
		}

		if ($this->lastUpdateRail != NULL && $this->lastUpdateRail != '')
		{
			if ($now - $this->lastUpdateRail > $this->refreshDelay)
			{
				$this->_update_rail_alerts();
			}
		}
		else
		{
			$this->_update_rail_alerts();
		}
	}


	private function _update_bus_alerts()
	{

		// $channel_id = HAF_settings_helper::get_alerts_channel_id();
		// $channel_id_expired = HAF_settings_helper::get_alerts_channel_id_expired();
		// $field_id_type = HAF_settings_helper::get_field_id_alert_type();
		// $field_id_ext_id = HAF_settings_helper::get_field_id_alert_ext_id();
		// $entries_res = ee('Model')->get('ChannelEntry')
		// 	->filter('channel_id', $channel_id)
		// 	->filter('status', 'IN', array('open'))
		// 	->filter('field_id_'.$field_id_type, 'bus')
		// 	->order('entry_date', 'DESC')
		// 	->all();

		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->section = $this->active_section_handle;
		$criteria->status = null; // Get all entries in this section
		$criteria->order = 'postDate desc';
		$criteria->limit = 500000; // This is to make sure we got max results possible

		$results = $criteria->find(array($this->type_handle => 'bus'));
		$entries = array();
		foreach ($results as $entry_res)
		{
			// $entries[$entry_res->{'field_id_'.$field_id_ext_id}] = $entry_res;
			$entries[$entry_res->{$this->custom_id_handle}] = $entry_res;
		}

		// echo 'update BUS';

		$result = json_decode($this->wmata_api->get_bus_incidents());
		if ($result == NULL)
		{
			HopAlertsFetchPlugin::log('json received for buses is not valid', LogLevel::Error);
			return;
		}

		if (isset($result->BusIncidents) && is_array($result->BusIncidents))
		{
			if (count($result->BusIncidents) > 0)
			{
				foreach ($result->BusIncidents as $busIncident)
				{
					if (array_key_exists($busIncident->IncidentID, $entries))
					{
						// nothing to do, entry is present and alert is still going
						// Remove it from the entries array
						unset($entries[$busIncident->IncidentID]);
					}
					else
					{
						$title = 'Bus '.$busIncident->IncidentType.' on ';
						foreach ($busIncident->RoutesAffected as $route)
						{
							$title .= $route.', ';
						}
						$title = substr($title, 0, -2);

						// DateTime is in Eastern Standard Time EST, we need to convert it to local Craft timezone
						$dt = new DateTime($busIncident->DateUpdated, new \DateTimeZone('EST'));
						$dt->setTimezone(new \DateTimeZone(craft()->timezone));

						// Create a new entry with alert data 
						// $sectionHandle, $author_id, $title, $type, $type_handle, $custom_id, $custom_id_handle, $content, $content_handle, $timestamp = NULL
						Main_Helper::createAlertEntry(
							$this->active_section_handle,
							$this->author_id,
							$title,
							'bus',
							$this->type_handle,
							$busIncident->IncidentID,
							$this->custom_id_handle,
							$busIncident->Description,
							$this->content_handle,
							$dt
						);
					}
				}

				$inactive_section_id = craft()->sections->getSectionByHandle($this->inactive_section_handle)->id;
				foreach ($entries as $entry)
				{
					// Close remaining entries, as that means they're not active anymore
					// $entry->status = 'closed'; // Switch entry to disabled ?
					$entry->sectionId = $inactive_section_id;
					$success = craft()->entries->saveEntry($entry);
				}
			}

			$dt = new DateTime();
			$now = $dt->getTimestamp();
			$haf = craft()->plugins->getPlugin( 'HopAlertsFetch' );
			craft()->plugins->savePluginSettings($haf, array('last_update_bus' => $now));
		}
		else if (isset($result->message))
		{
			HopAlertsFetchPlugin::log('Error when retrieving BUS alerts: "'.$result->message.'"', LogLevel::Error);
		}
	}

	private function _update_rail_alerts()
	{
		// $channel_id = HAF_settings_helper::get_alerts_channel_id();
		// $channel_id_expired = HAF_settings_helper::get_alerts_channel_id_expired();
		// $field_id_type = HAF_settings_helper::get_field_id_alert_type();
		// $field_id_ext_id = HAF_settings_helper::get_field_id_alert_ext_id();
		// $entries_res = ee('Model')->get('ChannelEntry')
		// 	->filter('channel_id', $channel_id)
		// 	->filter('status', 'IN', array('open'))
		// 	->filter('field_id_'.$field_id_type, 'rail')
		// 	->order('entry_date', 'DESC')
		// 	->all();

		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->section = $this->active_section_handle;
		$criteria->status = null; // Get all entries in this section
		$criteria->order = 'postDate desc';
		$criteria->limit = 500000; // This is to make sure we got max results possible

		$results = $criteria->find(array($this->type_handle => 'rail'));
		$entries = array();
		foreach ($results as $entry_res)
		{
			// $entries[$entry_res->{'field_id_'.$field_id_ext_id}] = $entry_res;
			$entries[$entry_res->{$this->custom_id_handle}] = $entry_res;
		}

		// echo 'update Rail';

		$result = json_decode($this->wmata_api->get_rail_incidents());
		if ($result == NULL)
		{
			HopAlertsFetchPlugin::log('json received for rail is not valid', LogLevel::Error);
			return;
		}

		if (isset($result->Incidents) && is_array($result->Incidents) && count($result->Incidents) > 0)
		{
			foreach ($result->Incidents as $railIncident)
			{
				if (array_key_exists($railIncident->IncidentID, $entries))
				{
					// nothing to do, entry is present and alert is still going
					// Remove it from the entries array
					unset($entries[$railIncident->IncidentID]);
				}
				else
				{
					$title = 'Rail '.$railIncident->IncidentType.' on ';
					$lines_aff_arr = explode(';', $railIncident->LinesAffected);
					foreach ($lines_aff_arr as $route)
					{
						if ($route != '')
						{
							$title .= $route.', ';
						}
					}
					$title = substr($title, 0, -2);

					// DateTime is in Eastern Standard Time EST, we need to convert it to GMT/UTC to store it
					$dt = new DateTime($railIncident->DateUpdated, new \DateTimeZone('EST'));
					$dt->setTimezone(new \DateTimeZone('UTC'));

					// Create a new entry with alert data
					// HAF_helper::create_alert_entry($title, 'rail', $railIncident->IncidentID, $railIncident->Description, $dt->format('U'));
					Main_Helper::createAlertEntry(
						$this->active_section_handle,
						$this->author_id,
						$title,
						'rail',
						$this->type_handle,
						$railIncident->IncidentID,
						$this->custom_id_handle,
						$railIncident->Description,
						$this->content_handle,
						$dt->format('U')
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

			$dt = new DateTime();
			$now = $dt->getTimestamp();
			$haf = craft()->plugins->getPlugin( 'HopAlertsFetch' );
			craft()->plugins->savePluginSettings($haf, array('last_update_bus' => $now));
		}
		else if (isset($result->message))
		{
			HopAlertsFetchPlugin::log('Error when retrieving RAIL alerts: "'.$result->message.'"', LogLevel::Error);
		}
	}
}