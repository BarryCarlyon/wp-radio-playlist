<?php

/**
* spotifyplay Extra
*
* @category WordPres_Plugins
* @package  Wordpress_Radio_Playlist
* @author   Barry Carlyon <barry@barrycarlyon.co.uk>
* @license  GPL V2
* @link     noneyet
*/
class Wordpress_Radio_Playlist_Extras_Spotifyplay
{
    function __construct()
    {
        if (get_option('wp-radio-playlist-extras-spotifyplay', 0)) {
            if (is_admin()) {
                if (get_option('wp-radio-playlist-raw-posts-tracks', 0)) {
                    add_action('admin_head', array($this, 'admin_head'));
                    add_action('wprp_track_meta_box_cb', array($this, 'wprp_track_meta_box_cb'));
                }

                add_action('wp_ajax_wprp_spotify_lookup_tracks', array($this, 'wp_ajax_wprp_spotify_lookup_tracks'));
                add_action('wp_ajax_wprp_spotify_get_track', array($this, 'wp_ajax_wprp_spotify_get_track'));

                add_filter('wprp_playlist_extra_form_headers', array($this, 'wprp_playlist_extra_form_headers'), 10, 2);
                add_filter('wprp_playlist_extra_form_columns', array($this, 'wprp_playlist_extra_form_columns'), 10, 2);
            }
        }
    }

    /**
    Default Controller
    */
    function admin_head()
    {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('#wprp_lookup_tracks').click(function() {
        jQuery('#wprp_found_tracks').html('<?php _e('Loading', 'wp-radio-playlist') ?>');
        jQuery.get(ajaxurl + '?action=wprp_spotify_lookup_tracks&postid=' + jQuery('#post_ID').val(), function(data) {
            jQuery('#wprp_found_tracks').html(data);
        });
    });
    jQuery('#spotify_play_button').on('click', '.wprp_use_track', function(event) {
        jQuery('#wprp_selected_track').html('<?php _e('Loading', 'wp-radio-playlist') ?>');
        jQuery('#wprp_found_tracks').html('');
        jQuery.get(ajaxurl + '?action=wprp_spotify_get_track&uri=' + jQuery(this).attr('data-uri') + '&postid=' + jQuery('#post_ID').val(), function(data) {
            jQuery('#wprp_selected_track').html(data);
        });
    });
});
</script>
<?php
    }

    public function wprp_track_meta_box_cb()
    {
        add_meta_box('spotify_play_button', __('Spotify Play', 'wp-radio-playlist'), array($this, 'wprp_spotify_play_button_form'), 'wprp_track');
    }

    public function wprp_spotify_play_button_form($post) {
        echo '<div id="wprp_selected_track">';

        $uri = get_post_meta($post->ID, 'wprp_spotify_uri', true);
        if ($uri) {
            echo '<iframe src="https://embed.spotify.com/?uri=';
            echo $uri;
            echo '" width="300" height="100" frameborder="0" allowtransparency="true"></iframe>';
        }

        echo '</div>';
        echo '<div id="wprp_found_tracks"></div>';
        echo '<input type="button" id="wprp_lookup_tracks" class="button-secondary" value="' . __('Lookup Tracks', 'wp-radio-playlist') . '" />';
    }

    /**
    // ajax
    */
    function wp_ajax_wprp_spotify_lookup_tracks() {
        $postid = wprp_get('postid');

        include WPRP_INCLUDES_DIR . 'spotify.class.php';

        $track_name = wprp_item_title($postid);
        $artist_id = get_post_meta($postid, 'wprp_artist', true);
        $artist_name = wprp_item_title($artist_id);

        $spotify = new Spotify();
        $tracks = $spotify->search_track($track_name);

        echo '<table>';
        foreach ($tracks->tracks as $track) {
            if ($track->artists[0]->name == $artist_name) {
                echo '<tr><td>';
                echo '<iframe src="https://embed.spotify.com/?uri=';
                echo $track->href;
                echo '" width="300" height="80" frameborder="0" allowtransparency="true"></iframe>';
                echo '</td><td>';
                echo '<input type="button" class="wprp_use_track" class="button-secondary" value="' . __('Use Track', 'wp-radio-playlist') . '" data-uri="';
                echo $track->href;
                echo '" />';
                echo '</td></tr>';
            }
        }
        echo '</table>';
        die();
    }

    function wp_ajax_wprp_spotify_get_track() {
        $postid = wprp_get('postid');
        update_post_meta($postid, 'wprp_spotify_uri', wprp_get('uri'));

        echo '<iframe src="https://embed.spotify.com/?uri=';
        echo wprp_get('uri');
        echo '" width="300" height="80" frameborder="0" allowtransparency="true"></iframe>';

        die();
    }

    /**
    // admin create/edit output filters
    */
    function wprp_playlist_extra_form_headers($input, $post) {
        $input .= '<th>' . __('Add Spotify', 'wp-radio-playlist') . '</th>';
        return $input;
    }
    function wprp_playlist_extra_form_columns($input, $post) {
        if ($post) {
            $
        }
        $input .= '<td></td>';
        return $input;
    }
}

class Wordpress_Radio_Playlist_Extras_Spotifyplay_Settings extends Wordpress_Radio_Playlist_Settings
{
    function __construct()
    {
        add_action('admin_init', array($this, 'admin_init'));
    }
    function admin_init()
    {
        add_settings_field(
            'wp-radio-playlist-extras-spotifyplay',
            __('Enable Spotify Play', 'wp-radio-playlist'),
            array($this, 'spotifyplay'),
            'wp-radio-playlist-settings',
            'wp-radio-playlist-settings-extras'
        );

        register_setting('wp-radio-playlist-settings', 'wp-radio-playlist-extras-spotifyplay');
    }
    public function spotifyplay()
    {
        $this->bool('wp-radio-playlist-extras-spotifyplay', 0);
    }
}
