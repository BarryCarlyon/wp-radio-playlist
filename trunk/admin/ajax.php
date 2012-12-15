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
    function wp_ajax_wprp_artist()
    {
        Wordpress_Radio_Playlist_Admin_Ajax::post_search('wprp_artist');
    }

    function wp_ajax_wprp_track()
    {
        Wordpress_Radio_Playlist_Admin_Ajax::post_search('wprp_track');
    }

    static protected function post_search($post_type)
    {
        global $wpdb;

        $search = like_escape($_REQUEST['q']);

        $query = 'SELECT ID,post_title FROM ' . $wpdb->posts . '
            WHERE post_title LIKE \'' . $search . '%\'
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
}
