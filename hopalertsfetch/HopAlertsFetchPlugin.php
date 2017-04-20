<?php
/**
 * Hop Alerts Fetch plugin for Craft CMS
 *
 *  Custom plugin to retrieve alert reports from different sources 
 *
 *
 * @author    Hop Studios
 * @copyright Copyright (c) 2017 Hop Studios
 * @link      https://www.hopstudios.com/software/
 * @package   HopAlertsFetch
 * @since     1.0.0
 */

namespace Craft;

class HopAlertsFetchPlugin extends BasePlugin
{
	/**
	 * Called after the plugin class is instantiated; do any one-time initialization here such as hooks and events:
	 *
	 * craft()->on('entries.saveEntry', function(Event $event) {
	 *    // ...
	 * });
	 *
	 * or loading any third party Composer packages via:
	 *
	 * require_once __DIR__ . '/vendor/autoload.php';
	 *
	 * @return mixed
	 */
	public function init()
	{
		parent::init();

		// Load our helpers
		foreach (glob(__DIR__ . "/helpers/*.php") as $filename) {
			include_once $filename;
		}

		// Load APIs
		foreach (glob(__DIR__ . "/api/*.php") as $filename) {
			include_once $filename;
		}
	}

	/**
	 * Returns the user-facing name.
	 *
	 * @return mixed
	 */
	public function getName()
	{
		 return Craft::t('Hop Alerts Fetch');
	}

	/**
	 * Plugins can have descriptions of themselves displayed on the Plugins page by adding a getDescription() method
	 * on the primary plugin class:
	 *
	 * @return mixed
	 */
	public function getDescription()
	{
		return Craft::t(' Custom plugin to retrieve alert reports from different sources ');
	}

	/**
	 * Plugins can have links to their documentation on the Plugins page by adding a getDocumentationUrl() method on
	 * the primary plugin class:
	 *
	 * @return string
	 */
	public function getDocumentationUrl()
	{
		return 'https://github.com/HopStudios/Hop-Alerts-Fetch-Craft';
	}

	/**
	 * Plugins can now take part in Craft’s update notifications, and display release notes on the Updates page, by
	 * providing a JSON feed that describes new releases, and adding a getReleaseFeedUrl() method on the primary
	 * plugin class.
	 *
	 * @return string
	 */
	public function getReleaseFeedUrl()
	{
		return '???';
	}

	/**
	 * Returns the version number.
	 *
	 * @return string
	 */
	public function getVersion()
	{
		return '1.0.0';
	}

	/**
	 * As of Craft 2.5, Craft no longer takes the whole site down every time a plugin’s version number changes, in
	 * case there are any new migrations that need to be run. Instead plugins must explicitly tell Craft that they
	 * have new migrations by returning a new (higher) schema version number with a getSchemaVersion() method on
	 * their primary plugin class:
	 *
	 * @return string
	 */
	public function getSchemaVersion()
	{
		return '1.0.0';
	}

	/**
	 * Returns the developer’s name.
	 *
	 * @return string
	 */
	public function getDeveloper()
	{
		return 'Hop Studios';
	}

	/**
	 * Returns the developer’s website URL.
	 *
	 * @return string
	 */
	public function getDeveloperUrl()
	{
		return 'https://www.hopstudios.com/software/';
	}

	/**
	 * Returns whether the plugin should get its own tab in the CP header.
	 *
	 * @return bool
	 */
	public function hasCpSection()
	{
		return false;
	}

	/**
	 * Called right before your plugin’s row gets stored in the plugins database table, and tables have been created
	 * for it based on its records.
	 */
	public function onBeforeInstall()
	{
	}

	/**
	 * Called right after your plugin’s row has been stored in the plugins database table, and tables have been
	 * created for it based on its records.
	 */
	public function onAfterInstall()
	{
	}

	/**
	 * Called right before your plugin’s record-based tables have been deleted, and its row in the plugins table
	 * has been deleted.
	 */
	public function onBeforeUninstall()
	{
	}

	/**
	 * Called right after your plugin’s record-based tables have been deleted, and its row in the plugins table
	 * has been deleted.
	 */
	public function onAfterUninstall()
	{
	}

	/**
	 * Defines the attributes that model your plugin’s available settings.
	 *
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'alerts_section_handle'             => array(AttributeType::String, 'label' => 'Section for new Alerts', 'default' => ''),
			'alerts_expired_section_handle'     => array(AttributeType::String, 'label' => 'Section for expired Alerts', 'default' => ''),
			'wmata_api_key'                     => array(AttributeType::String, 'label' => 'WMATA API Key', 'default' => ''),
			'twitter_oauth_access_token'        => array(AttributeType::String, 'label' => 'Twitter oauth access token', 'default' => ''),
			'twitter_oauth_access_token_secret' => array(AttributeType::String, 'label' => 'Twitter oauth access token secret', 'default' => ''),
			'twitter_consumer_key'              => array(AttributeType::String, 'label' => 'Twitter consumer key', 'default' => ''),
			'twitter_consumer_secret'           => array(AttributeType::String, 'label' => 'Twitter consumer secret', 'default' => ''),
			'time_refresh'                      => array(AttributeType::Number, 'label' => 'Refresh Time', 'default' => (5*60)),
			'time_expired'                      => array(AttributeType::Number, 'label' => 'Expiration Time', 'default' => (60*60*12)),
			'author_id'                         => array(AttributeType::Number, 'label' => 'Author ID', 'default' => 1),
			'field_handle_alert_type'           => array(AttributeType::String, 'label' => 'Field Handle for Alert Type', 'default' => ''),
			'field_handle_alert_ext_id'         => array(AttributeType::String, 'label' => 'Field Handle for External ID', 'default' => ''),
			'field_handle_alert_body'           => array(AttributeType::String, 'label' => 'Field Handle for Body', 'default' => ''),
			'last_update_bus'                   => array(AttributeType::Number, 'label' => 'Last time Bus alerts were updated', 'default' => 0),
			'last_update_rail'                  => array(AttributeType::Number, 'label' => 'Last time Bus alerts were updated', 'default' => 0),
			'last_update_car'                   => array(AttributeType::Number, 'label' => 'Last time Bus alerts were updated', 'default' => 0),
			'last_update_train_marc'            => array(AttributeType::Number, 'label' => 'Last time MARC train alerts were updated', 'default' => 0),
			'last_update_train_vavre'           => array(AttributeType::Number, 'label' => 'Last time VAVRE train alerts were updated', 'default' => 0),
			'last_update_bus_art'               => array(AttributeType::Number, 'label' => 'Last time ART Bus alerts were updated', 'default' => 0),
			'last_update_bus_montgomery_rideon' => array(AttributeType::Number, 'label' => 'Last time RideOn Bus alerts were updated', 'default' => 0),
			'last_update_bus_dc_circulator'     => array(AttributeType::Number, 'label' => 'Last time DC Circulator Bus alerts were updated', 'default' => 0),
		);
	}

	/**
	 * Returns the HTML that displays your plugin’s settings.
	 *
	 * @return mixed
	 */
	public function getSettingsHtml()
	{
		$sections = craft()->db->createCommand()
			->select('handle as value, name as label')
			->from('sections')
			->order('name')
			->queryAll();

		return craft()->templates->render('hopalertsfetch/HopAlertsFetch_Settings', array(
			'settings' => $this->getSettings(),
			'sections' => $sections
		));
	}

	/**
	 * If you need to do any processing on your settings’ post data before they’re saved to the database, you can
	 * do it with the prepSettings() method:
	 *
	 * @param mixed $settings  The Widget's settings
	 *
	 * @return mixed
	 */
	public function prepSettings($settings)
	{
		// Modify $settings here...

		return $settings;
	}

}