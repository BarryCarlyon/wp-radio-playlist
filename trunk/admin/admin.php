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
        include_once 'ajax.php';
        $this->setup();
    }

    /**
    * setup those admin hooks
    */
    private function setup()
    {
        $formats = split('-', get_option('wp-radio-playlist-dateformat', 'mm/dd/yy-m/d/Y'));
        $this->php_date_format = $formats[1];
        $this->jquery_date_format = $formats[0];

        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));

        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('admin_head', array($this, 'admin_head'));

        add_filter('set-screen-option', array($this, 'wprp_playlist_index_screen_options_set'), 10, 3);

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

        $index_page = add_menu_page(__('Playlists', 'wp-radio-playlist'), __('Playlists', 'wp-radio-playlist'), $this->target_role, 'wprp_playlist', array($this, 'wprp_playlist_index'));
        add_submenu_page('wprp_playlist', __('Create Playlist', 'wp-radio-playlist'), __('Create Playlist', 'wp-radio-playlist'), $this->target_role, 'wprp_playlist_create', array($this, 'wprp_playlist_create'));

        $this->menu_page_target = $index_page;
        add_action('load-' . $index_page, array($this, 'wprp_playlist_index_screen_options'));
    }

    /**
    * admin init
    */
    public function admin_init()
    {
        if (get_option('permalink_structure') && get_option('wp-radio-playlist-permalinks-slug')) {
            add_rewrite_rule('^(' . get_option('wp-radio-playlist-permalinks-slug') . ')/(\d{4})-(\d{2})-(\d{2})/?$', 'index.php?pagename=$matches[1]&playlist=$matches[2]-$matches[3]-$matches[4]', 'top');
        }

        $page = wprp_request('page', '');
        if ($page == 'wprp_playlist') {
            $action = wprp_request('action', '');
            if ($action == 'delete') {
                $playlist = wprp_request('playlist', '');
                if ($playlist) {
                    $r = wp_delete_post($playlist);
                    if ($r) {
                        $msg = 'deleteok';
                    } else {
                        $msg = 'deletefail';
                    }
                } else {
                    $msg = 'deletenoid';
                }
                wp_safe_redirect('?page=wprp_playlist&msg=' . $msg);
                die();
            }
        }
    }

    public function wprp_playlist_index()
    {
        // process action
        $msg = wprp_request('msg', '');
        if ($msg) {
            if ($msg == 'deleteok') {
                echo '<div id="message" class="updated"><p>' . __('Playlist Deleted', 'wp-radio-playlist') . '</p></div>';
            } else if ($msg == 'deletefail') {
                echo '<div id="message" class="error"><p>' . __('Failed to Delete Playlist', 'wp-radio-playlist') . '</p></div>';
            } else if ($msg == 'deletenoid') {
                echo '<div id="message" class="error"><p>' . __('No Playlist ID Specified', 'wp-radio-playlist') . '</p></div>';
            }
        }
        $_SERVER['REQUEST_URI'] = remove_query_arg( array( 'msg' ), $_SERVER['REQUEST_URI'] );

        $action = wprp_request('action', '');
        if ($action == 'edit') {
            $playlistid = wprp_request('playlist', '');
            if ($playlistid) {
                $post = get_post($playlistid);
                if ($post) {
                    $this->wprp_playlist_form($post);
                    return;
                }
                echo '<div id="message" class="error"><p>' . __('Could not load the playlist ID specified', 'wp-radio-playlist') . '</p></div>';
            } else {
                echo '<div id="message" class="error"><p>' . __('No playlist ID specified', 'wp-radio-playlist') . '</p></div>';
            }
        }

        // done
        include WPRP_DIR . 'admin/wp-playlists-list-table.php';

        $wp_list_table = new WP_Playlists_List_Table();
        $wp_list_table->prepare_items();

        echo '<div class="wrap">';
        screen_icon();
        echo '<h2>' . __('WP Radio Playlist', 'wp-radio-playlist') . ' <a href="?page=wprp_playlist_create" class="add-new-h2">' . __('Add New', 'wp-radio-playlist') . '</a></h2>';
?>
    <form id="playlists-filter" method="get">
        <!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <!-- Now we can render the completed list table -->
        <?php $wp_list_table->display() ?>
    </form>
</div>
<?php
    }

    /**
    * Screen Options
    */
    public function wprp_playlist_index_screen_options() {
        $screen = get_current_screen();
        if(!is_object($screen) || $screen->id != $this->menu_page_target)
            return;
        $args = array(
            'label' => __('Playlists per page', 'wp-radio-playlist'),
            'default' => 10,
            'option' => 'wprp_playlist_index_screen_options_per_page'
        );
        add_screen_option('per_page', $args);
    }
    public function wprp_playlist_index_screen_options_set($status, $option, $value) {
        if ('wprp_playlist_index_screen_options_per_page' == $option) {
            return $value;
        }
    }

    /**
    * Pages
    */
    public function wprp_playlist_create() {
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
                                'post_type' => 'wprp_artist',
                            );
                            $artist_id = wp_insert_post($post);
                        }

                        $track_id = wprp_get_track_id_by_artist_id($track, $artist_id);
                        if (!$track_id)
                        {
                            $post = array(
                                'post_status' => 'publish',
                                'post_title' => $track,
                                'post_type' => 'wprp_track',
                            );
                            $track_id = wp_insert_post($post);
                            update_post_meta($track_id, 'wprp_artist', $artist_id);
                        }

                        $playlist[$x] = array($artist_id, $track_id);
                    }
                }

                // create the playlist entry
                $json = json_encode($playlist);
                //Y-m-d
                $start = explode('/', wprp_post('start_date'));
                if ($this->php_date_format == 'm/d/Y') {
                    $post_date = $start[2] . '-' . $start[0] . '-' . $start[1];
                } else {
                    $post_date = $start[2] . '-' . $start[1] . '-' . $start[0];
                }
                $post = array(
                    'post_date' => $post_date . ' 00:00:00',//ymd his
                    'post_status' => 'publish',
                    'post_title' => wprp_post('post_title', __('Playlist', 'wp-radio-playlist') . ' ' . wprp_post('start_date')),
                    'post_type' => 'wprp_playlist',
                );
                $playlist_id = wp_insert_post($post);

                update_post_meta($playlist_id, 'wprp_json', $json);
                foreach ($playlist as $index => $item)
                {
                    update_post_meta($playlist_id, 'wprp_playlist_' . $index, implode(',', $item));
                }
                update_post_meta($playlist_id, 'wprp_playlist_tracks', count($playlist));
            }
        }
        $this->wprp_playlist_form();
    }

    protected function wprp_playlist_form($post = false)
    {
        echo '<div class="wrap">';
        screen_icon();


        if ($post) {
            echo '<h2>' . __('WP Radio Playlist | Edit', 'wp-radio-playlist') . '</h2>';
            echo '<form method="post" action="">';
            wp_nonce_field('wprp_playlist_edit');

            $post_title = $post->post_title;

            // from Y-m-d to stated
            $start = strstr($post->post_date, ' ', true);
            $start = explode('-', $start);
            if ($this->php_date_format == 'm/d/Y') {
                $start_date = $start[1] . '/' . $start[2] . '/' . $start[0];
            } else {
                $start_date = $start[2] . '/' . $start[1] . '/' . $start[0];
            }

            $artists = $tracks = array();
            $json = get_post_meta($post->ID, 'wprp_json', TRUE);
            $json = json_decode($json);
            foreach ($json as $entry) {
                $artists[] = wprp_item_title($entry[0]);
                $tracks[] = wprp_item_title($entry[1]);
            }
        } else {
            echo '<h2>' . __('WP Radio Playlist | Create', 'wp-radio-playlist') . '</h2>';
            echo '<form method="post" action="">';
            wp_nonce_field('wprp_playlist_create');

            $artists = wprp_post('artist', array());
            $tracks = wprp_post('track', array());

            $post_title = wprp_post('post_title', '');
            $start_date = wprp_post('start_date', wprp_next_monday($this->php_date_format));
        }

        echo '<table class="widefat">';
        echo '<tbody>';

        // data
        echo '<tr>';
        echo '<th>';
        echo __('Playlist Title', 'wp-radio-playlist');
        echo '</th>';
        echo '<td><input type="text" name="post_title" id="post_title" value="' . $post_title . '" /></td>';
        echo '</tr>';


        echo '<tr>';
        echo '<th>';
        echo __('Start Date', 'wp-radio-playlist');
        echo '</th>';
        echo '<td><input type="text" name="start_date" id="start_date" class="wprp_date" value="' . $start_date . '" /></td>';
        echo '</tr>';

        echo '</tbody>';
        echo '</table>';

        echo '<hr />';

        $extra_headers = apply_filters('wprp_playlist_extra_form_headers', '', $post);

        echo '<table class="widefat">';
        echo '
<thead>
    <tr><th>#</th>
    <th>' . __('Artist', 'wp-radio-playlist') . '</th>
    <th>' . __('Track', 'wp-radio-playlist') . '</th>
    ' . $extra_headers . '
</thead>
<tfoot>
    <tr><th>#</th>
    <th>' . __('Artist', 'wp-radio-playlist') . '</th>
    <th>' . __('Track', 'wp-radio-playlist') . '</th>
    ' . $extra_headers . '
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
            echo apply_filters('wprp_playlist_extra_form_columns', '', $x, array($artist, $track));
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
