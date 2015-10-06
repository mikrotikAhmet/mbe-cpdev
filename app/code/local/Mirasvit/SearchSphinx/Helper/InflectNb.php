<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.1
 * @revision  675
 * @copyright Copyright (C) 2014 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_SearchSphinx_Helper_InflectNb extends Mage_Core_Helper_Abstract
{

    static $plural = array(
    );

    static $singular = array(
        '/a$/i'       => "",
        '/e$/i'       => "",
        '/ede$/i'     => "",
        '/ande$/i'    => "",
        '/ende$/i'    => "",
        '/ane$/i'     => "",
        '/ene$/i'     => "",
        '/hetene$/i'  => "",
        '/en$/i'      => "",
        '/heten$/i'   => "",
        '/ar$/i'      => "",
        '/er$/i'      => "",
        '/heter$/i'   => "",
        '/as$/i'      => "",
        '/es$/i'      => "",
        '/edes$/i'    => "",
        '/endes$/i'   => "",
        '/enes$/i'    => "",
        '/hetenes$/i' => "",
        '/ens$/i'     => "",
        '/hetens$/i'  => "",
        '/ers$/i'     => "",
        '/ets$/i'     => "",
        '/et$/i'      => "",
        '/het$/i'     => "",
        '/ast$/i'     => "",
    );


    static $irregular = array(
    );

    static $uncountable = array(
    );

    /**
     * ÐÐ¾Ð·Ð²ÑÐ°ÑÐ°ÐµÑ ÑÐ»Ð¾Ð²Ð¾ Ð²Ð¾ Ð¼Ð½Ð¾Ð¶ÐµÑÑÐ²ÐµÐ½Ð½Ð¾Ð¼ ÑÐ¸ÑÐ»Ðµ (shoe -> shoes)
     *
     * @param  string $string
     *
     * @return string
     */
    public function pluralize($string)
    {
        // save some time in the case that singular and plural are the same
        if (in_array(strtolower($string), self::$uncountable)) {
            return $string;
        }

        // check for irregular singular forms
        foreach (self::$irregular as $pattern => $result) {
            $pattern = '/' . $pattern . '$/i';

            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        // check for matches using regular expressions
        foreach (self::$plural as $pattern => $result ) {
            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        return $string;
    }

    /**
     * ÐÐ¾Ð·Ð²ÑÐ°ÑÐ°ÐµÑ ÑÐ»Ð¾Ð²Ð¾ Ð² Ð¾Ð´Ð¸Ð½Ð¾ÑÐ½Ð¾Ð¼ ÑÐ¸ÑÐ»Ðµ (shoes -> shoe)
     *
     * @param  string $string
     *
     * @return string
     */
    public function singularize($string)
    {
        // save some time in the case that singular and plural are the same
        if (in_array(strtolower($string), self::$uncountable)) {
            return $string;
        }

        // check for irregular plural forms
        foreach (self::$irregular as $result => $pattern) {
            $pattern = '/'.$pattern.'$/i';

            if (preg_match($pattern, $string)) {
                return preg_replace($pattern, $result, $string);
            }
        }

        // check for matches using regular expressions
        foreach (self::$singular as $pattern => $result) {
            if (preg_match($pattern, $string)) {
                $sing = preg_replace($pattern, $result, $string);
                if (strlen($sing) >= 3) {
                    return $sing;
                } else {
                    return $string;
                }
            }
        }

        return $string;
    }
}