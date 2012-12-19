# WP Radio Playlist #
**Contributors:** BarryCarlyon  
**Donate link:** http://barrycarlyon.co.uk/wordpress/wordpress-plugins/wp-radio-playlist/  
**Tags:** radio, music, playlists  
**Requires at least:** 3.5.0  
**Tested up to:** 3.5.0  
**Stable tag:** 1.0.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Create and display playlists, with optional Change/New/ReEntry display

## Description ##

This Plugin allows site admins to create and display simple playlists.

You can optionally show the change of a given track between last weeks playlist.

Normally expecting a new Playlist to be added weekly, like a radio station.

## Installation ##

1. Download the Plugin from Extend
1. Unzip the Zip File
1. Upload `wp-radio-playlist` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

## Screenshots ##

## Changelog ##

### 1.0.0 ###
* Initial Release

## Usage ##

### Settings ###

Found under Settings -> WP Radio Playlist

Using three custom posts types, one for tracks, artists and playlists to store data.
Under settings you can choose to enable debug mode to review/update the raw posts (just in case).

You can change the date format as well, but the shortcode argument always takes YYYY-MM-DD as it is how WordPress stores that value  in the post_date column.

You can change the number of tracks in a playlist (you can't optionally add extras over this value, but if you have it set to 20 and only fill in the first 15 it intelligently works out what to do).

You can choose which WordPress role can create/edit/delete playlists.

### Playlists ###

A separate menu item is added to add/edit/delete playlists.

### ShortCode ###

**A single shortcode:** [wprp_playlist]  

### ShortCode Arguments ###

No arguments, displays the more recent playlist, with the change column and the drop down selector to change the currently displayed Playlist

* playlist - date in the format of YYYY-MM-DD, show a specific playlist
* selector - boolean show/hide the selector drop down
* change - boolean show/hide the change column.

Due to the several queries and potentially heavy queries involved with working out changes. The results are stored in a WordPress transient for a week. Normally this should be sufficent. But the change value isn't stored in the database, in case you add a playlist in between two existing playlists for some reason. Thus allowing the values to be recalculated.

## Upgrade Notice ##

There is nothing Special to do for all upgrades.

All new settings are spawned to default values automagically

You should however take a Database and File Backup before Hand. Just in case.

## Frequently Asked Questions ##

None Yet

### I need help! ###

Either use the [WordPress Support Forum](http://wordpress.org/support/plugin/wp-radio-playlist), or [Drop Me a Line](http://barrycarlyon.co.uk/wordpress/contact/)

