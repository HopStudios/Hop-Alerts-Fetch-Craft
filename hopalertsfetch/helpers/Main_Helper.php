<?php

namespace Craft;

class Main_Helper
{
	public static function createEntry($sectionHandle, $authorID, $title, $type, $customID, $content, $timestamp = NULL)
	{
		$entry = new EntryModel();
		$entry->sectionId = craft()->sections->getSectionByHandle($sectionHandle)->id;

		$postDate = new DateTime();
		if ($timestamp)
		{
			$postDate->setTimestamp($timestamp);
		}
		$postDate = DateTime::createFromString($postDate, craft()->timezone);

		$entry->authorId      = $authorID;
		$entry->postDate      = $postDate;
		// $entry->expiryDate    = $expiryDate;
		$entry->enabled       = TRUE;

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