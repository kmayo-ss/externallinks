# External links

## Introduction

The external links module is a task and ModelAdmin to track and to report on broken external links.

## Maintainer Contact

	* Kirk Mayo kirk (at) silverstripe (dot) com

## Requirements

	* SilverStripe 3.0 +

## Features

* Add external links to broken links reports
* Add a model admin for external broken links
* Add a task to track external broken links

## Installation

 1. Download the module form GitHub (Composer support to be added)
 2. Extract the file (if you are on windows try 7-zip for extracting tar.gz files
 3. Make sure the folder after being extracted is named 'externallinks'
 4. Place this directory in your sites root directory. This is the one with framework and cms in it.
 5. Run in your browser - `/dev/build` to rebuild the database.
 6. You should see a new menu called *Broken Ext. Links*
 7. Run the following task *http://path.to.silverstripe/dev/tasks/CheckExternalLinks*

## Dev task ##

Run the following task *http://path.to.silverstripe/dev/tasks/CheckExternalLinks* to check your site for external
broken links.
If you have the queuedjobs module installed you can set the task to be run every so ofter
Add the following code to the mysite config to run the job every 24 hours (86400 seconds)

`Config::inst()->update('CheckExternalLinks', 'QueuedJob', 86400);`


## Disable the Broken external link menu

To disable the *Broken Ext. Links* menu add the following code to mysite/_config.php

`CMSMenu::remove_menu_item('BrokenExternalLinksAdmin');`

## TODO ##

Fix setting the class attribute of broken links to ss-broken
