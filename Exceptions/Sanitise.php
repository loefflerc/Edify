<?php

namespace Edify\Exceptions;
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sanitise
 *
 * @author IrishAdo <irishado@php-programmers.net>
 */
class Sanitise extends \Exception{

    protected $severity;

    public function __construct($message, $code=0, $severity=1, $filename=__FILE__, $lineno=__LINE__) {
        $this->message = $message;
        $this->code = $code;
        $this->severity = $severity;
        $this->file = $filename;
        $this->line = $lineno;
    }

    public function getSeverity() {
        return $this->severity;
    }
}

?>