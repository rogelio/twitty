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
		'INVALID_OPTION' => ' is invalid for option!',
		'INVALID_FORMAT' => 'Invalid format!',
		'UNSUPPORTED_FORMAT' => ' is not supported yet!');
		
	private $twitty_format = 'json';
	
	private $twitty_base_request = array('URL_STATUS' => 'http://twitter.com/statuses/');
	
	
	/**
	 * Returns the 20 most recent updates from non-protected users.
	 *
	 * @return array results
	 */	
	public function status_public_timeline() {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'public_timeline.' . $this->twitty_format;
		
		return	$this->handle($API_request);
	}

	/**
	 * Returns the 20 most recent updates from the authenticated user.
	 *
	 * @param date $since Optional. Narrows the results up to 24 hours old.
	 * @param integer $since_id Optional. Returns only updates more recent than the specified ID.
	 * @param integer $count Optional. Specifies the number of updates to retrieve. $count < 200.
 	 * @param integer $page Optional. Specifies the page of updates.
	 * @return array results
	 */		
	public function status_friends_timeline($since = NULL, $since_id = NULL, $count = NULL, $page = NULL) {
		$API_request = $this->twitty_base_request['URL_STATUS'] . 'friends_timeline.' . $this->twitty_format;
		$param = array();
		
		if(!empty($since)) $param[] = 'since=' . urlencode($since);
		if(!empty($since_id)) $param[] = 'since_id=' . $since_id;
		if(!empty($count)) $param[] = 'count=' . $count;	
		if(!empty($page)) $param[] = 'page=' . $page;	
		
		$num_args = count(trim($param));
		
		if($num_args >= 1) $API_request .= '?' . implode('&', $param);

		return	$this->handle($API_request, 1);
	}
	
	public function	set_option($option = NULL, $value = NULL) {
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