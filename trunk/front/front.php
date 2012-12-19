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
    	add_shortcode('wprp_playlist', array($this, 'wprp_playlist'));
    }

    public function wprp_playlist()
    {
    	// get latest playlist
    	global $wpdb;
    	$query = 'SELECT * FROM ' . $wpdb->posts . '
    		WHERE post_type = \'wprp_playlist\'
    		AND post_status = \'publish\'
    		ORDER BY post_date DESC LIMIT 1';
    	$row = $wpdb->get_row($query);
    	if ($row) {
    		$playlist = json_decode(get_post_meta($row->ID, 'wprp_json', true));

    		$html = '<h3>' . $row->post_title . '</h3>';
    		$html .= '<table>';

    		foreach ($playlist as $pos => $entry)
    		{
    			$html .= '<tr>';
    			$html .= '<th>' . $pos . '</th>';
    			$html .= '<td>' . wprp_item_title($entry[0]) . ' </td>';
    			$html .= '<td>' . wprp_item_title($entry[1]) . ' </td>';
    			$html .= '</tr>';
    		}

    		$html .= '</table>';
    	} else {
    		return __('No Playlist to return', 'wp-radio-playlist');
    	}
    	return $html;
    }
}
