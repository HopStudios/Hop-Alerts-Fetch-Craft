<?php

namespace Craft;

class Main_Helper
{
	public static function createAlertEntry($sectionHandle, $author_id, $title, $type, $type_handle, $custom_id, $custom_id_handle, $content, $content_handle, $datetime = NULL)
	{
		$entry = new EntryModel();
		$entry->sectionId = craft()->sections->getSectionByHandle($sectionHandle)->id;

		$postDate = new DateTime();
		if ($datetime)
		{
			$postDate = $datetime;
		}

		$entry->enabled								= TRUE;
		$entry->authorId 							= $author_id;
		$entry->postDate							= $postDate;
		$entry->getContent()->title					= $title;
		$entry->getContent()->{$type_handle}		= $type;
		$entry->getContent()->{$custom_id_handle}	= $custom_id;
		$entry->getContent()->{$content_handle}		= $content;

		if (craft()->entries->saveEntry($entry))
		{
			
		}
		else
		{
			HopAlertsFetchPlugin::log('Tried to create an Alert entry but saving into DB failed: '.$entry->getErrors() , LogLevel::Error);
		}
	}

	public static function createEntry($sectionHandle, $author_id, $title, $fields, $timestamp = NULL)
	{
		if (!is_array($fields))
		{
			throw new Exception('Main_Helper::createEntry() - 4th parameter must be an array.');
		}

		$entry = new EntryModel();
		$entry->sectionId = craft()->sections->getSectionByHandle($sectionHandle)->id;

		$postDate = new DateTime();
		if ($timestamp)
		{
			$postDate->setTimestamp($timestamp);
		}
		$postDate = DateTime::createFromString($postDate, craft()->timezone);

		$entry->enabled								= TRUE;
		$entry->authorId 							= $author_id;
		$entry->postDate							= $postDate;
		$entry->getContent()->title					= $title;

		foreach($fields as $field_name => $field_value)
		{
			$entry->getContent()->{$field_name} = $field_value;
		}

		if (craft()->entries->saveEntry($entry))
		{
			
		}
		else
		{

			HopAlertsFetchPlugin::log('Tried to create an Alert entry but saving into DB failed: '.$entry->getErrors() , LogLevel::Error);
		}
	}
}