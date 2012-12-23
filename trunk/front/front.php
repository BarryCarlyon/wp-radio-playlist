<?php

/**
* Front EndController
*
* @category WordPres_Plugins
* @package  Wordpress_Radio_Playlist
* @author   Barry Carlyon <barry@barrycarlyon.co.uk>
* @license  GPL V2
* @link     noneyet
*/
class Wordpress_Radio_Playlist_Front
{
    /**
    * Yeah it constructs....
    */
    function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
        add_action('wp_head', array($this, 'wp_head'));
        add_shortcode('wprp_playlist', array($this, 'wprp_playlist'));
        add_shortcode('wprp_selector', array($this, 'selector'));

        add_filter('wp_nav_menu_objects', array($this, 'menu'), 10, 2);

//        if (get_option('wp-radio-playlist-extras-spotifyplay', 0)) {
  //          add_shortcode('wprp_spotify_playlist', array($this, 'wprp_spotify_playlist'));
    //    }
    }

    public function wp_enqueue_scripts()
    {
        wp_enqueue_script('jquery');
    }
    public function wp_head()
    {
        echo '
<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery(\'#wprp_playlist_selector\').change(function() {
        jQuery(this).parent(\'form\').submit();
    });
});
</script>
<style type="text/css">
#wprp_playlist_selector {
    float: right;
}
</style>
';
    }

    public function wprp_playlist($args = array())
    {
        $args['playlist'] = isset($args['playlist']) ? $args['playlist'] : wprp_request('playlist', false);
        $args['selector'] = isset($args['selector']) ? $args['selector'] : true;
        $args['change'] = isset($args['change']) ? $args['change'] : true;

        // get latest playlist
        global $wpdb;
        $query = 'SELECT * FROM ' . $wpdb->posts . '
            WHERE post_type = \'wprp_playlist\'
            AND post_status = \'publish\'
        ';

        if ($args['playlist']) {
            $query .= 'AND post_date = \'' . $args['playlist'] . ' 00:00:00\'';
        }

        $query .= '
            ORDER BY post_date DESC LIMIT 1';
        $row = $wpdb->get_row($query);
        if ($row) {
            $playlist = json_decode(get_post_meta($row->ID, 'wprp_json', true));

            $html = '';

            if ($args['selector']) {
                // build selector
                $html .= $this->selector($row->post_date);
            }

            // display latest
            $html .= '<h3>' . $row->post_title . '</h3>';
            $html .= '<table>';

            foreach ($playlist as $pos => $entry)
            {
                $class = '';
                if ($args['change']) {
                    $change = wprp_calculate_change($entry[1], $entry[0], $row->post_date);
                    if ($change > 0) {
                        $class = 'wprp_up';
                    } else if ($change < 0) {
                        $class = 'wprp_down';
                    } else if ($change == 0) {
                        $class = 'wprp_nowhere';
                    } else if ($change == 'new') {
                        $class = 'wprp_new';
                    } else {
                        $class = 'wprp_reentry';
                    }
                }
                $html .= '<tr class="' . $class . '">';
                $html .= '<th>' . $pos . '</th>';
                $html .= '<td>' . apply_filters('wprp_artist', wprp_item_title($entry[0])) . ' </td>';
                $html .= '<td>' . apply_filters('wprp_track', wprp_item_title($entry[1])) . ' </td>';
                if ($args['change']) {
                    $html .= '<td>' . $change . '</td>';
                }
                $html .= '</tr>';
            }

            $html .= '</table>';
        } else {
            return __('No Playlist to return', 'wp-radio-playlist');
        }
        return $html;
    }

    public function selector($selected) {
        if (strpos(' ', $selected)) {
            $selected = strstr($selected, ' ', TRUE);
        }

        $html = '<form action="" method="get">';
        $html .= '<input type="hidden" name="page_id" value="' . wprp_request('page_id') . '" />';
        $html .= '<select name="playlist" id="wprp_playlist_selector">';
        
        global $wpdb;
        $query = 'SELECT ID, post_date
            FROM ' . $wpdb->posts . '
            WHERE post_type = \'wprp_playlist\'
            AND post_status = \'publish\'
            ORDER BY post_date DESC';
        foreach ($wpdb->get_results($query) as $row) {
            $html .= '<option ' . selected($row->post_date, $selected, false) . '>';
            $html .= strstr($row->post_date, ' ', true);
            $html .= '</option>';
        }

        $html .= '</select>';
        $html .= '</form>';

        return $html;
    }

    public function menu($items, $args) {
        global $wpdb;
        $target = get_option('wp-radio-playlist-nav-target', '');
        if ($target) {
            foreach ($items as &$item) {
                if (is_array($item->classes)) {
                if (in_array($target, $item->classes)) {
                    $item->title = __('Playlists', 'wp-radio-playlist');

                    $url = $item->url;
                    if (strpos('?', $url)) {
                        $url .= '?';
                    } else {
                        $url .= '&';
                    }

                    $query = 'SELECT * FROM ' . $wpdb->posts . '
                        WHERE post_type = \'wprp_playlist\'
                        AND post_status = \'publish\'
                        ORDER BY post_date DESC LIMIT ' . get_option('wp-radio-playlist-nav-items', 5);
                    foreach ($wpdb->get_results($query) as $row) {
                        $extra = get_post($row->ID);
                        $extra->post_type = 'nav_menu_item';
                        $extra->title = strstr($row->post_date, ' ', true);
                        $extra->menu_item_parent = $item->ID;
                        $extra->url = $url . 'playlist=' . $extra->title;
                        $items[] = $extra;
                    }
                }
                }
            }
        }
        return $items;
    }

    // Spotify
    public function wprp_spotify_playlist()
    {
//<iframe src="https://embed.spotify.com/?uri=spotify:trackset:PREFEREDTITLE:5Z7ygHQo02SUrFmcgpwsKW,1x6ACsKV4UdWS2FMuPFUiT,4bi73jCM02fMpkI11Lqmfe" frameborder="0" allowtransparency="true"></iframe>
    }
}
