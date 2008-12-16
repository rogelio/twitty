<?php
/**
 * Another Twitter API library for PHP
 * 
 * and accessible at http://
 *
 * @author Rogelio Calamaya <rogelio@demilane.com>
 * @version 1.0-beta
 */

class Twitty {
	
	private $twitty_option = array(
		'USERPWD' => '',
		'TWITTY_' => '');
		
	private $twitty_raise = array(
		'INVALID_OPTION' => ' is invalid for option.',
		'INVALID_FORMAT' => 'Invalid format.',
		'UNSUPPORTED_FORMAT' => ' is not supported yet.',
		'MISSING_ID' => 'ID is required.',
		'CHARS_LIMIT' => 'Message length must not be greater than 140 characters.');
		
	private $twitty_Http_code = array (
		200 => 'OK: everything went awesome.',
		304 => 'Not Modified: there was no new data to return.',
		400 => 'Bad Request: your request is invalid',
		401 => 'Not Authorized: either you need to provide authentication credentials',
		403 => 'Forbidden: we understand your request, but are refusing to fulfill it.',
		404 => 'Not Found: either you\'re requesting an invalid URI or the resource in question doesn\'t exist (ex: no such user).',
		500 => 'Internal Server Error: we did something wrong.  Please post to the group about it and the Twitter team will investigate.',
		502 => 'Bad Gateway: returned if Twitter is down or being upgraded.',
		503 => 'Service Unavailable: the Twitter servers are up, but are overloaded with requests.  Try again later.');
		
	private $twitty_format = 'json';
	
	private $twitty_base_request = array(
		'URL_STATUS' => 'http://twitter.com/statuses/',
		'URL_USER' => 'http://twitter.com/users/',
		'URL_DIRECT_MESSAGES' => 'http://twitter.com/direct_messages/',
		'URL_FRIENDSHIP' => 'http://twitter.com/friendships/'
	);
	
	
	/**
	 * Returns the 20 most recent updates from non-protected users.
	 *
	 * @return array results
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
	 * @return array results
	 */		
	public function status_friends_timeline($count = NULL, $page = NULL, $since = NULL, $since_id = NULL) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'friends_timeline.' . $this->twitty_format;
		$param = array();
		
		if(!empty($count)) $param[] = 'count=' . $count;	
		if(!empty($page)) $param[] = 'page=' . $page;	
		if(!empty($since)) $param[] = 'since=' . urlencode($since);
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
	 * @return array results
	 */		
	public function status_user_timeline($id= NULL, $count = NULL, $page = NULL, $since = NULL, $since_id = NULL) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'user_timeline.' . $this->twitty_format;
		$param = array();
		
		if(!empty($id)) $param[] = 'id=' . $id;
		if(!empty($count)) $param[] = 'count=' . $count;	
		if(!empty($page)) $param[] = 'page=' . $page;	
		if(!empty($since)) $param[] = 'since=' . urlencode($since);
		if(!empty($since_id)) $param[] = 'since_id=' . $since_id;
		
		$num_args = count(trim($param));
		
		if($num_args >= 1) $API_request .= '?' . implode('&', $param);

		return $this->handle($API_request, 1);
	}
	
	/**
	 * Returns a single status.
	 *
	 * @param integer $id Required. The numerical ID of the status you're trying to retrieve
	 * @return array results
	 */		
	public function status_show($id) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'show/';
		
		if(!empty($id)) $API_request .= $id . '.' .  $this->twitty_format;
		else $API_request = $this->twitty_raise['MISSING_ID'];

		return $this->handle($API_request);
	}
	
	/**
	 * Sets an update to an authenticated user only.
	 *
	 * @param string $status Required The text of your status update, must < 140 characters.
	 * @param integer $in_reply_to_status_id Optional. The ID of an existing update that you want reply to.	 
	 * @param integer $since_id Optional. Returns only updates more recent than the specified ID.
	 * @return array results
	 */		
	public function status_update($status, $in_reply_to_status_id = NULL) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'update.' . $this->twitty_format;
		$param = array();
		
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
	 * @return array results
	 */		
	public function status_replies($page = NULL, $since = NULL, $since_id = NULL) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'replies.' . $this->twitty_format;
		$param = array();
		
		if(!empty($page)) $param[] = 'page=' . $page;	
		if(!empty($since)) $param[] = 'since=' . urlencode($since);
		if(!empty($since_id)) $param[] = 'since_id=' . $since_id;
		
		$num_args = count(trim($param));
		
		if($num_args >= 1) $API_request .= '?' . implode('&', $param);

		return $this->handle($API_request, 1);
	}
		
	/**
	 * Returns a single status.
	 *
	 * @param integer $id Required. The numerical ID of the status you're trying to retrieve
	 * @return array results
	 */		
	public function status_destroy($id) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'destroy/';
		
		if(!empty($id)) $API_request .= $id . '.' .  $this->twitty_format;
		else $API_request = $this->twitty_raise['MISSING_ID'];

		return $this->handle($API_request, 1, 1);
	}

	/**
	 * Returns up to 100 of the authenticating user's friends.
	 *
	 * @param mixed $id Optional. ID or screen name of the user
	 * @param integer $page Optional. Specifies the page of updates, 100/page.
	 * @return array results
	 */		
	public function user_friends($id = NULL, $page = NULL) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'friends.' . $this->twitty_format;
		$param = array();
		
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
	 * @return array results
	 */		
	public function user_followers($id = NULL, $page = NULL) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'followers.' . $this->twitty_format;
		$param = array();
		
		if(!empty($id)) $param[] = 'id=' . $id;	
		if(!empty($page)) $param[] = 'page=' . $page;	
				
		$num_args = count(trim($param));
		
		if($num_args >= 1) $API_request .= '?' . implode('&', $param);

		return $this->handle($API_request, 1);
	}
		
	/**
	 * Returns extended information of a given user ID or screen name.
	 *
	 * @param mixed $id Optional. ID or screen name of the user
	 * @param integer $page Optional. Specifies the page of updates, 100/page.
	 * @return array results
	 */		
	public function user_show($userIdentifier = NULL) {
		$API_request = $this->twitty_base_request['URL_USER'] . 'show';
		
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
	 * @return array results
	 */		
	public function direct_messages($page = NULL, $since = NULL, $since_id = NULL) {
		$API_request = substr($this->twitty_base_request['URL_DIRECT_MESSAGES'], 0, -1) . '.' . $this->twitty_format;
		$param = array();
		
		if(!empty($page)) $param[] = 'page=' . $page;	
		if(!empty($since)) $param[] = 'since=' . urlencode($since);
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
	 * @return array results
	 */		
	public function direct_messages_sent($page = NULL, $since = NULL, $since_id = NULL) {
		$API_request = $this->twitty_base_request['URL_DIRECT_MESSAGES'] . 'sent.' . $this->twitty_format;
		$param = array();
		
		if(!empty($page)) $param[] = 'page=' . $page;	
		if(!empty($since)) $param[] = 'since=' . urlencode($since);
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
	 * @return array results
	 */		
	public function direct_messages_new($user, $text) {
		$API_request = $this->twitty_base_request['URL_DIRECT_MESSAGES'] . 'new.' . $this->twitty_format;
		
		if(strlen(trim($text)) <= 140)	$API_request .= '?user=' . $user . '&text=' . urlencode($text);
		else $API_request = $this->twitty_raise['CHARS_LIMIT'];

		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Deletes the direct message specified in the required ID parameter.
	 *
	 * @param integer $id Required. The ID of the direct message.
	 * @return array results
	 */		
	public function direct_messages_destroy($id) {
		$API_request = $this->twitty_base_request['URL_DIRECT_MESSAGES'] . 'destroy/';
		
		if(!empty($id)) $API_request .= $id . '.' . $this->twitty_format;

		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Befriends the user specified in the ID parameter as the authenticating user.
	 *
	 * @param mixed $id Required. The ID or the screen name of the user.
	 * @param boolean $follow Optional. Enable notifications for the target user in addition to becoming friends.
	 * @return array results
	 */		
	public function friendship_create($id, $follow = FALSE) {
		$API_request = $this->twitty_base_request['URL_FRIENDSHIP'] . 'create/' . $id . '.' . $this->twitty_format;
		
		if($follow) $API_request .= '?follow=' . $follow;

		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Destroys the friendship with the user specified.
	 *
	 * @param mixed $id Required. The ID or screen name of the user.
	 * @return array results
	 */		
	public function friendship_destroy($id) {
		$API_request = $this->twitty_base_request['URL_FRIENDSHIP'] . 'destroy/';
		
		if(!empty($id)) $API_request .= $id . '.' . $this->twitty_format;

		return $this->handle($API_request, 1, 1);
	}
	
	/**
	 * Tests if a friendship exists between two users.
	 *
	 * @param mixed $user_a Required. The ID or screen name of the user A.
	 * @param mixed $user_b Required. The ID or screen name of the user B.	 
	 * @return array results
	 */		
	public function friendship_exists($user_a, $user_b) {
		$API_request = $this->twitty_base_request['URL_FRIENDSHIP'] . 'exists.' . $this->twitty_format;
		
		if(!empty($user_a) || !empty($user_b)) $API_request .= '?user_a=' . $user_a . '&user_b=' . $user_b;

		return $this->handle($API_request, 1);
	}				
	
	/**
	 * Set options for the library/API.
	 *
	 * @param string $option The option name.
	 * @param string $value The value of the option name.
	 * @return string option.
	 */	
	public function set_option($option = NULL, $value = NULL) {
		$option = strtoupper(trim($option));
		
		if(array_key_exists($option, $this->twitty_option)) $this->twitty_option[$option] = $value;
		else echo $option . $this->twitty_raise['INVALID_OPTION'];
		
		return $this->twitty_option[$option];
	}

	private function handle($request, $require_auth = 0, $post = 0) {
		$curl = curl_init();
		
		if($require_auth) curl_setopt($curl, CURLOPT_USERPWD, $this->twitty_option['USERPWD']);
		curl_setopt($curl, CURLOPT_POST, $post);
		curl_setopt($curl, CURLOPT_URL, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$data = curl_exec($curl);
		
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		curl_close($curl);
	
		return $this->result($data);
	}
	
	private function result($data) {
		
		if($this->twitty_format == 'json') $result = json_decode($data, true);
		elseif($this->twitty_format == 'xml') $result = 'XML' . $this->twitty_raise['UNSUPPORTED_FORMAT'];
		elseif($this->twitty_format == 'rss') $result = 'RSS' . $this->twitty_raise['UNSUPPORTED_FORMAT'];
		elseif($this->twitty_format == 'atom') $result = 'ATOM' . $this->twitty_raise['UNSUPPORTED_FORMAT'];
		else $result = $this->twitty_raise['INVALID_FORMAT'];
				
		return $result;
	}
}


?>