<?php

namespace Craft;

class Main_Helper
{
	public static function createEntry($sectionHandle, $author_id, $title, $type, $type_handle, $custom_id, $custom_id_handle, $content, $content_handle, $timestamp = NULL)
	{
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
		$entry->getContent()->{$type_handle}		= $type;
		$entry->getContent()->{$custom_id_handle}	= $custom_id;
		$entry->getContent()->{$content_handle}		= $content;

		$entry->getContent()->title = $title;

		if (craft()->entries->saveEntry($entry))
		{
			
		}
		else
		{

			HopAlertsFetchPlugin::log('Tried to create an Alert entry but saving into DB failed: '.$entry->getErrors() , LogLevel::Error);
		}
	}
}