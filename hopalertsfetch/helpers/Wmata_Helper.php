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

	public function __construct($wmata_api_key)
	{
		$wmata_api = new Wmata_API($wmata_api_key);
		$this->wmata_api = $wmata_api;
	}

	public function update_alerts()
	{
		$refresh_delay = HAF_settings_helper::get_time_refresh();
		$last_bus_update = HAF_settings_helper::get_setting('last_update_bus');
		$last_rail_update = HAF_settings_helper::get_setting('last_update_rail');
		$now = ee()->localize->now;

		if ($last_bus_update != NULL && $last_bus_update != '')
		{
			$last_bus_update = intval($last_bus_update);
			if ($now - $last_bus_update > $refresh_delay)
			{
				$this->_update_bus_alerts();
			}
		}
		else
		{
			$this->_update_bus_alerts();
		}

		if ($last_rail_update != NULL && $last_rail_update != '')
		{
			$last_rail_update = intval($last_rail_update);
			if ($now - $last_rail_update > $refresh_delay)
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
		ee()->load->library('logger');
		$channel_id = HAF_settings_helper::get_alerts_channel_id();
		$channel_id_expired = HAF_settings_helper::get_alerts_channel_id_expired();
		$field_id_type = HAF_settings_helper::get_field_id_alert_type();
		$field_id_ext_id = HAF_settings_helper::get_field_id_alert_ext_id();
		$entries_res = ee('Model')->get('ChannelEntry')
			->filter('channel_id', $channel_id)
			->filter('status', 'IN', array('open'))
			->filter('field_id_'.$field_id_type, 'bus')
			->order('entry_date', 'DESC')
			->all();

		$entries = array();
		foreach ($entries_res as $entry_res)
		{
			$entries[$entry_res->{'field_id_'.$field_id_ext_id}] = $entry_res;
		}

		$result = json_decode($this->wmata_api->get_bus_incidents());
		if ($result == NULL)
		{
			if (HAF_settings_helper::get_debug())
			{
				ee()->logger->developer('HAF: json received for buses is not valid');
			}
			return;
		}

		if (isset($result->BusIncidents) && is_array($result->BusIncidents) && count($result->BusIncidents) > 0)
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

					// DateTime is in Eastern Standard Time EST, we need to convert it to GMT/UTC to store it
					$dt = new DateTime($busIncident->DateUpdated, new DateTimeZone('EST'));
					$dt->setTimezone(new DateTimeZone('UTC'));

					// Create a new entry with alert data
					HAF_helper::create_alert_entry($title, 'bus', $busIncident->IncidentID, $busIncident->Description, $dt->format('U'));
				}
			}

			foreach ($entries as $entry)
			{
				// Close remaining entries, as that means they're not going anymore
				$entry->status = 'closed';
				$entry->channel_id = $channel_id_expired;
				$entry->save();
			}

			HAF_settings_helper::save_setting('last_update_bus', ee()->localize->now);
		}
	}

	private function _update_rail_alerts()
	{
		ee()->load->library('logger');
		$channel_id = HAF_settings_helper::get_alerts_channel_id();
		$channel_id_expired = HAF_settings_helper::get_alerts_channel_id_expired();
		$field_id_type = HAF_settings_helper::get_field_id_alert_type();
		$field_id_ext_id = HAF_settings_helper::get_field_id_alert_ext_id();
		$entries_res = ee('Model')->get('ChannelEntry')
			->filter('channel_id', $channel_id)
			->filter('status', 'IN', array('open'))
			->filter('field_id_'.$field_id_type, 'rail')
			->order('entry_date', 'DESC')
			->all();

		$entries = array();
		foreach ($entries_res as $entry_res)
		{
			$entries[$entry_res->{'field_id_'.$field_id_ext_id}] = $entry_res;
		}

		$result = json_decode($this->wmata_api->get_rail_incidents());
		if ($result == NULL)
		{
			if (HAF_settings_helper::get_debug())
			{
				ee()->logger->developer('HAF: json received for rail is not valid');
			}
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
					$dt = new DateTime($railIncident->DateUpdated, new DateTimeZone('EST'));
					$dt->setTimezone(new DateTimeZone('UTC'));

					// Create a new entry with alert data
					HAF_helper::create_alert_entry($title, 'rail', $railIncident->IncidentID, $railIncident->Description, $dt->format('U'));
				}
			}

			foreach ($entries as $entry)
			{
				// Close remaining entries, as that means they're not going anymore
				$entry->status = 'closed';
				$entry->channel_id = $channel_id_expired;
				$entry->save();
			}

			HAF_settings_helper::save_setting('last_update_rail', ee()->localize->now);
		}
	}
}