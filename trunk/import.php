<?php

/*
 Plugin Name: WP Radio Playlist | Import LSR
 Plugin URI: http://barrycarlyon.co.uk/
 Description: Beep
 Author: Barry Carlyon
 Author URI: http://www.barrycarlyon.co.uk
 Version: 0.0.1
 */

add_action('plugins_loaded', 'doit');

function doit() {
if (isset($_GET['doit'])) {
	ob_end_flush();
	ob_flush();
	ob_implicit_flush(TRUE);
	$query = 'SELECT * FROM playlist ORDER BY track_id ASC';
	global $wpdb;

	$playlist = array();
	$prev_week = false;

	foreach ($wpdb->get_results($query) as $row) {
		$week = strtotime($row->week);

		if ($prev_week && $week != $prev_week) {
			// store
			$json = json_encode($playlist);

			$post_date = $row->week;
            $post = array(
                'post_date' => $post_date . ' 00:00:00',//ymd his
                'post_status' => 'publish',
                'post_title' => __('Playlist', 'wp-radio-playlist') . ' ' . $post_date,
                'post_type' => 'wprp_playlist',
            );
            $playlist_id = wp_insert_post($post, true);
            if (is_wp_error($playlist_id)) {
            	print_r($playlist_id);
            	exit;
            }
            echo 'Playlist ID ' . $playlist_id . "\n<br />";

            update_post_meta($playlist_id, 'wprp_json', $json);
            foreach ($playlist as $index => $item)
            {
                update_post_meta($playlist_id, 'wprp_playlist_' . $index, implode(',', $item));
            }
            update_post_meta($playlist_id, 'wprp_playlist_tracks', count($playlist));

			echo 'Did Playlist ' . count($playlist);
			echo "\n<br />";

			$playlist = array();
		}
		$prev_week = strtotime($row->week);

		$track = addslashes(trim($row->track_name));
		$artist = addslashes(trim($row->artist));

		if ($track && $artist) {
			echo $row->track_id . ' - ' . $track . ' - ' . $artist . "\n<br />";

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

			$playlist[$row->track_no] = array($artist_id, $track_id);
		}
	}
	echo "\n\n" . 'done';
	exit;
}
}
