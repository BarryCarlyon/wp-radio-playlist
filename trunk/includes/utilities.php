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
