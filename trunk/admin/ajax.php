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
});
</script>
<?php
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
}
