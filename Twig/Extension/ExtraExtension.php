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
                } else if($assoc) { // When assoc mode
                    return $filtered;
                } else {
                    return '';
                }
            }),
            new \Twig_SimpleFunction('repository', [$this, 'getRepository']),
            new \Twig_SimpleFunction('match', [$this, 'regexMatch']),
            new \Twig_SimpleFunction('matches', [$this, 'regexMatches']),
            new \Twig_SimpleFunction('replace', [$this, 'regexReplace']),
        );
    }

    /**
     * @return array
     */
    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('query_string', [$this, 'getQueryString']),
            new \Twig_SimpleFilter('unset', [$this, 'unsetValue']),
            new \Twig_SimpleFilter('url_slug', [$this, 'getUrlSlug']),
            new \Twig_SimpleFilter('date_diff', [$this, 'getDateDiff']),
            new \Twig_SimpleFilter('date_locale', [$this, 'getLocaleDate']),
            new \Twig_SimpleFilter('date_modify', [$this, 'getModifiedDate']),
            new \Twig_SimpleFilter('abbr', [$this, 'abbreviate']),
            new \Twig_SimpleFilter('md5', [$this, 'getMD5String']),
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
        return is_string($string) ? StringUtilities::getUrlSlug($string) : $string;
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
        if(!is_int($fromTime)) {
            $fromTime = strtotime($fromTime);
        }

        if(null === $untilTime) {
            $untilTime = time();
        } elseif(!is_int($untilTime)) {
            $untilTime = strtotime($untilTime);
        }

        return DateTimeUtilities::getTimeStampDiff($fromTime, $untilTime);
    }

    /**
     * @param \DateTime|int|string $time
     * @param string $modification
     * @param null|string $format
     * @throws \Exception
     * @return \DateTime|string
     */
    public function getModifiedDate($time, $modification, $format = null) {
        if($time instanceof \DateTime) {
            $time = $time->getTimestamp();
        }
        if(!is_int($time)) {
            $time = strtotime($time);
        }

        $modified = DateTimeUtilities::getModifiedDateTime($modification, $time);
        return null === $format ? $modified : $modified->format($format);
    }

    /**
     * @param string @string
     * @param int @charNum
     * @return string
     */
    public function abbreviate($string, $charNum = 40) {
        $replacement = ' ... ';
        if (strlen($string) <= $charNum) {
            return $string;
        }

        return substr($string, 0, $charNum - strlen($replacement)). $replacement;
    }

    /**
     * @param $time
     * @return bool|string
     * @throws \Exception
     */
    public function getLocaleDate($time, $locale = null) {

        if($time instanceof \DateTime) {
            $time = $time->getTimestamp();
        }
        if(!is_int($time)) {
            $time = strtotime($time);
        }
        
        if ($locale === null) {
            $locale = $this->requestStack->getCurrentRequest()->get('_locale');
        }

        return ($locale === 'en')
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
        return (preg_match($pattern, $subject) === 1);
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