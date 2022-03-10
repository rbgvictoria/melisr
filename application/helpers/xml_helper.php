<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('xml_output')) {
    /**
     * function xml_output
     * 
     * Adds HTTP Content-type header for XML 
     * 
     * @param string $xml
     * @return string
     */
    function xml_output($xml) {
        header('Content-type: text/xml');
        return $xml;
    }
}

/*
 * /application/helpers/xml_helper.php
 */