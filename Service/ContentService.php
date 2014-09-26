<?php

namespace Recognize\ExtraBundle\Service;

/**
 * Class ContentService
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class ContentService {

	/**
	 * @var array Containing proxy settings
	 */
	protected static $settings = array();

	/**
	 * @var int
	 */
	protected static $timeout = 5000;

	/**
	 * @var string
	 */
	protected static $basicAuthentication = null;


	/**
	 * @param string $username
	 * @param string $password
	 */
	protected function setBasicAuthentication($username, $password) {
		self::$basicAuthentication = sprintf('%s:%s', $username, $password);
	}

	/**
	 * @return null|string
	 */
	protected function getBasicAuthentication() {
		return self::$basicAuthentication;
	}

	/**
	 * @return bool
	 */
	protected static function hasProxySettings() {
		return (isset(self::$settings['proxy']));
	}

	/**
	 * Returns specified proxy setting
	 * @param $setting
	 * @throws \Exception When setting doesn't exist
	 * @return mixed Setting value
	 */
	protected static function getProxySetting($setting) {
		$settings = self::getSetting('proxy'); // proxy settings
		if(array_key_exists($settings, $setting)) {
			return $settings[$setting];
		}
		throw new \Exception(sprintf('Unable to set proxy, required setting "%s" doesn\'t exist', $setting));
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	protected static function getSetting($key) {
		return (array_key_exists($key, self::$settings)) ? self::$settings[$key] : null;
	}

	/**
	 * @return int|string
	 */
	protected static function getTimeout() {
		$timeout = self::$timeout; // Default timeout
		if($settingTimeout = self::getSetting('timeout')) {
			$timeout = $settingTimeout;
		}
		return $timeout;
	}

	/**
	 * @return null|array Containing proxy settings
	 */
	protected static function getSettings() {
		return self::$settings;
	}

	/**
	 * @param array $settings
	 */
	protected static function setSettings(array $settings) {
		self::$settings = $settings;
	}

	/**
	 * Initializes an cURL resource
	 * @param string $contentUrl
	 * @param array $settings
	 * @param array $headers
	 * @param bool $useSSL
	 * @return resource
	 */
	protected static function initialize($contentUrl, array $settings, Array $headers = array(), $useSSL = false) {
		self::setSettings($settings); // Load given settings
		$curl = curl_init($contentUrl); // URL encode
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::getTimeout());
		if($useSSL) curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		if(!empty($headers)) curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		if(self::hasProxySettings()) { // When proxy was set...
			curl_setopt($curl, CURLOPT_PROXY, self::getProxySetting('host'));
			curl_setopt($curl, CURLOPT_PROXYPORT, self::getProxySetting('port'));
			curl_setopt($curl, CURLOPT_PROXYTYPE, self::getProxySetting('type'));
		}
		return $curl;
	}

	/**
	 * @param $curl Resource
	 * @throws \Exception When cURL connection fails
	 * @return mixed Retrieved contents
	 */
	protected static function responseHandler($curl) {
		$content = curl_exec($curl); // Execute curl
		if($content !== false) {
			$info = curl_getinfo($curl);
			//return $info;
			curl_close($curl);
			return $content;
		}
		throw new \Exception(curl_error($curl), 500);
	}

	/**
	 * @param string $contentUrl Content URL
	 * @param array $settings
	 * @param array $headers
	 * @param bool $useSSL
	 * @return mixed
	 */
	public static function getContents($contentUrl, array $settings = array(), Array $headers = array(), $useSSL = false) {
		$curl = self::initialize($contentUrl, $settings, $headers, $useSSL);
		if($authentication = self::getBasicAuthentication()) {
			curl_setopt($curl, CURLOPT_USERPWD, $authentication);
		}
		return self::responseHandler($curl);
	}

	/**
	 * @param $curl
	 * @param null $data
	 */
	protected static function setData(&$curl, $data = null) {
		$isObjectOrArray = (is_array($data) || is_object($data)); // When object or array build query
		curl_setopt($curl, CURLOPT_POSTFIELDS, ($isObjectOrArray ? http_build_query($data) : $data));
	}

	/**
	 * @param string $contentUrl Content URL
	 * @param array $postData Data that should be posted
	 * @param array $settings
	 * @param array $headers
	 * @param bool $useSSL
	 * @return mixed
	 */
	public static function postContents($contentUrl, $postData = null, array $settings = array(), Array $headers = array(), $useSSL = false) {
		$curl = self::initialize($contentUrl, $settings, $headers, $useSSL);
		curl_setopt($curl, CURLOPT_POST, true);
		self::setData($curl, $postData);
		return self::responseHandler($curl);
	}

	/**
	 * @param string $contentUrl
	 * @param null $putData
	 * @param array $settings
	 * @param array $headers
	 * @param bool $useSSL
	 * @return mixed
	 */
	public static function putContents($contentUrl, $putData = null, array $settings = array(), Array $headers = array(), $useSSL = false) {
		$curl = self::initialize($contentUrl, $settings, $headers, $useSSL);
		curl_setopt($curl, CURLOPT_PUT, true);
		self::setData($curl, $putData);
		return self::responseHandler($curl);
	}

}