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
    private $access_role;
    private $php_date_format;
    private $jquery_date_format;

    /**
    * Yeah it constructs....
    */
    function __construct()
    {
        include 'ajax.php';
        include 'settings.php';
        $this->setup();
    }

    /**
    * setup those admin hooks
    */
    private function setup()
    {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));

        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('admin_head', array($this, 'admin_head'));

        new Wordpress_Radio_Playlist_Settings();
        new Wordpress_Radio_Playlist_Admin_Ajax();
    }

    function admin_scripts()
    {
        wp_enqueue_style('jquery-ui', 'http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css');
        wp_enqueue_script('jquery-ui-datepicker');
    }
    function admin_head()
    {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('.wprp_date').datepicker({dateFormat: '<?php echo $this->jquery_date_format; ?>'});
});
</script>
<?php
    }

    /**
    * Admin Menu/Page
    */
    public function admin_menu()
    {
        // convert role
        $target_role = '';
        global $wp_roles;
        foreach ($wp_roles->roles as $role) {
            if ($role['name'] == get_option('wp-radio-playlist-wprole')) {
                $this->target_role = array_pop(array_keys($role['capabilities']));
                break;
            }
        }

        add_menu_page(__('Playlists', 'wp-radio-playlist'), __('Playlists', 'wp-radio-playlist'), $this->target_role, 'wprp_playlist', array($this, 'wprp_playlist_index'));
    }

    /**
    * admin init
    */
    public function admin_init()
    {
        $formats = split('-', get_option('wp-radio-playlist-dateformat', 'mm/dd/yy-m/d/Y'));
        $this->php_date_format = $formats[1];
        $this->jquery_date_format = $formats[0];
    }

    public function wprp_playlist_index()
    {
        if ($_POST)
        {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'wprp_playlist_create'))
            {
                echo '<div id="message" class="error"><p>' . __('Nonce Failed Verfication', 'wp-radio-playlist') . '</p></div>';
            } else {
                $artists = wprp_post('artist', array());
                $tracks = wprp_post('track', array());

                $playlist = array();

                for ($x = 1; $x <= get_option('wp-radio-playlist-tracks-in-list', 20); $x++)
                {
                    // artist
                    $artist = isset($artists[$x]) ? $artists[$x] : false;
                    $track = isset($tracks[$x]) ? $tracks[$x] : false;

                    if ($artist && $track)
                    {
                        $artist_id = wprp_get_artist_id($artist);
                        if (!$artist_id)
                        {
                            $post = array(
                                'post_status' => 'publish',
                                'post_title' => $artist,
                                'post_type' => 'wprp_artist'
                            );
                            $artist_id = wp_insert_post($post);
                        }

                        $track_id = wprp_get_track_id_by_artist_id($track, $artist_id);
                        if (!$track_id)
                        {
                            $post = array(
                                'post_status' => 'publish',
                                'post_title' => $track,
                                'post_type' => 'wprp_track'
                            );
                            $track_id = wp_insert_post($post);
                            update_post_meta($track_id, 'wprp_artist', $artist_id);
                        }

                        $playlist[$x] = array($artist_id, $track_id);
                    }
                }
            }
        }
        echo '<div class="wrap">';
        screen_icon();
        echo '<h2>' . __('WP Radio Playlist', 'wp-radio-playlist') . '</h2>';

        echo '<form method="post" action="">';
        wp_nonce_field('wprp_playlist_create');

        $artists = wprp_post('artist', array());
        $tracks = wprp_post('track', array());

        $start_date = '';

        $start_date = wprp_next_monday($this->php_date_format);

        echo '<table class="widefat">';
        echo '<tbody>';

        // data
        echo '<tr>';
        echo '<th>';
        echo __('Start Date', 'wp-radio-playlist');
        echo '</th>';
        echo '<td><input type="text" name="start_date" id="start_date" class="wprp_date" value="' . $start_date . '" /></td>';
        echo '</tr>';

/*
        echo '<tr>';
        echo '<th>';
        echo __('End Date', 'wp-radio-playlist');
        echo '</th>';
        echo '<td><input type="text" name="end_date" id="end_date" class="wprp_date" value="' . $end_date . '" /></td>';
        echo '</tr>';
*/

        echo '</tbody>';
        echo '</table>';

        echo '<hr />';

        echo '<table class="widefat">';
        echo '
<thead>
    <tr><th>#</th>
    <th>' . __('Artist', 'wp-radio-playlist') . '</th>
    <th>' . __('Track', 'wp-radio-playlist') . '</th>
</thead>
<tfoot>
    <tr><th>#</th>
    <th>' . __('Artist', 'wp-radio-playlist') . '</th>
    <th>' . __('Track', 'wp-radio-playlist') . '</th>
</tfoot>
';
        echo '<tbody>';
        // tracks
        for ($x = 1; $x <= get_option('wp-radio-playlist-tracks-in-list', 20); $x++)
        {
            $artist = isset($artists[$x]) ? $artists[$x] : '';
            $track = isset($tracks[$x]) ? $tracks[$x] : '';

            echo '<tr>';
            echo '<td>' . $x . '</td>';
            echo '<td><input type="text" class="wprp_artist" name="artist[' . $x . ']" style="width: 100%;" value="' . $artist . '" /></td>';
            echo '<td><input type="text" class="wprp_track" name="track[' . $x . ']" style="width: 100%;" value="' . $track . '" /></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';

        echo '<hr />';

        echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary alignright" value="' . __('Submit Playlist', 'wp-radio-playlist') . '"></p>';        
        echo '</form>';

        echo '</div>';
    }
}
