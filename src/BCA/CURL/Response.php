<?php

/**
 * cURL Library
 *
 * PHP Version 5.3
 *
 * @category  Library
 * @package   BCA/CURL
 * @author    Brodkin CyberArts <support@brodkinca.com>
 * @copyright 2012 Brodkin CyberArts.
 * @license   All rights reserved.
 * @version   GIT: $Id$
 * @link      https://github.com/brodkinca/BCA-PHP-CURL
 */

namespace BCA\CURL;

/**
 * cURL Response Class
 *
 * @category  Library
 * @package   BCA/CURL
 * @author    Brodkin CyberArts <support@brodkinca.com>
 * @copyright 2012 Brodkin CyberArts.
 * @license   All rights reserved.
 * @link      https://github.com/brodkinca/BCA-PHP-CURL
 */
class Response
{
    private $_response;
    private $_info;
    private $_error;

    /**
     * Populate Data
     *
     * @param string $response Data received from cURL request.
     * @param array  $info     Array returned from curl_getinfo().
     * @param array  $error    Array of error data, if applicable.
     */
    public function __construct($response, array $info, array $error=array())
    {
        settype($response, 'string');

        $this->_response = $response;
        $this->_info = $info;
        $this->_error = $error;
    }

    /**
     * Get cURL Response Data
     *
     * @param string $key Key returned by curl_getinfo().
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->_info["$key"])) {
            return $this->_info["$key"];
        }

        return null;
    }

    /**
     * Return Response as String
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_response;
    }

    /**
     * Print Debug Message to Screen
     *
     * @return null
     */
    public function debug()
    {
        echo "=============================================<br/>\n";
        echo "<h2>CURL Request Debugger</h2>\n";
        echo "=============================================<br/>\n";
        echo "<h3>Response</h3>\n";
        echo "<code>" . nl2br(htmlentities($this->_response)) . "</code><br/>\n\n";

        if ($this->_error) {
            echo "=============================================<br/>\n";
            echo "<h3>Errors</h3>";
            echo "<strong>Code:</strong> ".$this->_error['code']."<br/>\n";
            echo "<strong>Message:</strong> ".$this->_error['message']."<br/>\n";
        }

        echo "=============================================<br/>\n";
        echo "<h3>Info</h3>";
        echo "<pre>";
        print_r(!empty($this->_info));
        echo "</pre>";
    }

}
