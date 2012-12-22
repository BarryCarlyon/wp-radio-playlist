<?php

/**
* Admin Ajax Controller
*
* @category WordPres_Plugins
* @package  Wordpress_Radio_Playlist
* @author   Barry Carlyon <barry@barrycarlyon.co.uk>
* @license  GPL V2
* @link     noneyet
*/
class Wordpress_Radio_Playlist_Admin_Ajax
{
    function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('admin_head', array($this, 'admin_head'));

        add_action('wp_ajax_wprp_artist', array($this, 'wp_ajax_wprp_artist'));
        add_action('wp_ajax_wprp_track', array($this, 'wp_ajax_wprp_track'));

        if (get_option('wp-radio-playlist-extras-spotifyplay', 0)) {
            add_action('wp_ajax_wprp_spotify_lookup_tracks', array($this, 'wp_ajax_wprp_spotify_lookup_tracks'));
            add_action('wp_ajax_wprp_spotify_get_track', array($this, 'wp_ajax_wprp_spotify_get_track'));
        }
    }
    function admin_scripts()
    {
        wp_enqueue_script('suggest');
    }
    function admin_head()
    {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('.wprp_artist').suggest(ajaxurl + '?action=wprp_artist');
    jQuery('.wprp_track').suggest(ajaxurl + '?action=wprp_track');

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
// &title=' + jQuery('#title').val()
// 
    }

    function wp_ajax_wprp_artist()
    {
        Wordpress_Radio_Playlist_Admin_Ajax::post_search('wprp_artist');
    }

    function wp_ajax_wprp_track()
    {
        Wordpress_Radio_Playlist_Admin_Ajax::post_search('wprp_track');
    }

    private function post_search($post_type)
    {
        global $wpdb;

        $search = like_escape($_REQUEST['q']);

        $query = 'SELECT ID,post_title FROM ' . $wpdb->posts . '
            WHERE post_title LIKE \'%' . $search . '%\'
            AND post_type = \'' . $post_type . '\'
            AND post_status = \'publish\'
            ORDER BY post_title ASC';
        foreach ($wpdb->get_results($query) as $row) {
            $post_title = $row->post_title;
            $id = $row->ID;

            echo $post_title . "\n";
        }
        die();
    }

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
                echo '" width="300" height="100" frameborder="0" allowtransparency="true"></iframe>';
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
        echo '" width="300" height="100" frameborder="0" allowtransparency="true"></iframe>';

        die();
    }
}
