<?php

namespace Recognize\ExtraBundle\Twig\Extension;

use Recognize\ExtraBundle\Utility\DateTimeUtilities;
use Recognize\ExtraBundle\Utility\StringUtilities;
use Symfony\Bridge\Doctrine\RegistryInterface,
	Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ExtraExtension
 * @package Recognize\ExtraBundle\Twig\Extension
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class ExtraExtension extends \Twig_Extension {

	/**
	 * @var \Symfony\Component\HttpFoundation\RequestStack
	 */
	protected $requestStack;

	/**
	 * @var \Symfony\Bridge\Doctrine\RegistryInterface
	 */
	protected $registry;


	/**
	 * @param RequestStack $request
	 * @param \Symfony\Bridge\Doctrine\RegistryInterface $registry
	 */
	public function __construct(RequestStack $request, RegistryInterface $registry) {
		$this->requestStack = $request;
		$this->registry =  $registry;
	}

	/**
	 * @return array
	 */
	public function getFunctions() {
		$requestStack = $this->requestStack;
		return array(
			new \Twig_SimpleFunction('request', function($param) use ($requestStack) {
				$request = $requestStack->getCurrentRequest();
				return $request->get($param);
			}),
			new \Twig_SimpleFunction('queryString', function(array $filter = array(), $assoc = false) use ($requestStack) {
				$stack = $requestStack->getCurrentRequest()->query->all();
				$filtered = array_diff_key($stack, array_flip($filter));
				if(!empty($filtered) && !$assoc) {
					array_walk($filtered, function(&$item, $key) {
						$item = $key . '=' . $item;
					});
					return '?'.implode('&', $filtered);
				} elseif($assoc) { // When assoc mode
					return $filtered;
				} else return '';
			}),
			'repository' => new \Twig_Function_Method($this, 'getRepository'),
			'match' => new \Twig_Function_Method($this, 'regexMatch'),
			'matches' => new \Twig_Function_Method($this, 'regexMatches'),
			'replace' => new \Twig_Function_Method($this, 'regexReplace'),
		);
	}

	/**
	 * @return array
	 */
	public function getFilters() {
		return array(
			'query_string' => new \Twig_Filter_Method($this, 'getQueryString'),
			'unset' => new \Twig_Filter_Method($this, 'unsetValue'),
			'url_slug' => new \Twig_Filter_Method($this, 'getUrlSlug'),
			'date_diff' => new \Twig_Filter_Method($this, 'getDateDiff'),
			'date_locale' => new \Twig_Filter_Method($this, 'getLocaleDate'),
			'date_modify' => new \Twig_Filter_Method($this, 'getModifiedDate'),
			'abbr' => new \Twig_Filter_Method($this, 'abbreviate'),
			'md5' => new \Twig_Filter_Method($this, 'getMD5String'),
		);
	}

	/**
	 * @param array $array
	 * @param $value
	 * @return array
	 */
	public function unsetValue(array $array, $value) {
		if(isset($array[$value])) unset($array[$value]);
		return $array;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public function getUrlSlug($string) {
		return (is_string($string)) ? StringUtilities::getUrlSlug($string) : $string;
	}

	/**
	 * @param $repository
	 * @return \Doctrine\Common\Persistence\ObjectRepository
	 */
	public function getRepository($repository) {
		return $this->registry->getRepository($repository);
	}

	/**
	 * @param int|string $fromTime
	 * @param int|string $untilTime
	 * @return \DateInterval
	 */
	public function getDateDiff($fromTime, $untilTime = null) {
		if(!is_int($fromTime)) $fromTime = strtotime($fromTime);

		if(is_null($untilTime)) $untilTime = time();
		elseif(!is_int($untilTime)) $untilTime = strtotime($untilTime);

		return DateTimeUtilities::getTimeStampDiff($fromTime, $untilTime);
	}

	/**
	 * @param int|string $time
	 * @param string $modification
	 * @param null|string $format
	 * @throws \Exception
	 * @return \DateTime|string
	 */
	public function getModifiedDate($time, $modification, $format = null) {
		if(!is_int($time)) $time = strtotime($time);

		$modified = DateTimeUtilities::getModifiedDateTime($modification, $time);
		return (is_null($format)) ? $modified : $modified->format($format);
	}
	
	/**
	 * @param string @string
	 * @param int @charNum
	 * @return string
	 */
	public function abbreviate($string, $charNum = 40) {
		$replacement = " ... ";
		if (strlen($string) <= $charNum) {
			return $string;
		} else {
			return substr($string, 0, $charNum - strlen($replacement)). $replacement;
		} 
	}

	/**
	 * @param $time
	 * @return bool|string
	 * @throws \Exception
	 */
	public function getLocaleDate($time) {

		if($time instanceof \DateTime) $time = $time->getTimestamp();
		if(!is_int($time)) $time = strtotime($time);

		return ($this->requestStack->getCurrentRequest()->get('_locale') == 'en')
			? DateTimeUtilities::getFormattedDateTime('Y-m-d', $time)
			: DateTimeUtilities::getFormattedDateTime('d-m-Y', $time);
	}
	
	public function getMD5String($string) {
		return md5($string);
	}
	
	public function regexMatch($pattern, $subject) {
		preg_match($pattern, $subject, $matches);
		return $matches[0];
	}
	
	public function regexMatches($pattern, $subject) {
		return (preg_match($pattern, $subject) == 1);
	}
	
	public function regexReplace($pattern, $replacement, $subject) {
		return preg_replace($pattern, $replacement, $subject);
	}

    /**
     * @param $queryString
     * @return string
     */
    public function getQueryString($queryString) {
        return ($queryString) ? '?'.$queryString : '';
    }

	/**
	 * @return string Name
	 */
	public function getName() {
		return 'recognize_extra.twig.extension';
	}

}