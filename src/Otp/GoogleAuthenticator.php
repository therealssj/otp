<?php
namespace Otp;

/**
 * Google Authenticator
 *
 * Last update: 2012-06-16
 *
 * Can be easy used with Google Authenticator
 * @link https://code.google.com/p/google-authenticator/
 *
 * @author Christian Riesen <chris.riesen@gmail.com>
 * @link http://christianriesen.com
 * @license MIT License see LICENSE file
 */

class GoogleAuthenticator
{
	protected static $allowedTypes = array('hotp', 'totp');
	
	protected static $height = 200;
	protected static $width = 200;
	
	/**
	 * Returns the QR code url
	 *
	 * Format of encoded url is here:
	 * https://code.google.com/p/google-authenticator/wiki/KeyUriFormat
	 * Should be done in a better fashion
	 *
	 * @param string $type totp or hotp
	 * @param string $label Label to display this as to the user
	 * @param string $secret Base32 encoded secret
	 * @param integer $counter Required by hotp, otherwise ignored
	 * @param array $options Optional fields that will be set if present
	 *
	 * @return string URL to the QR code
	 */
	public static function getQrCodeUrl($type, $label, $secret, $counter = null, $options = array())
	{
		// two types only..
		if (!in_array($type, self::$allowedTypes)) {
			throw new \InvalidArgumentException('Type has to be of allowed types list');
		}
		
		// Label can't be empty
		$label = trim($label);
		
		if (strlen($label) < 1) {
			throw new \InvalidArgumentException('Label has to be one or more printable characters');
		}
		
		// Secret needs to be here
		if (strlen($secret) < 1) {
			throw new \InvalidArgumentException('No secret present');
		}
		
		// check for counter on hotp
		if ($type == 'hotp' && is_null($counter)) {
			throw new \InvalidArgumentException('Counter required for hotp');
		}
		
		// This is the base, these are at least required
		$otpauth = 'otpauth://' . $type . '/' . $label . '?secret=' . $secret;
		
		if ($type == 'hotp' && !is_null($counter)) {
			$otpauth .= '&counter=' . $counter;
		}
		
		// Now check the options array

		// algorithm (currently ignored by Authenticator)
		// Defaults to SHA1
		if (array_key_exists('algorithm', $options)) {
			$otpauth .= '&algorithm=' . $options['algorithm'];
		}
		
		// digits (currently ignored by Authenticator)
		// Defaults to 6
		if (array_key_exists('digits', $options)) {
			$otpauth .= '&digits=' . $options['digits'];
		}
		
		// period, only for totp (currently ignored by Authenticator)
		// Defaults to 30
		if ($type == 'totp' && array_key_exists('period', $options)) {
			$otpauth .= '&period=' . $options['period'];
		}
		
		// Width and height can be overwritten
		$width = self::$width;
		
		if (array_key_exists('width', $options) && is_numeric($options['width'])) {
			$width = $options['width'];
		}
		
		$height = self::$height;
		
		if (array_key_exists('height', $options) && is_numeric($options['height'])) {
			$height = $options['height'];
		}
		
		$url = 'https://chart.googleapis.com/chart?chs=' . $width . 'x'
			 . $height . '&cht=qr&chld=M|0&chl=' . urlencode($otpauth);
		
		return $url;
	}

}
