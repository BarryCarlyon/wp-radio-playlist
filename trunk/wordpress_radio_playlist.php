<?php

/*
 Plugin Name: WP Radio Playlist
 Plugin URI: http://barrycarlyon.co.uk/
 Description: Beep
 Author: Barry Carlyon
 Author URI: http://www.barrycarlyon.co.uk
 Version: 0.0.1
 */

/**
* Yeah it constructs....
*
* $Id: ym.php 2465 2012-12-06 15:48:46Z bcarlyon $
* $Revision: 2465 $
* $Date: 2012-12-06 15:48:46 +0000 (Thu, 06 Dec 2012) $
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
        $this->setup();

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
    */
    private function setup()
    {
        /**
        Begin hooking
        */
        add_action('init', array($this, 'post_types'));

        load_plugin_textdomain('wp-radio-playlist', false, basename(dirname(__FILE__)), '/languages');

        /**
        Optional commons
        */

        return;
    }

    /**
    * Setup Custom Post Types things
    */
    public function post_types()
    {
        // custom post type - tracks
        register_post_type(
            'wprp_track',
            array(
                'label'                 => __('Tracks', 'wp-radio-playlist'),
                'labels'                => array(
                    'name' => __('Tracks', 'wp-radio-playlist'),
                    'singular_name' => __('Track', 'wp-radio-playlist'),
                    'add_new' => __('Add New', 'wp-radio-playlist'),
                    'add_new_item' => __('Add New Track', 'wp-radio-playlist'),
                    'edit_item' => __('Edit Track', 'wp-radio-playlist'),
                    'new_item' => __('New Track', 'wp-radio-playlist'),
                    'all_items' => __('All Tracks', 'wp-radio-playlist'),
                    'view_item' => __('View Tracks', 'wp-radio-playlist'),
                    'search_items' => __('Search Tracks', 'wp-radio-playlist'),
                    'not_found' =>  __('No Tracks found', 'wp-radio-playlist'),
                    'not_found_in_trash' => __('No Tracks found in Trash', 'wp-radio-playlist'), 
                    'parent_item_colon' => '',
                    'menu_name' => __('Tracks', 'wp-radio-playlist')
                ),
                'public'                => (get_option('wp-radio-playlist-raw-posts-tracks', false) ? true : false),
                'supports'              => array(
                    'title',
                    'custom-fields',
                ),
                'has_archive'           => false,
                'publicly_queryable'    => false,
                'exclude_from_search'   => true,
                'can_export'            => true,
//                'menu_icon'             => plugin_dir_url(__FILE__) . 'img/zombaio_icon.png',
            )
        );

        // custom post type - playlist
        register_post_type(
            'wprp_playlist',
            array(
                'label'                 => __('Playlists', 'wp-radio-playlist'),
                'labels'                => array(
                    'name' => __('Playlists', 'wp-radio-playlist'),
                    'singular_name' => __('Playlist', 'wp-radio-playlist'),
                    'add_new' => __('Add New', 'wp-radio-playlist'),
                    'add_new_item' => __('Add New Playlist', 'wp-radio-playlist'),
                    'edit_item' => __('Edit Playlist', 'wp-radio-playlist'),
                    'new_item' => __('New Playlist', 'wp-radio-playlist'),
                    'all_items' => __('All Playlists', 'wp-radio-playlist'),
                    'view_item' => __('View Playlists', 'wp-radio-playlist'),
                    'search_items' => __('Search Playlists', 'wp-radio-playlist'),
                    'not_found' =>  __('No Playlists found', 'wp-radio-playlist'),
                    'not_found_in_trash' => __('No Playlists found in Trash', 'wp-radio-playlist'), 
                    'parent_item_colon' => '',
                    'menu_name' => __('Playlists', 'wp-radio-playlist')
                ),
                'public'                => (get_option('wp-radio-playlist-raw-posts-playlists', false) ? true : false),
                'supports'              => array(
                    'title',
                    'custom-fields',
                ),
                'has_archive'           => false,
                'publicly_queryable'    => false,
                'exclude_from_search'   => true,
                'can_export'            => true,
//                'menu_icon'             => plugin_dir_url(__FILE__) . 'img/zombaio_icon.png',
            )
        );

        return;
    }
}
new Wordpress_Radio_Playlist();
