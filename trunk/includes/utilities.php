<?php

function wprp_post($item, $default = '')
{
    $data = (isset($_POST[$item]) && $_POST[$item]) ? $_POST[$item] : $default;
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
    $data = (isset($_GET[$item]) && $_GET[$item]) ? $_GET[$item] : $default;
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
    $data = (isset($_REQUEST[$item]) && $_REQUEST[$item]) ? $_REQUEST[$item] : $default;
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
        WHERE post_title = \'' . $search . '\'
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
        WHERE post_title = \'' . $search . '\'
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

function wprp_next_monday($format = false) {
    if ($format) {
        return date($format, strtotime('Monday'));
    }
    return strtotime('Monday');
}

// shunts
function wprp_item_title($post_id) {
    $post = get_post($post_id);
    if ($post)
        return $post->post_title;
}

function wprp_calculate_change($track_id, $artist_id, $this_date, $last_date = false) {
    global $wpdb;
    $value = $artist_id . ',' . $track_id;

    $this_date_full = $this_date . ' 00:00:00';
    // prev position
    if ($last_date) {
        $last_date_full = $last_date . ' 00:00:00';
        // query
        $query = 'SELECT ID FROM ' . $wpdb->posts . '
            WHERE post_type = \'wprp_playlist\'
            AND post_date = \'' . $last_date_full . '\'';
    } else {
        // obtain last date before this date
        $query = 'SELECT ID, post_date FROM ' . $wpdb->posts . '
            WHERE post_type = \'wprp_playlist\'
            AND post_date < \'' . $this_date_full . '\'
            ORDER BY post_date DESC
            LIMIT 1';
        $last_date_full = $wpdb->get_var($query, 1);
        $last_date = strstr($last_date, ' ', TRUE);
    }
    // transient check
    $transient_name = 'wprp_' . $value . '_' . $this_date . '_' . $last_date;
    $transient_name = str_replace(array(' ', ':', ',', '-'), '_', $transient_name);

    if (false === ($transient_result = get_transient($transient_name))) {
        // continue
        $last_date_id = $wpdb->get_var($query);

        $query = 'SELECT meta_key FROM ' . $wpdb->postmeta . '
            WHERE meta_value = \'' . $value . '\'
            AND post_id = ' . $last_date_id;
        $last_date_ref = $wpdb->get_var($query);

        // done

        // current position
        $query = 'SELECT ID FROM ' . $wpdb->posts . '
            WHERE post_type = \'wprp_playlist\'
            AND post_date = \'' . $this_date . '\'';
        $this_date_id = $wpdb->get_var($query);

        $query = 'SELECT meta_key FROM ' . $wpdb->postmeta . '
            WHERE meta_value = \'' . $value . '\'
            AND post_id = ' . $this_date_id;
        $this_date_ref = $wpdb->get_var($query);

        $current_position = split('_', $this_date_ref);
        $current_position = $current_position[2];
        // done

        // math time
        $result = '';

        if ($last_date_ref) {
            $old_position = split('_', $last_date_ref);
            $old_position = $old_position[2];

            $change = $current_position - $old_position;
            if ($change > 0) {
                $result = '+' . $change;
            } else if ($change == 0) {
                $result = '-';
            } else {
                $result = $change;
            }
        } else {
            // reentry?
            $query = 'SELECT * FROM ' . $wpdb->posts . ' p
                LEFT JOIN ' . $wpdb->postmeta . ' pm
                ON pm.post_id = p.ID
                WHERE p.post_date < \'' . $last_date . '\'
                AND pm.meta_key LIKE \'wprp_playlist_%\'
                AND pm.meta_value = \'' . $value . '\'
                LIMIT 1';
            $wpdb->query($query);
            if ($wpdb->num_rows) {
                $result = 'reentry';
            } else {
                $result = 'new';
            }
        }

        // one week
        set_transient($transient_name, $result, 604800);

        return $result;
    } else {
        return $transient_result;
    }
}

function wprp_get_latest_playlist_date() {
    global $wpdb;
    $query = 'SELECT post_date FROM ' . $wpdb->posts . '
        WHERE post_type = \'wprp_playlist\'
        AND post_status = \'publish\'
        ORDER BY post_date DESC LIMIT 1
    ';
    $date = $wpdb->get_var($query);
    return strstr($date, ' ', true);
}
function wprp_get_latest_playlist() {
    return wprp_get_playlist_by_date();
}
function wprp_get_playlist_by_date($date = false) {
    global $wpdb;
    $query = 'SELECT * FROM ' . $wpdb->posts . '
        WHERE post_type = \'wprp_playlist\'
    ';

    if ($date) {
        $query .= ' AND post_date = \'' . $date . ' 00:00:00\' ';
    } else {
        $query .= ' AND post_status = \'publish\' ';
    }

    $query .= '
        ORDER BY post_date DESC LIMIT 1';
    return $wpdb->get_row($query);
}
