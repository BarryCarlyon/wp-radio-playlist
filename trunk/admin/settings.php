<?php

class Wordpress_Radio_Playlist_Settings
{
    function __construct()
    {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    public function admin_menu()
    {
        add_options_page(__('WP Radio Playlist', 'wp-radio-playlist'), __('WP Radio Playlist', 'wp-radio-playlist'), 'activate_plugins', 'wp-radio-playlist-settings', array($this, 'settings_page'));
    }

    public function admin_init()
    {
        // register a section
        // id, title, callback, page slug
        add_settings_section(
            'wp-radio-playlist-settings-general',
            __('General Settings', 'wp-radio-playlist'),
            array($this, 'settings_header_general'),
            'wp-radio-playlist-settings'
        );
        add_settings_section(
            'wp-radio-playlist-settings-debug',
            __('Debug Settings', 'wp-radio-playlist'),
            array($this, 'settings_header_debug'),
            'wp-radio-playlist-settings'
        );

        // register field render
        // id, title, callback, page, section, args
        add_settings_field(
            'wp-radio-playlist-wprole',
            __('Required Role to CRUD', 'wp-radio-playlist'),
            array($this, 'wprole'),
            'wp-radio-playlist-settings',
            'wp-radio-playlist-settings-general'
        );
        add_settings_field(
            'wp-radio-playlist-tracks-in-list',
            __('Number of Tracks in a Playlist', 'wp-radio-playlist'),
            array($this, 'tracksinlist'),
            'wp-radio-playlist-settings',
            'wp-radio-playlist-settings-general'
        );

        add_settings_field(
            'wp-radio-playlist-dateformat',
            __('Date Format', 'wp-radio-playlist'),
            array($this, 'dateformat'),
            'wp-radio-playlist-settings',
            'wp-radio-playlist-settings-general'
        );

        add_settings_field(
            'wp-radio-playlist-raw-posts-tracks',
            __('Show Raw Tracks Post Editor', 'wp-radio-playlist'),
            array($this, 'raw_posts_tracks'),
            'wp-radio-playlist-settings',
            'wp-radio-playlist-settings-debug'
        );
        add_settings_field(
            'wp-radio-playlist-raw-posts-artists',
            __('Show Raw Artists Post Editor', 'wp-radio-playlist'),
            array($this, 'raw_posts_artists'),
            'wp-radio-playlist-settings',
            'wp-radio-playlist-settings-debug'
        );        add_settings_field(
            'wp-radio-playlist-raw-posts-playlists',
            __('Show Raw Playlists Post Editor', 'wp-radio-playlist'),
            array($this, 'raw_posts_playlists'),
            'wp-radio-playlist-settings',
            'wp-radio-playlist-settings-debug'
        );

        // register option
        // option group, option name, sanitize callback function
        register_setting('wp-radio-playlist-settings', 'wp-radio-playlist-wprole');
        register_setting('wp-radio-playlist-settings', 'wp-radio-playlist-tracks-in-list');

        register_setting('wp-radio-playlist-settings', 'wp-radio-playlist-dateformat');

        register_setting('wp-radio-playlist-settings', 'wp-radio-playlist-raw-posts-tracks');
        register_setting('wp-radio-playlist-settings', 'wp-radio-playlist-raw-posts-artists');
        register_setting('wp-radio-playlist-settings', 'wp-radio-playlist-raw-posts-playlists');
    }

    /**
    * General Settings page
    */
    public function settings_header_general()
    {
        echo '';//__('General Settings', 'wp-radio-playlist');
    }
    public function settings_header_debug()
    {
        echo '';//__('Debug Settings', 'wp-radio-playlist');
    }

    /**
    * General Settings page
    */
    public function settings_page()
    {
        echo '<div class="wrap">';
        screen_icon();
        echo '<h2>' . __('WP Radio Playlist Settings', 'wp-radio-playlist') . '</h2>';

        echo '<form method="post" action="options.php">';

        settings_fields('wp-radio-playlist-settings');
        do_settings_sections('wp-radio-playlist-settings');

        echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="' . __('Save Changes', 'wp-radio-playlist') . '"></p>';
        echo '</form>';

        echo '</div>';
    }

    /**
    * Settings API outputs
    */
    public function wprole()
    {
        global $wp_roles;

        $options = array();
        foreach ($wp_roles->roles as $role) {
            $options[$role['name']] = $role['name'];
        }

        $this->option('wp-radio-playlist-wprole', $options, 'Administrator');
    }
    public function tracksinlist()
    {
        $this->number('wp-radio-playlist-tracks-in-list', 20);
    }
    public function dateformat() {
        $options = array(
            'mm/dd/yy-m/d/Y' => 'm/d/y',
            'dd/mm/yy-d/m/Y' => 'd/m/y',
        );
        $this->option('wp-radio-playlist-dateformat', $options, 'mm/dd/yy-m/d/Y');
    }

    public function raw_posts_tracks()
    {
        $this->bool('wp-radio-playlist-raw-posts-tracks', 0);
    }
    public function raw_posts_artists()
    {
        $this->bool('wp-radio-playlist-raw-posts-artists', 0);
    }
    public function raw_posts_playlists()
    {
        $this->bool('wp-radio-playlist-raw-posts-playlists', 0);
    }

    /**
    * Input types
    */
    private function bool($option, $default)
    {
        echo '<input name="' . $option . '" id="' . $option . '" type="checkbox" value="1" class="code" ' . checked( 1, get_option($option, $default), false ) . ' />';
    }
    private function text($option, $default)
    {
        echo '<input name="' . $option . '" id="' . $option . '" type="text" value="' . get_option($option, $default) . '" class="code" />';
    }
    private function number($option, $default, $args = array())
    {
        $args = array(
            'step'      => isset($args['step']) ? $args['step'] : 1,
            'min'       => isset($args['min']) ? $args['min'] : 1,
            'max'       => isset($args['max']) ? $args['max'] : 999,
            'maxlength' => isset($args['maxlength']) ? $args['maxlength'] : 3,
        );

        echo '<input name="' . $option . '" id="' . $option . '" type="number" value="' . get_option($option, $default) . '" min="' . $args['min'] . '" max="' . $args['max'] . '" step="' . $args['step'] . '" maxlength="' . $args['maxlength'] . '" />';
    }
    private function option($option, $options, $default)
    {
        echo '<select name="' . $option . '" id="' . $option . '">';
        foreach ($options as $index => $item) {
            echo '<option value="' . $index . '" ';
            echo selected(get_option($option, $default), $index);
            echo '>' . $item . '</option>';
        }
        echo '</select>';
    }
}