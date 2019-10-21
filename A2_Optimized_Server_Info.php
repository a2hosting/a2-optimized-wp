<?php

/**
 * A2_Optimized_Server_Info
 * Class to get information about the server we're currently running on
 *
 **/

// Prevent direct access to this file
if ( ! defined( 'WPINC' ) )  die;

class A2_Optimized_Server_Info {
	/* Is server behind Cloud Flare? */
	public $cf = false;

	/* Is server already gzipping files? */
	public $gzip = false;

	/* Is server using Brotli? */
	public $br = false;

	public $w3tc_config;

	public function __construct($w3tc) {
		$this->w3tc_config = $w3tc;
		$this->server_header_call();
	}

	/**
	 * Makes a cURL call and loops through $encodings array to parse headers
	 * looking for a match.
	 * Also checks Server header for cloudflare string
	 * Caches cURL headers in transient cache for 12 hours
	 *
	 **/
	private function server_header_call() {
		$encodings = array('gzip', 'br');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, home_url());
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');

		foreach ($encodings as $encoding) {
			curl_setopt($ch, CURLOPT_ENCODING, $encoding);

			$header = get_transient( 'a2-server_resp2-' . $encoding );

			if (false === $header) {
				$header = curl_exec($ch);
				set_transient( 'a2-server_resp2-' . $encoding, $header, WEEK_IN_SECONDS );
			}
			$temp_headers = explode("\n", $header);
			foreach ($temp_headers as $i => $header) {
				$header = explode(':', $header, 2);
				if (isset($header[1])) {
					$headers[$header[0]] = $header[1];
				} else {
					$headers[$header[0]] = '';
				}
			}
			if (isset($headers['Server']) && (strpos(strtolower($headers['Server']), 'cloudflare') !== false)) {
				$this->cf = true;
			}
			if (isset($headers['server']) && (strpos(strtolower($headers['server']), 'cloudflare') !== false)) {
				$this->cf = true;
			}
			if (isset($headers['Content-Encoding']) && (strpos(strtolower($headers['Content-Encoding']), 'gzip') !== false)) {
				$this->gzip = true;
			}
			if (isset($headers['Content-Encoding']) && (strpos(strtolower($headers['Content-Encoding']), 'br') !== false)) {
				$this->br = true;
			}
		}
		curl_close($ch);
	}
}
