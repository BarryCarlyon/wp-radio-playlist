<?php

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
    * Options init
    */
    public function init($santiy = false)
    {
        $options = get_option('wp-radio-playlist', false);
        $save = false;
        if (!$options) {
            $options = $this->sanity_check();
        } else {
            $test = $this->sanity_check();
            foreach ($test as $item => $value) {
                if (!isset($options->$item)) {
                    $save = TRUE;
                    $options->$item = $value;
                }
            }
        }
        $this->options = $options;
        if ($save) {
            $this->saveoptions();
        }
        return;
    }

    /**
    * Options
    */
    private function sanity_check()
    {
        $options = new stdClass();

        $options->raw_posts_tracks = false;

        return $options;
    }

    /**
    * Save options
    * @return bool success/fail of option saving
    */
    private function saveoptions()
    {
        return update_option('wp-radio-playlist', $this->options);
    }

    /**
    * Setup Custom Post Types things
    */
    public function post_types()
    {
        // custom post type - tracks
        register_post_type(
            'wp_radio_playlist_track',
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
                'public'                => ($this->options->raw_posts_tracks ? TRUE : FALSE),
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
    }
}
new Wordpress_Radio_Playlist();
