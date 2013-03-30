<?php

namespace Edify\Utils;

/**
 * Series of functions designed to check if a value is of a specific type
 * mostly used to protect your application from invlaid data.
 */

/**
 * Sanitise your data
 *
 * @author IrishAdo <irishado@php-programmers.net>
 */
class Sanitise {

    /**
     * Is the parameter a natural number
     *
     * @param type $val
     * @param type $acceptzero
     * @return boolean
     * @author ja at krystof dot org (php.net manual)
     */
    function is_natural($val, $acceptzero = false) {
        $return = ((string) $val === (string) (int) $val);
        if ($acceptzero)
            $base = 0;
        else
            $base = 1;
        if (!$return || intval($val) < $base){
            throw new \Edify\Exceptions\Sanitise("Value supplied is not a natural whole number - 00000003");
        }
        return $return;
    }

    /**
     * is_text is used to test if a value is a string or not
     *
     * @throw Edify\Exception\Sanitise
     * @param type $val
     * @param type $maxLength
     * @return String
     */
    function is_text($val, $maxLength = -1) {
        if (!is_string($val)) {
            throw new \Edify\Exceptions\Sanitise("Value supplied is not a String - 00000001");
        }
        if ($maxLength!=-1 && strlen($val) > $maxLength) {
            throw new \Edify\Exceptions\Sanitise("Value supplied is not a String - 00000002");
        }
        return $val;
    }

}

?>
