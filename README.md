# Bernie for President in 2016!
Events for Bernie
All events related to Bernie. Townhall meetings, meetups, etc. Click on state to filter results.

# Events Scraper

Make sure you have www.bernie2016events-local.org as a hostname

If possible set port to 8082

All Grups and pages in `./php-script/groups.json` and `./php-script/pages.json`

## To get Facebook data

* http://www.bernie2016events-local.org:8082/php-script/pull-groups.php -- MUCH PREFERRED!
* http://www.bernie2016events-local.org:8082/php-script/pull-group-events.php

Note: in `pull-groups.php` interchange LINE `54` and `55` for different data sources.

## To get Meetup Data

* http://www.bernie2016events-local.org:8082/php-script/pull-meetup.php

## Post-process

1. Replace <TAB> with \t in Sublime2 or any text processor,
2. Post in Google Spreadsheet or any spreadsheet


## Datasource

Makes use of Google Spreadsheets for easy collaboration: https://docs.google.com/spreadsheets/d/1IaJQtbrsb8_bxpoayN-DhgAb3o_RMUDZyI4TwADmM1g/edit?usp=sharing

## All Events Map
Uses MapBox / Leaflet + D3

Zipcode source: [geonames.org](http://www.geonames.org/)

## By State

Inspired by [r-bloggers](http://www.r-bloggers.com/animated-us-hexbin-map-of-the-avian-flu-outbreak). Uses [d3](http://www.d3js.org) [hexbin plugin](https://github.com/d3/d3-plugins/tree/master/hexbin).

[Bernie Sanders for president!](www.berniesanders.com)
[Reddit for Bernie](http://www.reddit.com/SandersForPresident)
