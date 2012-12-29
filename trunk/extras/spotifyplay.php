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
                    add_action('admin_head', array($this, 'admin_head_default'));
                    add_action('wprp_track_meta_box_cb', array($this, 'wprp_track_meta_box_cb'));
                }
                add_action('admin_head', array($this, 'admin_head_normal'));
                add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

                add_action('wp_ajax_wprp_spotify_lookup_tracks', array($this, 'wp_ajax_wprp_spotify_lookup_tracks'));
                add_action('wp_ajax_wprp_spotify_get_track', array($this, 'wp_ajax_wprp_spotify_get_track'));
                add_action('wp_ajax_wprp_spotify_get_first_track', array($this, 'wp_ajax_wprp_spotify_get_first_track'));

                add_filter('wprp_playlist_admin_extra_form_headers', array($this, 'wprp_playlist_admin_extra_form_headers'), 10, 2);
                add_filter('wprp_playlist_admin_extra_form_columns', array($this, 'wprp_playlist_admin_extra_form_columns'), 10, 3);

                return;
            }

            add_filter('wprp_playlist_front_shortcode_wprp_playlist_columns_after_change', array($this, 'wprp_playlist_front_shortcode_wprp_playlist_columns_after_change'), 10, 3);

            add_shortcode('wprp_playlist_spotify_player', array($this, 'wprp_playlist_spotify_player'));
        }
    }

    /**
    Default Controller
    */
    function admin_head_default()
    {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('#wprp_lookup_tracks').click(function(event) {
        event.preventDefault();
        jQuery('#wprp_found_tracks').html('<?php _e('Loading', 'wp-radio-playlist') ?>');
        jQuery.get(ajaxurl + '?action=wprp_spotify_lookup_tracks&postid=' + jQuery('#post_ID').val(), function(data) {
            jQuery('#wprp_found_tracks').html(data);
        });
    });
    jQuery('#spotify_play_button').on('click', '.wprp_use_track', function(event) {
        event.preventDefault();
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
        include WPRP_INCLUDES_DIR . 'spotify.class.php';

        $postid = wprp_get('postid');
        if ($postid) {
            $track_name = wprp_item_title($postid);
            $artist_id = get_post_meta($postid, 'wprp_artist', true);
            $artist_name = wprp_item_title($artist_id);
        } else {
            $track_name = rawurldecode(wprp_get('track'));
            $artist_name = rawurldecode(wprp_get('artist'));
        }

        $spotify = new Spotify();
        $tracks = $spotify->search_track($track_name);

        if (!$tracks) {
            echo __('An Error Occured', 'wp-radio-playlist');
            die();
        }
        if (!count($tracks->tracks)) {
            echo __('No Tracks Returned', 'wp-radio-playlist');
            die();
        }

        $not_artist_matches = '';

        echo '<table>';
        foreach ($tracks->tracks as $track) {
            $this_track = '<tr><td>';
            $this_track .= '<iframe src="https://embed.spotify.com/?uri=';
            $this_track .= $track->href;
            $this_track .= '" width="300" height="80" frameborder="0" allowtransparency="true"></iframe>';
            $this_track .= '</td><td>';
            $this_track .= '<input type="button" class="wprp_use_track" class="button-secondary" value="' . __('Use Track', 'wp-radio-playlist') . '" data-uri="';
            $this_track .= $track->href;
            $this_track .= '" />';
            $this_track .= '</td></tr>';

            // yes case sensitive.... the external resource is more likely to be correct
            if (strcmp($track->artists[0]->name, $artist_name)) {
                $not_artist_matches .= $this_track;
            } else {
                echo $this_track;
            }
        }
        if (strlen($not_artist_matches)) {
            echo '<tr><td colspan="2">' . __('Track Name not Artist Match (consider correcting the Artist?)', 'wp-radio-playlist') . '</td></tr>';
            echo $not_artist_matches;
        }
        echo '</table>';
        die();
    }

    function wp_ajax_wprp_spotify_get_track() {
        $postid = wprp_get('postid');
        if ($postid) {
            update_post_meta($postid, 'wprp_spotify_uri', wprp_get('uri'));
        }
        $track = rawurldecode(wprp_get('track'));
        $artist = rawurldecode(wprp_get('artist'));
        if ($track && $artist) {
            $artist_id = wprp_get_artist_id($artist);
            if ($artist_id) {
                $track_id = wprp_get_track_id_by_artist_id($track, $artist_id);
                if ($track_id) {
                    update_post_meta($track_id, 'wprp_spotify_uri', wprp_get('uri'));
                }
            }
        }

        echo $this->_wprp_spotify_player(wprp_get('uri'));

        die();
    }

    function wp_ajax_wprp_spotify_get_first_track() {
        include WPRP_INCLUDES_DIR . 'spotify.class.php';

        $postid = wprp_get('postid');
        if ($postid) {
            $track_name = wprp_item_title($postid);
            $artist_id = get_post_meta($postid, 'wprp_artist', true);
            $artist_name = wprp_item_title($artist_id);
        } else {
            $track_name = rawurldecode(wprp_get('track'));
            $artist_name = rawurldecode(wprp_get('artist'));

            $artist_id = wprp_get_artist_id($artist_name);
            if ($artist_id) {
                $postid = wprp_get_track_id_by_artist_id($track_name, $artist_id);
            }
        }

        $spotify = new Spotify();
        $tracks = $spotify->search_track($track_name);

        if (!$tracks) {
            echo __('An Error Occured', 'wp-radio-playlist');
            die();
        }
        if (!count($tracks->tracks)) {
            echo __('No Tracks Returned', 'wp-radio-playlist');
            die();
        }

        foreach ($tracks->tracks as $track) {
            if (!strcmp($track->artists[0]->name, $artist_name)) {
                if ($postid) {
                    update_post_meta($postid, 'wprp_spotify_uri', $track->href);
                } else {
                    echo 'POSTID FAIL<br />';
                }
                echo $this->_wprp_spotify_player($track->href);
                die();
            }
        }
        echo __('No Valid Tracks Found (Specific Track Name/Artist Name Match)', 'wp-radio-playlist');
        die();
    }

    private function _wprp_spotify_player($uri, $height = 80, $width = 300) {
        $x = '<iframe src="https://embed.spotify.com/?uri=';
        $x .= $uri;
        $x .= '" width="' . $width . '" height="' . $height . '" frameborder="0" allowtransparency="true"></iframe>';
        return $x;
    }

    /**
    // admin create/edit output filters
    */
    function admin_head_normal() {
        ?>
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('<div id="wprp_dialog"></div>').appendTo('body');
    var x;
    var track;
    var artist;
    jQuery('.wprp_load_tracks').click(function(event) {
        event.preventDefault();
        x = jQuery(this).attr('data-x');
        artist = jQuery(this).parents('tr').find('.wprp_artist').val();
        track = jQuery(this).parents('tr').find('.wprp_track').val();
        jQuery('#wprp_dialog').html('').load(ajaxurl + '?action=wprp_spotify_lookup_tracks&track=' + encodeURIComponent(track) + '&artist=' + encodeURIComponent(artist)).dialog({
            'modal': true,
            'width': 440,
            'height': 300
        });
    });
    jQuery('#wprp_dialog').on('click', '.wprp_use_track', function(event) {
        event.preventDefault();
        jQuery('#wprp_dialog').dialog('close');
        jQuery.get(ajaxurl + '?action=wprp_spotify_get_track&uri=' + jQuery(this).attr('data-uri') + '&track=' + encodeURIComponent(track) + '&artist=' + encodeURIComponent(artist), function(data) {
            jQuery('.wprp_selected_track_player_' + x).html(data);
        });
    });

    jQuery('.wprp_load_track').click(function(event) {
        event.preventDefault();
        jQuery('.wprp_load_track').attr('disabled', 'disabled');
        x = jQuery(this).attr('data-x');
        artist = jQuery(this).parents('tr').find('.wprp_artist').val();
        track = jQuery(this).parents('tr').find('.wprp_track').val();
        jQuery.get(ajaxurl + '?action=wprp_spotify_get_first_track&track=' + encodeURIComponent(track) + '&artist=' + encodeURIComponent(artist), function(data) {
            jQuery('.wprp_selected_track_player_' + x).html(data);
            jQuery('.wprp_load_track').removeAttr('disabled');
        });
    });
});
</script>
<?php
    }
    function admin_enqueue_scripts() {
        if (wprp_request('page') == 'wprp_playlist' || wprp_request('page') == 'wprp_playlist_create') {
            wp_enqueue_script('jquery-ui-dialog');
        }
    }

    function wprp_playlist_admin_extra_form_headers($input, $post) {
        $input .= '<th style="width: 300px;">' . __('Add Spotify', 'wp-radio-playlist') . '</th>';
        $input .= '<th>' . __('Search Spotify', 'wp-radio-playlist') . '</th>';
        return $input;
    }
    function wprp_playlist_admin_extra_form_columns($input, $x, $artist_track) {
        $uri = '';
        $input .= '<td class="wprp_selected_track_player_' . $x . '">';
        if (is_array($artist_track)) {
            $artist_id = wprp_get_artist_id($artist_track[0]);
            $track_id = wprp_get_track_id_by_artist_id($artist_track[1], $artist_id);
            $uri = get_post_meta($track_id, 'wprp_spotify_uri', TRUE);
        }
        if ($uri) {
            $input .= $this->_wprp_spotify_player($uri);
        }
        $input .= '</td><td>';
        $input .= '<input type="button" class="button-secondary wprp_load_tracks" data-x="' . $x . '" value="' . __('Load Tracks', 'wp-radio-playlist') . '" />';
        $input .= '<input type="button" class="button-secondary wprp_load_track" data-x="' . $x . '" value="' . __('Load First Track', 'wp-radio-playlist') . '" />';
        $input .= '</td>';
        return $input;
    }

    /**
    Front
    */
    public function wprp_playlist_front_shortcode_wprp_playlist_columns_after_change($html, $pos, $artist_track) {
        $uri = get_post_meta($artist_track[1], 'wprp_spotify_uri', TRUE);
        if ($uri) {
            $html = '<td>' . $this->_wprp_spotify_player($uri) . '</td>';
        }
        return $html;
    }

    public function wprp_playlist_spotify_player($args = array())
    {
        $args['playlist'] = isset($wp_query->query_vars['playlist']) && $wp_query->query_vars['playlist'] ? $wp_query->query_vars['playlist'] : (isset($args['playlist']) ? $args['playlist'] : wprp_request('playlist', false));//, wprp_get_latest_playlist_date()));
        $args['height'] = isset($args['height']) ? $args['height'] : 680;
//        $args['width'] = isset($args['width']) ? $args['width'] : 300;
        $args['width'] = $args['height'] - 80;

        $row = wprp_get_playlist_by_date($args['playlist']);
        if ($row) {
            $args['title'] = isset($args['title']) ? $args['title'] : $row->post_title;

            $uris = array();
            $playlist = json_decode(get_post_meta($row->ID, 'wprp_json', true));
            foreach ($playlist as $pos => $entry) {
                $uri = get_post_meta($entry[1], 'wprp_spotify_uri', true);
                if ($uri) {
                    $uris[] = str_replace('spotify:track:', '', $uri);
                }
            }
            //https://embed.spotify.com/?uri=spotify:trackset:cookies:0qTw7kEFpNdECY7jRCg54v,5YaqbhEmoxSpIbdBTPG6KQ
            $uri = 'spotify:trackset:' . urlencode($args['title']) . ':' . implode(',', $uris);
            return $this->_wprp_spotify_player($uri, $args['height'], $args['width']);
        } else {
            return __('No Playlist to return', 'wp-radio-playlist');
        }
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
