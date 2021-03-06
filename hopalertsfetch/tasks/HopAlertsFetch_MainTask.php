<?php
/**
 * Hop Alerts Fetch plugin for Craft CMS
 *
 * HopAlertsFetch_Main Task
 *
 * --snip--
 * Tasks let you run background processing for things that take a long time, dividing them up into steps.  For
 * example, Asset Transforms are regenerated using Tasks.
 *
 * Keep in mind that tasks only get timeslices to run when Craft is handling requests on your website.  If you
 * need a task to be run on a regular basis, write a Controller that triggers it, and set up a cron job to
 * trigger the controller.
 *
 * https://craftcms.com/classreference/services/TasksService
 * --snip--
 *
 * @author    Hop Studios
 * @copyright Copyright (c) 2017 Hop Studios
 * @link      https://www.hopstudios.com/software/
 * @package   HopAlertsFetch
 * @since     1.0.0
 */

namespace Craft;

class HopAlertsFetch_MainTask extends BaseTask
{
    /**
     * Defines the settings.
     *
     * @access protected
     * @return array
     */

    protected function defineSettings()
    {
        return array(
            'time_refresh'                      => AttributeType::Number,
            'time_expired'                      => AttributeType::Number,
            'wmata_api_key'                     => AttributeType::String,
            'twitter_oauth_access_token'        => AttributeType::String,
            'twitter_oauth_access_token_secret' => AttributeType::String,
            'twitter_consumer_key'              => AttributeType::String,
            'twitter_consumer_secret'           => AttributeType::String,
            'last_update_bus'                   => AttributeType::Number,
            'last_update_rail'                  => AttributeType::Number,
            'last_update_car'                   => AttributeType::Number,
            'last_update_train_marc'            => AttributeType::Number,
            'last_update_train_vavre'           => AttributeType::Number,
            'last_update_bus_art'               => AttributeType::Number,
            'last_update_bus_montgomery_rideon' => AttributeType::Number,
            'last_update_bus_dc_circulator'     => AttributeType::Number,
        );
    }

    /**
     * Returns the default description for this task.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'HopAlertsFetch_Main Tasks';
    }

    /**
     * Gets the total number of steps for this task.
     *
     * @return int
     */
    public function getTotalSteps()
    {
        return 3;
    }

    /**
     * Runs a task step.
     *
     * @param int $step
     * @return bool
     */
    public function runStep($step)
    {
        switch ($step)
        {
            case 1:

                break;
        }
        return true;
    }
}
