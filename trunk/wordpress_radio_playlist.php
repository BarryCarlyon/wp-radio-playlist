<?php

/**
* Yeah it constructs....
*
* $Id: ym.php 2465 2012-12-06 15:48:46Z bcarlyon $
* $Revision: 2465 $
* $Date: 2012-12-06 15:48:46 +0000 (Thu, 06 Dec 2012) $
*
* PHP Version 5
* 
* @category WordPres_Plugins
* @package  Wordpress_Radio_Playlist
* @author   Barry Carlyon <barry@barrycarlyon.co.uk>
* @license  GPL V2
* @link     noneyet
*/

/**
* Main caller/constants includer/commons
*/
class Wordpress_Radio_Playlist
{
    /**
    * Yeah it constructs....
    */
    function __construct()
    {
        if (is_admin()) {
            include 'admin/admin.php';
        } else {
            include 'front/front.php';
            new Wordpress_Radio_Playlist_Front();
        }
    }
}
new Wordpress_Radio_Playlist();
