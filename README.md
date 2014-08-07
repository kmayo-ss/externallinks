# External links

[![Build Status](https://travis-ci.org/silverstripe-labs/silverstripe-externallinks.svg?branch=master)](https://travis-ci.org/silverstripe-labs/silverstripe-externallinks)

## Introduction

The external links module is a task and ModelAdmin to track and to report on broken external links.

## Maintainer Contact

	* Kirk Mayo kirk (at) silverstripe (dot) com

## Requirements

	* SilverStripe 3.1 +

## Features

* Add external links to broken links reports
* Add a task to track external broken links

## Installation

 1. If you have composer you can use `composer require silverstripe/externallinks:*`. Otherwise, 
    download the module from GitHub and extract to the 'externallinks' folder. Place this directory
    in your sites root directory. This is the one with framework and cms in it.
 2. Run in your browser - `/dev/build` to rebuild the database.
 3. Run the following task *http://path.to.silverstripe/dev/tasks/CheckExternalLinks* to check for broken external links

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
