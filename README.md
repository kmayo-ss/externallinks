# External links

## Introduction

The external links module is a task and ModelAdmin to track and to report on broken external links.

## Maintainer Contact

	* Kirk Mayo kirk (at) silverstripe (dot) com

## Requirements

	* SilverStripe 3.0 +

## Features

* Add external links to broken links reports
* Add a task to track external broken links

## Installation

 1. Download the module form GitHub (Composer support to be added)
 2. Extract the file (if you are on windows try 7-zip for extracting tar.gz files
 3. Make sure the folder after being extracted is named 'externallinks'
 4. Place this directory in your sites root directory. This is the one with framework and cms in it.
 5. Run in your browser - `/dev/build` to rebuild the database.
 6. Run the following task *http://path.to.silverstripe/dev/tasks/CheckExternalLinks* to check for broken external links

## Report ##

A new report is added called 'External Broken links report' from here you can also start a new job which is run
via AJAX and in batches of 10 so it can be run via content editors who do not have access to jobs or tasks.

## Dev task ##

Run the following task *http://path.to.silverstripe/dev/tasks/CheckExternalLinks* to check your site for external
broken links.

## Queued job ##

If you have the queuedjobs module installed you can set the task to be run every so ofter
Add the following yml config to config.yml in mysite/_config have the the task run once every day (86400 seconds)

    CheckExternalLinks:
      Delay: 86400

## Whitelisting codes ##

If you want to ignore or whitelist certain http codes this can be setup via IgnoreCodes in the config.yml
file in mysite/_config

    CheckExternalLinks:
      Delay: 60
      IgnoreCodes:
        - 401
        - 403
        - 501
