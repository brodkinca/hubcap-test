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

    /**
     * Populate Data
     *
     * @param string $response Data received from cURL request.
     * @param array  $info     Array returned from curl_getinfo().
     */
    public function __construct($response, array $info)
    {
        settype($response, 'string');

        $this->_response = $response;
        $this->_info = $info;
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

}
