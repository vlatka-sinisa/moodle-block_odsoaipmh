Description
===========
This Moodle block plugin serves as Data Provider for OAI-PMH 2.0 Harvester.
Plugin enables Moodle administrator to define global settings for Data Provider
as well as default metadata sharing for courses.

Course administrators can define if their course is shared, what are the
licensing terms and Moodle plugins which data should be shared.
ODS OAI-PMH supports all Moodle 2.x default modules, but also tries to support
all other unknown modules. For unknown modules description for each item/module
is decided using advanced heuristic algorithm.

In order to maintain the reliability of the Moodle server all data is cached,
and server administrator can also define what harvesters are allowed to access
the data. It is also possible to define length of caching for all data.

This is transient OAI-PMH provider, which means that all deleted data, is
retained in the cache log.

All data items are provided with unique URL for direct access to the item.
Access to the item is regulated by Moodle permission settings. This module
does not allow access to the content, but only shares metadata information
about all items that are allowed by the course administrator or Moodle
administrator to be harvested.

OAI-PMH target
==============
<moodle URL>/blocks/odsoaipmh/oaipmh/index.php

Installation
============
Using standard Moodle installation procedure
https://moodle.org/plugins/view.php?plugin=block_odsoaipmh

Uninstallation
==============
Using standard Moodle uninstallation procedure

Configuration
=============
Server and course administrator
