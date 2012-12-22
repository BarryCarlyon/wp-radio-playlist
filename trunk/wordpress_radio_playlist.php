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

define('WPRP_DIR', plugin_dir_path(__FILE__));
define('WPRP_INCLUDES_DIR', plugin_dir_path(__FILE__) . 'includes/');
define('WPRP_EXTRAS_DIR', plugin_dir_path(__FILE__) . 'extras/');

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
        include_once WPRP_INCLUDES_DIR . 'utilities.php';
        include_once WPRP_INCLUDES_DIR . 'settings.php';

        $this->_extras();

        if (is_admin()) {
            include WPRP_DIR . '/admin/admin.php';
            new Wordpress_Radio_Playlist_Admin();
        } else {
            include WPRP_DIR . '/front/front.php';
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
    * Check for load and init extras
    *
    * @return nothing
    */
    private function _extras()
    {
        $dir = new FilesystemIterator(WPRP_EXTRAS_DIR);
        foreach ($dir as $path => $fileinfo) {
            if ($fileinfo->isFile()) {
                if ($fileinfo->getExtension() == 'php') {
                    $name = substr($fileinfo->getFilename(), 0, -4);
                    include $path;
                    $bool = 'wp-radio-playlist-extras-' . strtolower($name);
                    $class = 'Wordpress_Radio_Playlist_Extras_' . $name;
                    if (get_option($bool, 0)) {
                        new $class();
                    }
                    $class .= '_Settings';
                    new $class();
                }
            }
        }
    }

    /**
    * Setup Custom Post Types things
    * 
    * @return nothing
    */
    public function postTypes()
    {
        include WPRP_INCLUDES_DIR . 'post_types.php';
        return;
    }
}
new Wordpress_Radio_Playlist();
