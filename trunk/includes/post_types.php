<?php

// custom post type - tracks
register_post_type(
    'wprp_track',
    array(
        'label'                 => __('Tracks', 'wp-radio-playlist'),
        'labels'                => array(
            'name' => __('Tracks', 'wp-radio-playlist'),
            'singular_name' => __('Track', 'wp-radio-playlist'),
            'add_new' => __('Add New', 'wp-radio-playlist'),
            'add_new_item' => __('Add New Track', 'wp-radio-playlist'),
            'edit_item' => __('Edit Track', 'wp-radio-playlist'),
            'new_item' => __('New Track', 'wp-radio-playlist'),
            'all_items' => __('All Tracks', 'wp-radio-playlist'),
            'view_item' => __('View Tracks', 'wp-radio-playlist'),
            'search_items' => __('Search Tracks', 'wp-radio-playlist'),
            'not_found' =>  __('No Tracks found', 'wp-radio-playlist'),
            'not_found_in_trash' => __('No Tracks found in Trash', 'wp-radio-playlist'), 
            'parent_item_colon' => '',
            'menu_name' => __('Tracks', 'wp-radio-playlist')
        ),
        'public'                => (get_option('wp-radio-playlist-raw-posts-tracks', false) ? true : false),
        'supports'              => array(
            'title',
            'editor',
            'custom-fields',
        ),
        'has_archive'           => false,
        'publicly_queryable'    => false,
        'exclude_from_search'   => true,
        'can_export'            => true,
        'register_meta_box_cb'  => 'wprp_track_meta_box_cb',
//                'menu_icon'             => plugin_dir_url(__FILE__) . 'img/zombaio_icon.png',
    )
);

// custom post type - artists
register_post_type(
    'wprp_artist',
    array(
        'label'                 => __('Artists', 'wp-radio-playlist'),
        'labels'                => array(
            'name' => __('Artists', 'wp-radio-playlist'),
            'singular_name' => __('Artist', 'wp-radio-playlist'),
            'add_new' => __('Add New', 'wp-radio-playlist'),
            'add_new_item' => __('Add New Artist', 'wp-radio-playlist'),
            'edit_item' => __('Edit Artist', 'wp-radio-playlist'),
            'new_item' => __('New Artist', 'wp-radio-playlist'),
            'all_items' => __('All Artists', 'wp-radio-playlist'),
            'view_item' => __('View Artists', 'wp-radio-playlist'),
            'search_items' => __('Search Artists', 'wp-radio-playlist'),
            'not_found' =>  __('No Artists found', 'wp-radio-playlist'),
            'not_found_in_trash' => __('No Artists found in Trash', 'wp-radio-playlist'), 
            'parent_item_colon' => '',
            'menu_name' => __('Artists', 'wp-radio-playlist')
        ),
        'public'                => (get_option('wp-radio-playlist-raw-posts-artists', false) ? true : false),
        'supports'              => array(
            'title',
            'editor',
            'custom-fields',
        ),
        'has_archive'           => false,
        'publicly_queryable'    => false,
        'exclude_from_search'   => true,
        'can_export'            => true,
//                'menu_icon'             => plugin_dir_url(__FILE__) . 'img/zombaio_icon.png',
    )
);

// custom post type - playlist
register_post_type(
    'wprp_playlist',
    array(
        'label'                 => __('Playlists', 'wp-radio-playlist'),
        'labels'                => array(
            'name' => __('Playlists', 'wp-radio-playlist'),
            'singular_name' => __('Playlist', 'wp-radio-playlist'),
            'add_new' => __('Add New', 'wp-radio-playlist'),
            'add_new_item' => __('Add New Playlist', 'wp-radio-playlist'),
            'edit_item' => __('Edit Playlist', 'wp-radio-playlist'),
            'new_item' => __('New Playlist', 'wp-radio-playlist'),
            'all_items' => __('All Playlists', 'wp-radio-playlist'),
            'view_item' => __('View Playlists', 'wp-radio-playlist'),
            'search_items' => __('Search Playlists', 'wp-radio-playlist'),
            'not_found' =>  __('No Playlists found', 'wp-radio-playlist'),
            'not_found_in_trash' => __('No Playlists found in Trash', 'wp-radio-playlist'), 
            'parent_item_colon' => '',
            'menu_name' => __('Playlists', 'wp-radio-playlist')
        ),
        'public'                => (get_option('wp-radio-playlist-raw-posts-playlists', false) ? true : false),
        'supports'              => array(
            'title',
            'editor',
            'custom-fields',
        ),
        'has_archive'           => false,
        'publicly_queryable'    => false,
        'exclude_from_search'   => true,
        'can_export'            => true,
//                'menu_icon'             => plugin_dir_url(__FILE__) . 'img/zombaio_icon.png',
    )
);

// meta box callbacks
function wprp_track_meta_box_cb() {
    do_action('wprp_track_meta_box_cb');
}
