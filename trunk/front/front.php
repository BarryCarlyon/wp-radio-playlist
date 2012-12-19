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

    public function wprp_playlist()
    {
        // get latest playlist
        global $wpdb;
        $query = 'SELECT * FROM ' . $wpdb->posts . '
            WHERE post_type = \'wprp_playlist\'
            AND post_status = \'publish\'
        ';

        if (wprp_request('playlist')) {
            $query .= 'AND post_date = \'' . wprp_request('playlist') . ' 00:00:00\'';
        }

        $query .= '
            ORDER BY post_date DESC LIMIT 1';
        $row = $wpdb->get_row($query);
        if ($row) {
            $playlist = json_decode(get_post_meta($row->ID, 'wprp_json', true));

            $html = '';
            // build selector
            $html .= $this->selector($row->post_date);

            // display latest
            $html .= '<h3>' . $row->post_title . '</h3>';
            $html .= '<table>';

            foreach ($playlist as $pos => $entry)
            {
                $html .= '<tr>';
                $html .= '<th>' . $pos . '</th>';
                $html .= '<td>' . apply_filters('wprp_artist', wprp_item_title($entry[0])) . ' </td>';
                $html .= '<td>' . apply_filters('wprp_track', wprp_item_title($entry[1])) . ' </td>';
                $html .= '</tr>';
            }

            $html .= '</table>';
        } else {
            return __('No Playlist to return', 'wp-radio-playlist');
        }
        return $html;
    }

    function selector($selected) {
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
}
