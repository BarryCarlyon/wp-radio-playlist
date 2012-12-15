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

function wprp_getpost_by_title($search, $post_type)
{
    global $wpdb;

    $query = 'SELECT ID FROM ' . $wpdb->posts . '
        WHERE post_title LIKE \'' . $search . '\'
        AND post_type = \'' . $post_type . '\'
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
