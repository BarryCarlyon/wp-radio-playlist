<?php

function wprp_post($item, $default = '')
{
    $data = isset($_POST[$item]) ? $_POST[$item] : $default;
    if (is_array($data)) {
        foreach ($data as $key => &$item) {
            $item = sanitize_text_field($item);
        }
    } else {
        $data = sanitize_text_field($data);
    }
    return $data;
}

function wprp_get($item, $default = '')
{
    $data = isset($_GET[$item]) ? $_GET[$item] : $default;
    if (is_array($data)) {
        foreach ($data as $key => &$item) {
            $item = sanitize_text_field($item);
        }
    } else {
        $data = sanitize_text_field($data);
    }
    return $data;
}

function wprp_request($item, $default = '')
{
    $data = isset($_REQUEST[$item]) ? $_REQUEST[$item] : $default;
    if (is_array($data)) {
        foreach ($data as $key => &$item) {
            $item = sanitize_text_field($item);
        }
    } else {
        $data = sanitize_text_field($data);
    }
    return $data;
}

function wprp_get_artist_id($search)
{
    global $wpdb;

    $query = 'SELECT ID as artist_id FROM ' . $wpdb->posts . '
        WHERE post_title LIKE \'' . $search . '\'
        AND post_type = \'wprp_artist\'
        AND post_status = \'publish\'
        ORDER BY post_title ASC
        LIMIT 1';// there SHOULD only ever be one...
    $results = $wpdb->get_results($query);
    if ($wpdb->num_rows == 1)
    {
        return $wpdb->get_var($query);
    }
    return false;
}

function wprp_get_track_id_by_artist_id($search, $artist_id)
{
    global $wpdb;

    $query = 'SELECT p.ID AS track_id FROM ' . $wpdb->posts . ' p
        LEFT JOIN ' . $wpdb->postmeta . ' pm
        ON pm.post_id = p.id
        WHERE post_title LIKE \'' . $search . '\'
        AND post_type = \'wprp_track\'
        AND post_status = \'publish\'
        AND meta_key = \'wprp_artist\'
        AND meta_value = \'' . $artist_id . '\'
        ORDER BY post_title ASC
        LIMIT 1';// there SHOULD only ever be one...
    $results = $wpdb->get_results($query);
    if ($wpdb->num_rows == 1)
    {
        return $wpdb->get_var($query);
    }
    return false;
}

function wprp_next_monday() {
    return strtotime('Monday');
    $now = time();
    while (date('D', $now) != 'Mon') {
        $now = $now + (60 * 60 * 24);
    }
}
echo date('r', wprp_next_monday());exit;
