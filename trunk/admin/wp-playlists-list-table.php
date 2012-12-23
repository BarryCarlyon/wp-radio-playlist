<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WP_Playlists_List_Table extends WP_List_Table {
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'playlist',
            'plural'    => 'playlists',
            'ajax'      => false,
        ) );
        
    }
    
    function column_default($item, $column_name){
        switch($column_name){
            case 'ID':
            case 'post_title':
            case 'post_date':
                return $item[$column_name];
            case 'tracks':
                return 0;
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
    function column_post_title($item){
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&playlist=%s">' . __('Edit', 'wp-radio-playlist') . '</a>',$_REQUEST['page'],'edit',$item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&playlist=%s">' . __('Delete', 'wp-radio-playlist') . '</a>',$_REQUEST['page'],'delete',$item['ID']),
        );
        
        //Return the title contents
        return sprintf('%1$s%2$s',
            $item['post_title'],
            $this->row_actions($actions)
        );
    }

    function column_post_date($item) {
        return strstr($item['post_date'], ' ', TRUE);
    }

    function column_tracks($item) {
        $json = json_decode($item['json'],true);
        return count($json);
    }
    
    function get_columns(){
        $columns = array(
            'post_title'    => __('Title', 'wp-radio-playlist'),
            'tracks'        => __('Tracks', 'wp-radio-playlist'),
            'post_date'     => __('State Date', 'wp-radio-playlist'),
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'post_title'     => array('post_title', false),
            'post_date'     => array('post_date', false),
        );
        return $sortable_columns;
    }
    
    function get_bulk_actions() {
        $actions = array(
        );
        return $actions;
    }
    
    function process_bulk_action() {
    }
    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        // http://plugins.svn.wordpress.org/custom-list-table-example/tags/1.2/list-table-example.php
        global $wpdb;

        $user = get_current_user_id();
        // get the current admin screen
        $screen = get_current_screen();
        // retrieve the "per_page" option
        $screen_option = $screen->get_option('per_page', 'option');
        // retrieve the value of the option stored for the current user
        $per_page = get_user_meta($user, $screen_option, true);
        if ( empty ( $per_page) || $per_page < 1 ) {
            // get the default value if none is set
            $per_page = $screen->get_option( 'per_page', 'default' );
        }

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        
        $query = 'SELECT ID, post_title, post_date, pm.meta_value AS json FROM ' . $wpdb->posts . ' p
            LEFT JOIN ' . $wpdb->postmeta . ' pm
            ON pm.post_id = p.ID
            WHERE post_type = \'wprp_playlist\'
            AND pm.meta_key = \'wprp_json\'
            ORDER BY ';
        $sort_col = wprp_request('orderby', 'post_date');
        $sort_dir = wprp_request('order', 'DESC');

        $query .= $sort_col . ' ' . $sort_dir;

        $data = $wpdb->get_results($query, ARRAY_A);
        
        $current_page = $this->get_pagenum();
        $total_items = count($data);
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    
}
