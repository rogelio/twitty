<?php
/**
 * Extended Twitter API library for PHP
 * 
 * @author Rogelio Calamaya <rogelio@demilane.com>
 * @copyright Copyright (c) 2008
 * @link http://demilane.com
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @version 1.0
 */
/**
 * Copyright (c) 2008, Rogelio Calamaya of Demilane.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 *
 * -Redistributions of source code must retain the above copyright notice, 
 *  this list of conditions and the following disclaimer.
 * -Redistributions in binary form must reproduce the above copyright notice,
 *  this list of conditions and the following disclaimer in the documentation and/or 
 *  other materials provided with the distribution.
 * -Neither the name of Demilane nor the names of its contributors may be used to endorse or
 *  promote products derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL
 * THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT 
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, 
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. 
 */
 
class Twitty {
	
	public $twitty_option = array(
		'USERPWD' => '',
		'SHOW_HEADERS' => 0);
		
	private $twitty_raise = array(
		'MSG_LIMIT' => 'Message must not be greater than 140 characters.',
		'NAME_LIMIT' => 'Name must not be greater than 40 characters.',
		'EMAIL_LIMIT' => 'Email must not be greater than 40 characters.',
		'URL_LIMIT' => 'URL must not be greater than 100 characters.',
		'LOC_LIMIT' => 'Location must not be greater than 30 characters.',
		'DESC_LIMIT' => 'Description must not be greater than 100 characters.',
		'INVALID_EMAIL' => 'Must be a valid email address.',
		'INVALID_FORMAT' => 'Invalid format.',
		'INVALID_OPTION' => ' is invalid for option.',
		'INVALID_PARAM' => 'Invalid or missing parameter.',
		'DATE_FORMAT' => 'Date format is [day][month][numerical day][hh:mm:ss][year] ex: Thu Dec 18 08:28:37 2008',
		'NOT_INT' => 'Invalid parameter, parameter must be Integer',
		'MISSING_ID' => 'ID is required.',
		'UNSUPPORTED_FORMAT' => ' is not supported yet.',
		'ON_DEV' => 'Method still in development.');
		
	private $twitty_Http_code = array (
		200 => 'OK: everything went awesome.',
		304 => 'Not Modified: there was no new data to return.',
		400 => 'Bad Request: your request is invalid.',
		401 => 'Not Authorized: either you need to provide authentication credentials.',
		403 => 'Forbidden: we understand your request, but are refusing to fulfill it.',
		404 => 'Not Found: either you\'re requesting an invalid URI or the resource in question doesn\'t exist.',
		500 => 'Internal Server Error: we did something wrong.  Please post to the group about it and the Twitter team will investigate.',
		502 => 'Bad Gateway: returned if Twitter is down or being upgraded.',
		503 => 'Service Unavailable: the Twitter servers are up, but are overloaded with requests.  Try again later.');
		
	private $twitty_format = 'json';
	
	private $twitty_base_request = array(
		'URL_STATUS' => 'http://twitter.com/statuses/',
		'URL_USER' => 'http://twitter.com/users/',
		'URL_DIRECT_MESSAGES' => 'http://twitter.com/direct_messages/',
		'URL_FRIENDSHIP' => 'http://twitter.com/friendships/',
		'URL_ACCOUNT' => 'http://twitter.com/account/',
		'URL_FAVORITES' => 'http://twitter.com/favorites/',
		'URL_NOTIFICATION' => 'http://twitter.com/notifications/',
		'URL_BLOCK' => 'http://twitter.com/blocks/',
		'URL_HELP' => 'http://twitter.com/help/');
	
	public function __construct($username = NULL, $password = NULL) {
		$this->twitty_option['USERPWD'] = stripslashes(trim($username)) . ':' . stripslashes(trim($password));
	}
	
	/**
	 * Returns the 20 most recent updates from non-protected users.
	 *
	 * @return object results
	 */	
	public function status_public_timeline() {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'public_timeline.' . $this->twitty_format;
		return $this->handle($API_request);
	}

	/**
	 * Returns the 20 most recent updates from the authenticated user and the user's friends.
	 *
	 * @param integer $count Optional. Specifies the number of updates to retrieve. $count < 200.
	 * @param integer $page Optional. Specifies the page of updates, 20/page.
	 * @param date $since Optional. Narrows the results up to 24 hours old.
	 * @param integer $since_id Optional. Returns only updates more recent than the specified ID.
	 * @return object results
	 */		
	public function status_friends_timeline($count = null, $page = null, $since = null, $since_id = null) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'friends_timeline.' . $this->twitty_format;
		$param = array();
		if(!empty($since) && ($time = strtotime($since)) === false) return $this->error('DATE_FORMAT');
		if(is_string($count) || is_string($page) || is_string($since_id)) return $this->error('NOT_INT');
		if(!empty($count)) $param[] = 'count=' . $count;
		if(!empty($page)) $param[] = 'page=' . $page;
		if(!empty($since)) $param[] = 'since=' . urlencode(date('D M d h:i:s O Y', $time));
		if(!empty($since_id)) $param[] = 'since_id=' . $since_id;

		$num_args = count(trim($param));
		
		if($num_args >= 1) $API_request .= '?' . implode('&', $param);

		return $this->handle($API_request, 1);
	}
	
	/**
	 * Returns the 20 most recent updates from the authenticated/other user only.
	 *
	 * @param mixed $id Optional. Specifies the ID or screen name of the user for whom to return the timeline.
	 * @param integer $count Optional. Specifies the number of updates to retrieve. $count < 200.	 
	 * @param integer $page Optional. Specifies the page of updates, 20/page.
	 * @param date $since Optional. Narrows the results up to 24 hours old.
	 * @param integer $since_id Optional. Returns only updates more recent than the specified ID.
	 * @return object results
	 */		
	public function status_user_timeline($id= null, $count = null, $page = null, $since = null, $since_id = null) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'user_timeline.' . $this->twitty_format;
		$param = array();
		if(!empty($since) && ($time = strtotime($since)) === false) return $this->error('DATE_FORMAT');
		if(is_string($count) || is_string($page) || is_string($since_id)) return $this->error('NOT_INT');
		if(!empty($id)) $param[] = 'id=' . $id;
		if(!empty($count)) $param[] = 'count=' . $count;
		if(!empty($page)) $param[] = 'page=' . $page;
		if(!empty($since)) $param[] = 'since=' . urlencode(date('D M d h:i:s O Y', $time));
		if(!empty($since_id)) $param[] = 'since_id=' . $since_id;		

		$num_args = count(trim($param));
		
		if($num_args >= 1) $API_request .= '?' . implode('&', $param);

		return $this->handle($API_request, 1);
	}
	
	/**
	 * Returns a single status.
	 *
	 * @param integer $id Required. The numerical ID of the status you're trying to retrieve
	 * @return object results
	 */		
	public function status_show($id) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'show/';
		
		if(!empty($id) && is_int($id)) $API_request .= $id . '.' .  $this->twitty_format;
		else $this->error('NOT_INT');

		return $this->handle($API_request);
	}
	
	/**
	 * Sets an update to an authenticated user only.
	 *
	 * @param string $status Required The text of your status update, must < 140 characters.
	 * @param integer $in_reply_to_status_id Optional. The ID of an existing update that you want reply to.	 
	 * @return object results
	 */		
	public function status_update($status, $in_reply_to_status_id = null) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'update.' . $this->twitty_format;
		$param = array();

		if(strlen(trim($status)) > 140) return $this->error('MSG_LIMIT');		
		if(!empty($status)) $param[] = 'status=' . urlencode($status);
		if(!empty($in_reply_to_status_id)) $param[] = 'in_reply_to_status_id=' . $in_reply_to_status_id;

		$num_args = count(trim($param));
		
		if($num_args >= 1) $API_request .= '?' . implode('&', $param);

		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Returns the 20 most recent replies.
	 *
	 * @param integer $page Optional. Specifies the page of updates, 20/page.
	 * @param date $since Optional. Narrows the results up to 24 hours old.
	 * @param integer $since_id Optional. Returns only updates more recent than the specified ID.
	 * @return object results
	 */		
	public function status_replies($page = null, $since = null, $since_id = null) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'replies.' . $this->twitty_format;
		$param = array();
		if(!empty($since) && ($time = strtotime($since)) === false) return $this->error('DATE_FORMAT');
		if(is_string($page) || is_string($since_id)) return $this->error('NOT_INT');
		if(!empty($page)) $param[] = 'page=' . $page;
		if(!empty($since)) $param[] = 'since=' . urlencode(date('D M d h:i:s O Y', $time));
		if(!empty($since_id)) $param[] = 'since_id=' . $since_id;			
		
		$num_args = count(trim($param));
		
		if($num_args >= 1) $API_request .= '?' . implode('&', $param);

		return $this->handle($API_request, 1);
	}
		
	/**
	 * Returns a single status.
	 *
	 * @param integer $id Required. The numerical ID of the status you're trying to retrieve
	 * @return object results
	 */		
	public function status_destroy($id) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'destroy/';
		
		if(!empty($id) && is_int($id)) $API_request .= $id . '.' .  $this->twitty_format;
		else $this->error('NOT_INT');
		
		return $this->handle($API_request, 1, 1);
	}

	/**
	 * Returns up to 100 of the authenticating user's friends.
	 *
	 * @param mixed $id Optional. ID or screen name of the user
	 * @param integer $page Optional. Specifies the page of updates, 100/page.
	 * @return object results
	 */		
	public function user_friends($id = null, $page = null) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'friends.' . $this->twitty_format;
		$param = array();
		
		if(is_string($page)) return $this->error('NOT_INT');		
		if(!empty($id)) $param[] = 'id=' . $id;	
		if(!empty($page)) $param[] = 'page=' . $page;	
				
		$num_args = count(trim($param));
		
		if($num_args >= 1) $API_request .= '?' . implode('&', $param);

		return $this->handle($API_request, 1);
	}
	
	/**
	 * Returns up to 100 of the authenticating user's followers.
	 *
	 * @param mixed $id Optional. ID or screen name of the user
	 * @param integer $page Optional. Specifies the page of updates, 100/page.
	 * @return object results
	 */		
	public function user_followers($id = null, $page = null) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'followers.' . $this->twitty_format;
		$param = array();
		
		if(is_string($page)) return $this->error('NOT_INT');		
		if(!empty($id)) $param[] = 'id=' . $id;	
		if(!empty($page)) $param[] = 'page=' . $page;	
				
		$num_args = count(trim($param));
		
		if($num_args >= 1) $API_request .= '?' . implode('&', $param);

		return $this->handle($API_request, 1);
	}
		
	/**
	 * Returns extended information of a given user ID or screen name.
	 *
	 * @param mixed $id Required. ID or screen name or email of the user
	 * @return object results
	 */		
	public function user_show($userIdentifier = null) {
		$API_request = $this->twitty_base_request['URL_USER'] . 'show';
		
		if(empty($userIdentifier)) return $this->error('INVALID_PARAM');
		if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $userIdentifier)) 
			$API_request .= '.' . $this->twitty_format . '?' . 'email=' . $userIdentifier;
		else $API_request .= '/' . $userIdentifier . '.' . $this->twitty_format;	

		return $this->handle($API_request, 1);
	}
	
	/**
	 * Returns the 20 most recent direct messages by the authenticating user.
	 *
	 * @param integer $page Optional. Specifies the page of updates, 20/page.
	 * @param date $since Optional. Narrows the results up to 24 hours old.
	 * @param integer $since_id Optional. Returns only updates more recent than the specified ID.
	 * @return object results
	 */		
	public function direct_messages($page = null, $since = null, $since_id = null) {
		$API_request = substr($this->twitty_base_request['URL_DIRECT_MESSAGES'], 0, -1) . '.' . $this->twitty_format;
		$param = array();
			
		if(!empty($since) && ($time = strtotime($since)) === false) return $this->error('DATE_FORMAT');
		if(is_string($page) || is_string($since_id)) return $this->error('NOT_INT');
		if(!empty($page)) $param[] = 'page=' . $page;
		if(!empty($since)) $param[] = 'since=' . urlencode(date('D M d h:i:s O Y', $time));
		if(!empty($since_id)) $param[] = 'since_id=' . $since_id;			
		
		$num_args = count(trim($param));
		
		if($num_args >= 1) $API_request .= '?' . implode('&', $param);

		return $this->handle($API_request, 1);
	}
	
	/**
	 * Returns the 20 most recent direct messages sent by the authenticating user.
	 *
	 * @param integer $page Optional. Specifies the page of updates, 20/page.
	 * @param date $since Optional. Narrows the results up to 24 hours old.
	 * @param integer $since_id Optional. Returns only updates more recent than the specified ID.
	 * @return object results
	 */		
	public function direct_message_sent($page = null, $since = null, $since_id = null) {
		$API_request = $this->twitty_base_request['URL_DIRECT_MESSAGES'] . 'sent.' . $this->twitty_format;
		$param = array();
		
		if(!empty($since) && ($time = strtotime($since)) === false) return $this->error('DATE_FORMAT');
		if(is_string($page) || is_string($since_id)) return $this->error('NOT_INT');
		if(!empty($page)) $param[] = 'page=' . $page;
		if(!empty($since)) $param[] = 'since=' . urlencode(date('D M d h:i:s O Y', $time));
		if(!empty($since_id)) $param[] = 'since_id=' . $since_id;
		
		$num_args = count(trim($param));
		
		if($num_args >= 1) $API_request .= '?' . implode('&', $param);

		return $this->handle($API_request, 1);
	}
	
	/**
	 * Sends a new direct message to the specified user from the authenticating user
	 *
	 * @param mixed $user Required. The recipient.
	 * @param string $text Required. The message.
	 * @return object results
	 */		
	public function direct_message_new($user, $text) {
		$API_request = $this->twitty_base_request['URL_DIRECT_MESSAGES'] . 'new.' . $this->twitty_format;
		
		if(empty($user) || empty($text)) return $this->error('INVALID_PARAM');
		if(strlen(trim($text)) <= 140)	$API_request .= '?user=' . $user . '&text=' . urlencode($text);
		else return $this->error('MSG_LIMIT');

		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Deletes the direct message specified in the required ID parameter.
	 *
	 * @param integer $id Required. The ID of the direct message.
	 * @return object results
	 */		
	public function direct_message_destroy($id) {
		$API_request = $this->twitty_base_request['URL_DIRECT_MESSAGES'] . 'destroy/';
		
		if(!empty($id)) $API_request .= $id . '.' . $this->twitty_format;
		else return $this->error('INVALID_PARAM');
		if(is_string($id)) return $this->error('NOT_INT');

		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Befriends the user specified in the ID parameter as the authenticating user.
	 *
	 * @param mixed $id Required. The ID or the screen name of the user.
	 * @param boolean $follow Optional. Enable notifications for the target user in addition to becoming friends.
	 * @return object results
	 */		
	public function friendship_create($id, $follow = FALSE) {
		$API_request = $this->twitty_base_request['URL_FRIENDSHIP'] . 'create/';
		
		if(empty($id)) return $this->error('INVALID_PARAM');		
		if(!empty($id)) $API_request .= $id . '.' . $this->twitty_format;
		
		if($follow) $follow = TRUE; $API_request .= '?follow=' . $follow;

		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Destroys the friendship with the user specified.
	 *
	 * @param mixed $id Required. The ID or screen name of the user.
	 * @return object results
	 */		
	public function friendship_destroy($id) {
		$API_request = $this->twitty_base_request['URL_FRIENDSHIP'] . 'destroy/';
		
		if(empty($id)) return $this->error('INVALID_PARAM');
		if(!empty($id)) $API_request .= $id . '.' . $this->twitty_format;

		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Tests if a friendship exists between two users.
	 *
	 * @param mixed $user_a Required. The ID or screen name of the user A.
	 * @param mixed $user_b Required. The ID or screen name of the user B.	 
	 * @return boolean results
	 */		
	public function friendship_exists($user_a, $user_b) {
		$API_request = $this->twitty_base_request['URL_FRIENDSHIP'] . 'exists.' . $this->twitty_format;
		
		if(!empty($user_a) || !empty($user_b)) $API_request .= '?user_a=' . $user_a . '&user_b=' . $user_b;

		return $this->handle($API_request, 1);
	}
	
	/**
	 * Test if supplied user credentials are valid
	 * 
	 * @return boolean results
	 */		
	public function account_verify_credentials() {
		$API_request = $this->twitty_base_request['URL_ACCOUNT'] . 'verify_credentials.' . $this->twitty_format;
		return $this->handle($API_request, 1);
	}	
	
	/**
	 * Ends the session of the current user.
	 * 
	 * @return object results
	 */		
	public function account_end_session() {
		$API_request = $this->twitty_base_request['URL_ACCOUNT'] . 'end_session.' . $this->twitty_format;
		return $this->handle($API_request, 1, 1);
	}						
	
	/**
	 * Sets which device Twitter delivers updates to for the authenticating user.
	 * 
	 * @param string $device Required. Must be one of: sms, im, none.
	 * @return object results
	 */		
	public function account_delivery_device($device = 'none') {
		$API_request = $this->twitty_base_request['URL_ACCOUNT'] . 'update_delivery_device.' . $this->twitty_format;
		
		if(!empty($device)) $API_request .= '?device=' . $device;
		else $API_request .= '?device=none';
		
		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Sets color scheme of the the user page profile.
	 * 
	 * @param string $element Required. Where could be the color set to.
	 * @param string $color Required. Hex value of the color.	 
	 * @return object results
	 */		
	public function account_profile_color($element, $color) {
		$API_request = $this->twitty_base_request['URL_ACCOUNT'] . 'update_profile_colors.' . $this->twitty_format;
		$element = strtoupper(trim($element));
		$param = array(
			'BACKGROUND' => 'profile_background_color',
			'TEXT' => 'profile_text_color',
			'LINK' => 'profile_link_color',
			'SIDEBARFILL' => 'profile_sidebar_fill_color',
			'SIDEBARBORDER' => 'profile_sidebar_border_color');
		
		if(array_key_exists($element, $param)) $API_request .= '?' . $param[$element] . '=' . str_replace('#', '' ,$color);
		else return $this->error('INVALID_PARAM');
		
		return $this->handle($API_request, 1, 1);
	}	
	
	/**
	 * Sets profile image of the the user.
	 * 			NOT IN USE!!
	 * @param string $image Required. Where could be the color set to.
	 * @return object results
	 */		
	public function account_profile_image($image = null) {
		$API_request = $this->twitty_base_request['URL_ACCOUNT'] . 'update_profile_image.' . $this->twitty_format;

		if(!empty($image)) $API_request .= '?image=';
		//else return $this->error('INVALID_PARAM');
		
		return $this->error('ON_DEV');
	}
	
	/**
	 * Sets the profile background image of the the user page.
	 * 			NOT IN USE!
	 * @param string $image Required. Where could be the color set to.
	 * @return object results
	 */		
	public function account_profile_bg_image($image = null) {
		$API_request = $this->twitty_base_request['URL_ACCOUNT'] . 'update_profile_background_image.' . $this->twitty_format;

		if(!empty($image)) $API_request .= '?image=' . $image['file']['name'];
		//else return $this->error('INVALID_PARAM');
		
		return $this->error('ON_DEV');
	}

	/**
	 * Returns the remaining number of API requests available to the requesting user.
	 * 
	 * @return array results
	 */		
	public function account_rate_limit() {
		$API_request = $this->twitty_base_request['URL_ACCOUNT'] . 'rate_limit_status.' . $this->twitty_format;
		return $this->handle($API_request, 1);
	}
	
	/**
	 * Sets profile values[name,email,url,location,description] of the the user.
	 * 
	 * @param string $element Required. Where could be the color set to.
	 * @param string $color Required. Hex value of the color.	 
	 * @return object results
	 */		
	public function account_profile($attribute, $value) {
		$API_request = $this->twitty_base_request['URL_ACCOUNT'] . 'update_profile.' . $this->twitty_format;
		$attribute = strtoupper(trim($attribute));
		$param = array(
			'NAME' => 'name',
			'EMAIL' => 'email@email.com',
			'URL' => 'url',
			'LOCATION' => 'location',
			'DESCRIPTION' => 'description');
		if(array_key_exists($attribute, $param)):
			$param[$attribute] = $value;
			if((strlen(trim($param['NAME'])) > 40)) return $this->error('NAME_LIMIT');
			elseif((strlen(trim($param['EMAIL'])) > 40)) return $this->error('EMAIL_LIMIT');
			elseif(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", trim($param['EMAIL']))) return $this->error('INVALID_EMAIL');
			elseif((strlen(trim($param['URL'])) > 100)) return $this->error('URL_LIMIT');
			elseif((strlen(trim($param['LOCATION'])) > 30)) return $this->error('LOC_LIMIT');
			elseif((strlen(trim($param['DESCRIPTION'])) > 160)) return $this->error('DESC_LIMIT');
			else $API_request .= '?' . strtolower($attribute) . '=' . urlencode($value);
		else:
			return $this->error('INVALID_PARAM');
		endif;
		
		return $this->handle($API_request, 1, 1);
	}		
	
	/**
	 * Returns the 20 most recent favorite messages by the authenticating user.
	 *
	 * @param integer $id Optional. The ID or screen name of the user.
	 * @param integer $page Optional. Specifies the page of updates, 20/page.
	 * @return object results
	 */		
	public function favorites($id = null, $page = null) {
		$API_request = substr($this->twitty_base_request['URL_FAVORITES'], 0, -1);

		if(!empty($id)) $API_request .= '/' . $id . '.' . $this->twitty_format;// . ( empty($page) ? '' : '?page=' . $page);
		if(!empty($page)) $API_request .= ( empty($id) ? '.' . $this->twitty_format . '?page=' . $page : '?page=' . $page);	
		
		$API_request .= ( empty($id) ? '.' . $this->twitty_format : '');
		
		return $this->handle($API_request, 1);
	}
	
	/**
	 * Favorites the status specified in the ID parameter as the authenticating user.
	 *
	 * @param integer $id Required. The ID of the message.
	 * @return object results
	 */		
	public function favorites_create($id) {
		$API_request = $this->twitty_base_request['URL_FAVORITES'] . 'create/';
		if(!empty($id)) $API_request .= $id . '.' .  $this->twitty_format;
		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Un-favorites the status specified in the ID parameter as the authenticating user.
	 *
	 * @param integer $id Required. The ID of the message.
	 * @return object results
	 */		
	public function favorites_destroy($id) {
		$API_request = $this->twitty_base_request['URL_FAVORITES'] . 'destroy/';
		if(!empty($id)) $API_request .= $id . '.' .  $this->twitty_format;
		return $this->handle($API_request, 1, 1);
	}

	/**
	 * Enables notifications for updates from the specified user to the authenticating user
	 *
	 * @param mixed $id Required. The ID or screenname of the user.
	 * @return object results
	 */		
	public function notification_follow($id) {
		$API_request = $this->twitty_base_request['URL_NOTIFICATION'] . 'follow/';
		if(!empty($id)) $API_request .= $id . '.' .  $this->twitty_format;
		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Disables notifications for updates from the specified user to the authenticating user
	 *
	 * @param mixed $id Required. The ID or screenname of the user.
	 * @return array results
	 */		
	public function notification_leave($id) {
		$API_request = $this->twitty_base_request['URL_NOTIFICATION'] . 'leave/';
		if(!empty($id)) $API_request .= $id . '.' .  $this->twitty_format;
		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Blocks a user to the authenticating user.
	 *
	 * @param mixed $id Required. The ID or screenname of the user.
	 * @return object results
	 */		
	public function block_create($id) {
		$API_request = $this->twitty_base_request['URL_BLOCK'] . 'create/';
		if(!empty($id)) $API_request .= $id . '.' .  $this->twitty_format;
		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Unblocks a user to the authenticating user.
	 *
	 * @param mixed $id Required. The ID or screenname of the user.
	 * @return object results
	 */		
	public function block_destroy($id) {
		$API_request = $this->twitty_base_request['URL_BLOCK'] . 'destroy/';
		if(!empty($id)) $API_request .= $id . '.' .  $this->twitty_format;
		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Test a request to the API.
	 *
	 * @return object results
	 */		
	public function help_test() {
		$API_request = $this->twitty_base_request['URL_HELP'] . 'test.' . $this->twitty_format;
		return $this->handle($API_request);
	}
	
	/**
	 * If maintenance mode, true message will show up.
	 *
	 * @return object results
	 */		
	public function help_downtime_schedule() {
		$API_request = $this->twitty_base_request['URL_HELP'] . 'downtime_schedule.' . $this->twitty_format;
		return $this->handle($API_request);
	}				
		
	/**
	 * Set options for the library/API.
	 *
	 * @param string $option The option name.
	 * @param string $value The value of the option name.
	 * @return string option.
	 */	
	public function set_option($option = null, $value = null) {
		$option = strtoupper(trim($option));
		
		if(array_key_exists($option, $this->twitty_option)) $this->twitty_option[$option] = $value;
		else echo $option . $this->error('INVALID_OPTION');
		
		return $this->twitty_option[$option];
	}
	
	/**
	 * Communication to API
	 *
	 * @param string $request The URL.
	 * @param boolean $require_auth TRUE|FALSE if requires authentication.
	 * @param boolean $post TRUE|FALSE if requires POST.
	 * @param 
	 * @return mixed resources.
	 */	
	private function handle($request, $require_auth = 0, $post = 0, $image = '') {
		$curl = curl_init();
		$basename ='';
		if($require_auth) curl_setopt($curl, CURLOPT_USERPWD, $this->twitty_option['USERPWD']);
		if(!empty($image)):
			//$file = '@' . $image;
			$basename = basename($image);
			$fo = fopen($image,'r');
			curl_setopt($curl, CURLOPT_UPLOAD, 1);
			curl_setopt($curl, CURLOPT_TIMEOUT, 300);
			curl_setopt($curl, CURLE_OPERATION_TIMEOUTED, 300);
			curl_setopt($curl, CURLOPT_INFILE, $fo);
			curl_setopt($curl, CURLOPT_INFILESIZE, filesize($image));
			fclose($fo);
			//curl_setopt($curl, CURLOPT_POSTFIELDS, $file);
		endif;		
		curl_setopt($curl, CURLOPT_POST, $post);
		curl_setopt($curl, CURLOPT_URL, $request.$basename);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, $this->twitty_option['SHOW_HEADERS']);
		
		$data = curl_exec($curl);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		
		if($httpCode == 200) return $this->result($data);
		else return $this->header($httpCode);
	}
	
	private function image($img) {
		$fn = basename($src);
		$fp = fopen($src,"r");
		curl_setopt($ch, CURLOPT_UPLOAD, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 300);
		curl_setopt($ch, CURLE_OPERATION_TIMEOUTED, 300);
		curl_setopt($ch, CURLOPT_URL, $dest);
		curl_setopt($ch, CURLOPT_INFILE, $fp);
		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($src));
		fclose ($fp);
	}
	/**
	 * The results ...
	 *
	 * @param mixed $data The result.
	 * @return object result.
	 */		
	private function result($data) {
		
		if($this->twitty_format == 'json') $result = json_decode($data, true);
		elseif($this->twitty_format == 'xml') $result = 'XML' . $this->twitty_raise['UNSUPPORTED_FORMAT'];
		elseif($this->twitty_format == 'rss') $result = 'RSS' . $this->twitty_raise['UNSUPPORTED_FORMAT'];
		elseif($this->twitty_format == 'atom') $result = 'ATOM' . $this->twitty_raise['UNSUPPORTED_FORMAT'];
		else $result = $this->twitty_raise['INVALID_FORMAT'];
		return (object) $result;
	}
	
	/**
	 * This will show the Headers of the last request ...
	 *
	 * @param mixed $code The codee of the error.
	 */		
	private function header($code) {
		if(is_int($code)) $error = $this->twitty_Http_code[$code];
		else $error = $this->twitty_raise[$code];
		echo '<br />'.$error;
	}
	
	/**
	 * The Error ...
	 *
	 * @param mixed $code The codee of the error.
	 */		
	private function error($code) {
		if(is_int($code)) $error = $this->twitty_Http_code[$code];
		else $error = $this->twitty_raise[$code];
		echo '<br />'.$error;
	}		
}
?>