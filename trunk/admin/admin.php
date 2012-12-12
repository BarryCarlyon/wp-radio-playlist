<?php

/**
* Admin Controller
*
* @category WordPres_Plugins
* @package  Wordpress_Radio_Playlist
* @author   Barry Carlyon <barry@barrycarlyon.co.uk>
* @license  GPL V2
* @link     noneyet
*/
class Wordpress_Radio_Playlist_Admin
{
    /**
    * Yeah it constructs....
    */
    function __construct()
    {
        $this->setup();
    }

    /**
    * setup those admin hooks
    */
    private function setup()
    {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    /**
    * admin init
    */
    public function admin_init()
    {
        // register a section
        // id, title, callback, page slug
        add_settings_section(
            'wp-radio-playlist-settings',
            __('WP Radio Playlist General Settings', 'wp-radio-playlist'),
            array($this, 'settings_header_general'),
            'wp-radio-playlist-settings'
        );
        add_settings_section(
            'wp-radio-playlist-settings-debug',
            __('WP Radio Playlist Debug Settings', 'wp-radio-playlist'),
            array($this, 'settings_header_debug'),
            'wp-radio-playlist-settings'
        );

        // register field render
        // id, title, callback, page, section, args
        add_settings_field(
            'wp-radio-playlist-raw-posts-tracks',
            __('Show Raw Tracks Post Editor', 'wp-radio-playlist'),
            array($this, 'raw_posts_tracks'),
            'wp-radio-playlist-settings',
            'wp-radio-playlist-settings-debug'
        );
        add_settings_field(
            'wp-radio-playlist-raw-posts-playlists',
            __('Show Raw Playlists Post Editor', 'wp-radio-playlist'),
            array($this, 'raw_posts_playlists'),
            'wp-radio-playlist-settings',
            'wp-radio-playlist-settings-debug'
        );

        // register option
        // option group, option name, sanitize callback function
        register_setting('wp-radio-playlist-settings-debug', 'wp-radio-playlist-raw-posts-tracks');
        register_setting('wp-radio-playlist-settings-debug', 'wp-radio-playlist-raw-posts-playlists');
    }

    /**
    * Admin Menu/Page
    */
    public function admin_menu()
    {
        add_options_page(__('WP Radio Playlist', 'wp-radio-playlist'), __('WP Radio Playlist', 'wp-radio-playlist'), 'activate_plugins', 'wp-radio-playlist-settings', array($this, 'general_settings_page'));
    }

    /**
    * General Settings page
    */
    public function settings_header_general()
    {
        echo __('General Settings', 'wp-radio-playlist');
    }
    public function settings_header_debug()
    {
        echo __('Debug Settings', 'wp-radio-playlist');
    }

    /**
    * General Settings page
    */
    public function general_settings_page()
    {
        echo '<form method="post" action="options.php">';
        settings_fields('wp-radio-playlist-settings');
        do_settings_sections('wp-radio-playlist-settings');

        settings_fields('wp-radio-playlist-settings-debug');
        do_settings_sections('wp-radio-playlist-settings-debug');

        echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="' . __('Save Changes', 'wp-radio-playlist') . '"></p>';
        echo '</form>';
    }

    /**
    * Settings API outputs
    */
    public function raw_posts_tracks()
    {
        $this->bool('wp-radio-playlist-raw-posts-tracks');
    }
    public function raw_posts_playlists()
    {
        $this->bool('wp-radio-playlist-raw-posts-playlists');
    }

    private function bool($option)
    {
        echo '<input name="' . $option . '" id="' . $option . '" type="checkbox" value="1" class="code" ' . checked( 1, get_option($option), false ) . ' />';
    }
}
