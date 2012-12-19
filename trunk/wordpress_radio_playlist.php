<?php

/**
 Plugin Name: WP Radio Playlist
 Plugin URI: http://barrycarlyon.co.uk/
 Description: Beep
 Author: Barry Carlyon
 Author URI: http://www.barrycarlyon.co.uk
 Version: 1.0.0
 */

/**
* Yeah it constructs....
*
* PHP Version 5
* 
* @category WordPres_Plugins
* @package  Wordpress_Radio_Playlist
* @author   Barry Carlyon <barry@barrycarlyon.co.uk>
* @license  GPL V2
* @link     noneyet
*/

/**
* Main caller/constants includer/commons
*/
class Wordpress_Radio_Playlist
{
    /**
    * Yeah it constructs....
    */
    public function __construct()
    {
        $this->_setup();
        include 'includes/utilities.php';

        if (is_admin()) {
            include 'admin/admin.php';
            new Wordpress_Radio_Playlist_Admin();
        } else {
            include 'front/front.php';
            new Wordpress_Radio_Playlist_Front();
        }

        return;
    }

    /**
    * Start setting up hooks
    *
    * @return nothing
    */
    private function _setup()
    {
        /**
        Begin hooking
        */
        add_action('init', array($this, 'postTypes'));

        load_plugin_textdomain(
            'wp-radio-playlist',
            false,
            basename(dirname(__FILE__)) . '/languages'
        );

        /**
        Optional commons
        */

        return;
    }

    /**
    * Setup Custom Post Types things
    * 
    * @return nothing
    */
    public function postTypes()
    {
        include 'includes/post_types.php';
        return;
    }
}
new Wordpress_Radio_Playlist();
