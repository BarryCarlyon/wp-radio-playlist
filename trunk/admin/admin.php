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
        include_once 'settings.php';
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

//        add_action('admin_init', array($this, 'admin_init'));
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
        add_submenu_page('wprp_playlist', __('Create Playlist', 'wp-radio-playlist'), __('Create Playlist', 'wp-radio-playlist'), $this->target_role, 'wprp_playlist_create', array($this, 'wprp_playlist_create'));
    }

    /**
    * admin init
    */
    public function admin_init()
    {
    }

    public function wprp_playlist_index()
    {
//        global $post_type, $post_type_object;
$post_type = 'wprp_playlist';
$post_type_object = get_post_type_object( $post_type );

//$screen = WP_Screen::get( $post_type );
//$screen->post_type = $post_type;

/*
if ( ! $post_type_object )
    wp_die( __( 'Invalid post type' ) );

if ( ! current_user_can( $post_type_object->cap->edit_posts ) )
    wp_die( __( 'Cheatin&#8217; uh?' ) );
*/
//$screen = get_current_screen();
//print_r($screen);
get_current_screen()->post_type = $post_type;

include __DIR__ . '/../includes/steve.php';

//$wp_list_table = _get_list_table('WP_Posts_List_Table');//, array('screen' => $screen));
$wp_list_table = new WP_Playlists_List_Table();
$wp_list_table->prepare_items();

?>
        <form id="movies-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $wp_list_table->display() ?>
</form>
<?php
return;
        $playlists = array(
            'numberposts' => -1,
            'offset' => 0,

            'post_type' => 'wprp_playlist',
            'post_status' => array(
                'publish',
                'future'
            ),
        );
/*
        $total = get_posts($playlists);


$wp_list_table->data = $total;
*/
global $wpdb, $wp_query;
//$wp_query->query = 'SELECT * FROM ' .$wpdb->posts . ' WHERE post_type = \'wprp_playlist\'';
$wp_query = new WP_Query( $playlists );

$pagenum = $wp_list_table->get_pagenum();
$doaction = $wp_list_table->current_action();

$wp_list_table->prepare_items();

$wp_list_table->views();
?>
<form id="posts-filter" action="" method="get">

<?php $wp_list_table->search_box( $post_type_object->labels->search_items, 'post' ); ?>

<input type="hidden" name="post_status" class="post_status_page" value="<?php echo !empty($_REQUEST['post_status']) ? esc_attr($_REQUEST['post_status']) : 'all'; ?>" />
<input type="hidden" name="post_type" class="post_type_page" value="<?php echo $post_type; ?>" />

<?php $wp_list_table->display(); ?>

</form>
<?php

        /*
        $playlists = array(
            'numberposts' => -1,
            'offset' => 0,

            'post_type' => 'wprp_playlist',
            'post_status' => array(
                'publish',
                'future'
            ),
        );
        $total = get_posts($playlists);

        $playlists['numberposts'] = 10;
        $playlists = get_posts($playlists);

        echo '<div class="wrap">';

        echo '<a href="?page=wprp_playlist_create" class="button-secondary alignright" style="margin-top: 10px;">' . __('Create', 'wp-radio-playlist') . '</a>';

        screen_icon();
        echo '<h2>' . __('WP Radio Playlist', 'wp-radio-playlist') . '</h2>';

        echo '<table class="widefat">';

        echo '<thead>';
        echo '
<tr>
    <th>' . __('ID', 'wp-radio-playlist') . '</th>
    <th>' . __('Tracks', 'wp-radio-playlist') . '</th>
    <th>' . __('Date', 'wp-radio-playlist') . '</th>
</tr>
';
        echo '</thead>';
        echo '<tfoot>';
        echo '
<tr>
    <th>' . __('ID', 'wp-radio-playlist') . '</th>
    <th>' . __('Tracks', 'wp-radio-playlist') . '</th>
    <th>' . __('Date', 'wp-radio-playlist') . '</th>
</tr>
';
        echo '</tfoot>';

        echo '<tbody>';

        if (!count($playlists)) {
            echo '<tr><td colspan="10">' . __('No Playlists to Show', 'wp-radio-playlist') . '</td></tr>';
        } else {
            foreach ($playlists as $playlist)
            {
                echo '<tr>';
                echo '<td>' . $playlist->ID . '</td>';

                echo '<td>' . get_post_meta($playlist->ID, 'wprp_playlist_tracks', true);

                $date = split(' ', $playlist->post_date);
                echo '<td>' . $date[0] . '</td>';

                echo '</tr>';
            }
        }

        echo '</tbody>';

        echo '</table>';
        echo '</div>';
        */
    }

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
//                        'post_content' => $json,
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
        echo '<div class="wrap">';
        screen_icon();
        echo '<h2>' . __('WP Radio Playlist | Create', 'wp-radio-playlist') . '</h2>';

        echo '<form method="post" action="">';
        wp_nonce_field('wprp_playlist_create');

        $artists = wprp_post('artist', array());
        $tracks = wprp_post('track', array());

        $post_title = wprp_post('post_title', '');
        $start_date = wprp_post('start_date', wprp_next_monday($this->php_date_format));

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
