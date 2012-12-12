<?php

/**
* carlyon_cms/includes/musicbrainz.php
* MusicBrainz Class
* will get more done to it over time
* 
* Copyright (c) 2010 Barry Carlyon <barry@barrycarlyon.co.uk>
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
* 
* 
* @catergory Websites
* @package LSRfm.com Content Management System
* @author Barry Carlyon <barry@barrycarlyon.co.uk>
* @copyright 2008-2010 Barry Carlyon
* @license http://www.opensource.org/licenses/mit-license.php MIT License
* @version CVS: $Id:$
* @link http://barrycarlyon.co.uk/
* 
*/

class musicbrainz {
	public $mb_data = array();

	function __construct() {
//		global $log;
//		$log->add_log('MusicBrainz Spawned', 'MB_CLASS');
		// make sure curl is included and ready
		require_once('curl_xml.php');
		// defaults
		$this->mb_data = array(
			'title'		=> '',
			'artist'	=> '',
			'release'	=> '', // aka album
			'duration'	=> '', // millieseconds
			'limit'		=> 1
		);
	}

	// nice set of functions
	function setTitle($title)	{ $this->mb_data['title']	= $title; }
	function setArtist($artist)	{ $this->mb_data['artist']	= $artist; }
	function setRelease($rel)	{ $this->mb_data['release']	= $rel; }
	function setAlbum($album)	{ $this->setRelease($album); }
	function setDuration($dur)	{ $this->mb_data['duration']	= $dur; }
	function setSeconds($seconds)	{ $this->setDuration($seconds * 1000); } // accept in seconds convert to mili
	function setTime($time)		{ $match = explode(':', $time); $this->setDuraction((($match[0] * 60) + $match[1]) * 1000); } // split time to 
	function setLimit($limit)	{ $this->mb_data['limit']	= 1; }

	function getidsfromtrack() {
//		global $log;
//		$log->add_log('getting ids from a track', 'MB_CLASS');
		$curl = new curl();

		$target = 'http://musicbrainz.org/ws/1/track/?type=xml';
		foreach ($this->mb_data as $ref => $dat) {
			$target .= '&' . $ref . '=' . urlencode($dat);
		}

		$curl->target($target);
		$curl->runit();

		if (isset($curl->bodyarray['metadata']['track-list'])) {
			$mb = $curl->bodyarray['metadata']['track-list'];

			$this->artist_id	= $mb['track']['artist_attr']['id'];
			$this->track_id		= $mb['track_attr']['id'];

			return TRUE;
		} else {
			return FALSE;
		}
	}

	function __deconstruct() {

	}
}
