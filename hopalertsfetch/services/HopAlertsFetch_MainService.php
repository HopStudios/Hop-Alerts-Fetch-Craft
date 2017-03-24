<?php
/**
 * Hop Alerts Fetch plugin for Craft CMS
 *
 * HopAlertsFetch_Main Service
 *
 * --snip--
 * All of your plugin’s business logic should go in services, including saving data, retrieving data, etc. They
 * provide APIs that your controllers, template variables, and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 * --snip--
 *
 * @author    Hop Studios
 * @copyright Copyright (c) 2017 Hop Studios
 * @link      https://www.hopstudios.com/software/
 * @package   HopAlertsFetch
 * @since     1.0.0
 */

namespace Craft;

class HopAlertsFetch_MainService extends BaseApplicationComponent
{
    private $_settings = array();

    public function __construct()
    {
        $this->_settings = craft()->plugins->getPlugin('HopAlertsFetch')->getSettings();
    }

    /**
     * This function can literally be anything you want, and you can have as many service functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     craft()->hopAlertsFetch_main->exampleService()
     */
    public function exampleService()
    {
    }

    public function fetchAllAlerts()
    {
        $this->fetchWmataAlerts();
    }

    public function startFetchTask()
    {
        // TODO : use Task later
    }

    public function fetchWmataAlerts()
    {
        $helper = new Wmata_Helper($this->_settings->wmata_api_key);
    }

    public function fetchTwitterAlerts()
    {

    }

    public function fetchRSSAlerts()
    {

    }

}