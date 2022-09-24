<?php

/**
 * The file that run json API requests
 *
 *
 * @link       http://www.pragmasoftwares.com/
 * @since      1.0.0
 *
 * @package    RHD
 * @subpackage RHD/includes
 */


class WordPressRemoteJSON extends WordPressRemote {
  
  /**
   * Prepare the headers for JSON requests and then run the main method run()
   **/
	public function run() {
		$this->arguments['headers']['Content-type'] = 'application/json';
		parent::run();
	}
	
}