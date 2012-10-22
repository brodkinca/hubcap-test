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
 * cURL Request Class
 *
 * Work with remote servers via cURL much easier than using the native PHP bindings.
 *
 * @category  Library
 * @package   BCA/CURL
 * @author    Brodkin CyberArts <support@brodkinca.com>
 * @author    Philip Sturgeon <email@philsturgeon.co.uk>
 * @copyright 2012 Brodkin CyberArts.
 * @license   All rights reserved.
 * @version   GIT: $Id$
 * @link      https://github.com/brodkinca/BCA-PHP-CURL
 */
class CURL
{
    protected $response;       // Contains the cURL response for debug
    protected $session;             // Contains the cURL handler for a session
    protected $url;                 // URL of the session
    protected $options;   // Populates curl_setopt_array
    protected $headers;   // Populates extra HTTP headers

    public $error_code;             // Error code returned as an int
    public $error_string;           // Error message returned as a string
    public $info;                   // Returned after request (elapsed time, etc)

    /**
     * Constructor
     *
     * @param string $url Valid URL resource
     */
    public function __construct($url)
    {
        if ( ! $this->_hasExtCurl()) {
            trigger_error('cURL Class - PHP was not built with cURL enabled. Rebuild PHP with --with-curl to use cURL.', E_USER_ERROR);
        }

        // Set Default Values
        $this->reset();

        // Create Session
        $this->_startSession($url);
    }

    /**
     * Simple Call Front Controller
     *
     * @param string $method    Valid HTTP method
     * @param array  $arguments Array of query parameters
     *
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        if (in_array($method, array('simple_get', 'simple_post', 'simple_put', 'simple_delete'))) {
            // Take off the "simple_" and past get/post/put/delete to _simpleCall
            $verb = str_replace('simple_', '', $method);
            array_unshift($arguments, $verb);

            return call_user_func_array(array($this, '_simpleCall'), $arguments);
        }
    }

    /**
     * Get File From FTP Server
     *
     * @param string $url       URL of remote FTP host
     * @param string $file_path Path to file on remote host
     * @param string $username  FTP username
     * @param string $password  FTP password
     *
     * @return mixed
     */
    public function simpleFTPGet($url, $file_path, $username = '', $password = '')
    {
        // If there is no ftp:// or any protocol entered, add ftp://
        if ( ! preg_match('!^(ftp|sftp)://! i', $url)) {
            $url = 'ftp://' . $url;
        }

        // Use an FTP login
        if ($username != '') {
            $auth_string = $username;

            if ($password != '') {
                $auth_string .= ':' . $password;
            }

            // Add the user auth string after the protocol
            $url = str_replace('://', '://' . $auth_string . '@', $url);
        }

        // Add the filepath
        $url .= $file_path;

        $this->option(CURLOPT_BINARYTRANSFER, true);
        $this->option(CURLOPT_VERBOSE, true);

        return $this->_execute();
    }

    /**
     * POST HTTP Request
     *
     * @param array $params  Array of query parameters
     * @param array $options Array of cURL options
     *
     * @return mixed
     */
    public function post(array $params = array(), array $options = array())
    {
        // If its an array (instead of a query string) then format it correctly
        if (is_array($params)) {
            $params = http_build_query($params, null, '&');
        }

        // Add in the specific options provided
        $this->options($options);

        $this->method('post');

        $this->option(CURLOPT_POST, true);
        $this->option(CURLOPT_POSTFIELDS, $params);

        return $this->_execute();
    }

    /**
     * PUT HTTP Request
     *
     * @param array $params  Array of query parameters
     * @param array $options Array of cURL options
     *
     * @return mixed
     */
    public function put(array $params = array(), array $options = array())
    {
        // If its an array (instead of a query string) then format it correctly
        if (is_array($params)) {
            $params = http_build_query($params, null, '&');
        }

        // Add in the specific options provided
        $this->options($options);

        $this->method('put');
        $this->option(CURLOPT_POSTFIELDS, $params);

        // Overrides $_POST with PUT data
        $this->option(CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));

        return $this->_execute();
    }

    /**
     * DELETE HTTP Request
     *
     * @param array $params  Array of query parameters
     * @param array $options Array of cURL options
     *
     * @return mixed
     */
    public function delete(array $params, array $options = array())
    {
        // If its an array (instead of a query string) then format it correctly
        if (is_array($params)) {
            $params = http_build_query($params, null, '&');
        }

        // Add in the specific options provided
        $this->options($options);

        $this->method('delete');

        $this->option(CURLOPT_POSTFIELDS, $params);

        return $this->_execute();
    }

    /**
     * Set Session Cookies
     *
     * @param array $params Associative array of cookie keys and values
     *
     * @return self
     */
    public function cookies(array $params = array())
    {
        if (is_array($params)) {
            $params = http_build_query($params, null, '&');
        }

        $this->option(CURLOPT_COOKIE, $params);

        return $this;
    }

    /**
     * Set HTTP Header
     *
     * @param string $header  Key of HTTP header
     * @param string $content Value of HTTP header
     *
     * @return self
     */
    public function header($header, $content = null)
    {
        $this->headers[] = $content ? $header . ': ' . $content : $header;

        return $this;
    }

    /**
     * Set HTTP Method
     *
     * @param string $method Valid HTTP method
     *
     * @return self
     */
    public function method($method)
    {
        $this->options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);

        return $this;
    }

    /**
     * Set HTTP username and password
     *
     * @param string $username Username
     * @param string $password Password
     * @param string $type     Any valid value for CURLOPT_HTTPAUTH
     *
     * @return self
     */
    public function auth($username = '', $password = '', $type = 'any')
    {
        $this->option(CURLOPT_HTTPAUTH, constant('CURLAUTH_' . strtoupper($type)));
        $this->option(CURLOPT_USERPWD, $username . ':' . $password);

        return $this;
    }

    /**
     * Set Proxy Server
     *
     * @param string  $url      URL of proxy server
     * @param integer $port     Port number of proxy server
     * @param string  $username Proxy username
     * @param string  $password Proxy password
     *
     * @return self
     */
    public function proxy($url = '', $port = 80, $username = '', $password = '')
    {
        $this->option(CURLOPT_HTTPPROXYTUNNEL, true);
        $this->option(CURLOPT_PROXY, $url . ':' . $port);

        $username AND $this->option(CURLOPT_PROXYUSERPWD, $username . ':' . $password);

        return $this;
    }

    /**
     * Set SSL Options
     *
     * @param boolean $verify_peer  Require a valid certificate
     * @param integer $verify_host  Require a hostname match of certificate
     * @param string  $path_to_cert Local path to certificate(s) file
     *
     * @return self
     */
    public function ssl($verify_peer = true, $verify_host = 2, $path_to_cert = null)
    {
        if ($verify_peer) {
            $this->option(CURLOPT_SSL_VERIFYPEER, true);
            $this->option(CURLOPT_SSL_VERIFYHOST, $verify_host);
            if (isset($path_to_cert)) {
                $path_to_cert = realpath($path_to_cert);
                $this->option(CURLOPT_CAINFO, $path_to_cert);
            }
        } else {
            $this->option(CURLOPT_SSL_VERIFYPEER, false);
        }

        return $this;
    }

    /**
     * Set cURL Option
     *
     * @param string $code  Key of cURL option to set or override
     * @param string $value New value of cURL option
     *
     * @return self
     */
    public function option($code, $value)
    {
        if (is_string($code) && !is_numeric($code)) {
            $code = constant('CURLOPT_' . strtoupper($code));
        }

        $this->options[$code] = $value;

        return $this;
    }

    /**
     * Print Debug Message to Screen
     *
     * @return null
     */
    public function debug()
    {
        echo "=============================================<br/>\n";
        echo "<h2>CURL Test</h2>\n";
        echo "=============================================<br/>\n";
        echo "<h3>Response</h3>\n";
        echo "<code>" . nl2br(htmlentities($this->last_response)) . "</code><br/>\n\n";

        if ($this->error_string) {
            echo "=============================================<br/>\n";
            echo "<h3>Errors</h3>";
            echo "<strong>Code:</strong> " . $this->error_code . "<br/>\n";
            echo "<strong>Message:</strong> " . $this->error_string . "<br/>\n";
        }

        echo "=============================================<br/>\n";
        echo "<h3>Info</h3>";
        echo "<pre>";
        print_r($this->info);
        echo "</pre>";
    }

    /**
     * Execute Request and Return Result
     *
     * @return mixed
     */
    private function _execute()
    {
        // Set two default options, and merge any extra ones in
        if ( ! isset($this->options[CURLOPT_TIMEOUT])) {
            $this->options[CURLOPT_TIMEOUT] = 30;
        }
        if ( ! isset($this->options[CURLOPT_RETURNTRANSFER])) {
            $this->options[CURLOPT_RETURNTRANSFER] = true;
        }
        if ( ! isset($this->options[CURLOPT_FAILONERROR])) {
            $this->options[CURLOPT_FAILONERROR] = true;
        }

        // Only set follow location if not running securely
        if ( ! ini_get('safe_mode') && ! ini_get('open_basedir')) {
            // Ok, follow location is not set already so lets set it to true
            if ( ! isset($this->options[CURLOPT_FOLLOWLOCATION])) {
                $this->options[CURLOPT_FOLLOWLOCATION] = true;
            }
        }

        if ( ! empty($this->headers)) {
            $this->option(CURLOPT_HTTPHEADER, $this->headers);
        }

        $this->options();

        // Execute the request & and hide all output
        $this->response = curl_exec($this->session);
        $this->info = curl_getinfo($this->session);

        if ($this->response === false) {
            // Request failed
            $errno = curl_errno($this->session);
            $error = curl_error($this->session);

            curl_close($this->session);
            $this->_reset();

            $this->error_code = $errno;
            $this->error_string = $error;

            return false;

        } else {
            // Request successful
            curl_close($this->session);
            $this->last_response = $this->response;
            $this->_reset();

            return $this->last_response;
        }
    }

    /**
     * Was PHP Built with cURL Support?
     *
     * @return boolean
     */
    private function _hasExtCurl()
    {
        return function_exists('curl_init');
    }

    /**
     * Handler for Simple Calls
     *
     * @param string $method  Valid HTTP method
     * @param string $url     URL resource to query
     * @param array  $params  Array of query parameters
     * @param array  $options Array cURL options
     *
     * @return array
     */
    private function _simpleCall($method, $url, array $params = array(), array $options = array())
    {
        // Get acts differently, as it doesnt accept parameters in the same way
        if ($method === 'get') {
            // If a URL is provided, create new session
            $this->_startSession($url.($params ? '?'.http_build_query($params, null, '&') : ''));
        } else {
            // If a URL is provided, create new session
            $this->_startSession($url);

            $this->{$method}($params);
        }

        // Add in the specific options provided
        $this->options($options);

        return $this->_execute();
    }

    /**
     * Start cURL Session
     *
     * @param string $url Valid URL resource
     *
     * @return self
     */
    private function _startSession($url)
    {
        $this->url = $url;
        $this->session = curl_init($this->url);

        return $this;
    }

    /**
     * Set Values to Defaults
     *
     * @return null
     */
    private function _reset()
    {
        $this->response = '';
        $this->headers = array();
        $this->options = array();
        $this->error_code = null;
        $this->error_string = '';
        $this->session = null;
    }

}
