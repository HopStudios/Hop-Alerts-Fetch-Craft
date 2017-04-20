# Hop Alerts Fetch plugin for Craft CMS

Custom plugin to retrieve alert reports from different sources 

## Installation

To install Hop Alerts Fetch, follow these steps:

1. Download & unzip the file and place the `hopalertsfetch` directory into your `craft/plugins` directory
2.  -OR- do a `git clone https://github.com/HopStudios/Hop-Alerts-Fetch-Craft.git` directly into your `craft/plugins` folder.  You can then update it with `git pull`
3. Install plugin in the Craft Control Panel under Settings > Plugins
4. The plugin folder should be named `hopalertsfetch` for Craft to see it.  GitHub recently started appending `-master` (the branch name) to the name of the folder for zip file downloads.

Hop Alerts Fetch works on Craft 2.4.x and Craft 2.5.x.

## Hop Alerts Fetch Overview

This custom plugin is fetching data from different sources (RSS feed, Twitter feed and API) and saves it as entries in Craft. This data is about commuting incidents in the DC area.

The plugin requires 2 channels, one for the active incidents, one for the closed incidents. Incidents will be moved from one channel to another automatically. The plugin is made so no action is required from the user.

## Usage

`{{ craft.hopAlertsFetch.fetchAlerts }}`

Use that tag in any page to trigger the plugin to fetch entries. This will only fetch entries from sources, if the last check of that source occured x seconds ago (that time is determined in the plugin settings).

To display the entries, just use the regular Craft tags `{% for entry in craft.entries.section('incidents').find() %}`.

## Hop Alerts Fetch Roadmap

Some things to do, and ideas for potential features:

* Release it

Brought to you by [Hop Studios](https://www.hopstudios.com/software/)
