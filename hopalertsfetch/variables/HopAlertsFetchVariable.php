<?php
/**
 * Hop Alerts Fetch plugin for Craft CMS
 *
 * Hop Alerts Fetch Variable
 *
 * --snip--
 * Craft allows plugins to provide their own template variables, accessible from the {{ craft }} global variable
 * (e.g. {{ craft.pluginName }}).
 *
 * https://craftcms.com/docs/plugins/variables
 * --snip--
 *
 * @author    Hop Studios
 * @copyright Copyright (c) 2017 Hop Studios
 * @link      https://www.hopstudios.com/software/
 * @package   HopAlertsFetch
 * @since     1.0.0
 */

namespace Craft;

class HopAlertsFetchVariable
{
    /**
     * Whatever you want to output to a Twig tempate can go into a Variable method. You can have as many variable
     * functions as you want.  From any Twig template, call it like this:
     *
     *     {{ craft.hopAlertsFetch.exampleVariable }}
     *
     * Or, if your variable requires input from Twig:
     *
     *     {{ craft.hopAlertsFetch.exampleVariable(twigValue) }}
     */
    public function exampleVariable($optional = null)
    {
        return "And away we go to the Twig template...";
    }

    /**
     * Doesn't output anything, simply trigger fetching the alerts
     */
    public function fetchAlerts()
    {
        craft()->hopAlertsFetch_main->fetchAllAlerts();
        return 'fetched';
    }
}