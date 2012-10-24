<?php

/**
 * Remote Data Return
 *
 * PHP Version 5.3
 *
 * Prints data used by CURL library integration tests.
 *
 * @category  Library
 * @package   BCA/CURL
 * @author    Brodkin CyberArts <support@brodkinca.com>
 * @copyright 2012 Brodkin CyberArts.
 * @license   All rights reserved.
 * @version   GIT: $Id$
 * @link      https://github.com/brodkinca/BCA-PHP-CURL
 */

if (isset($_GET['http_code'])) {
    if ($_GET['http_code'] == 404) {
        header("HTTP/1.0 404 Not Found");
    } elseif ($_GET['http_code'] == 500) {
        header("HTTP/1.0 500 Internal Server Error");
    }
}

header('Content-Type: application/json');

// Authentication
$data['auth_user'] = @$_SERVER['PHP_AUTH_USER'];
$data['auth_pass'] = @$_SERVER['PHP_AUTH_PW'];

// Headers
$data['headers'] = apache_request_headers();

// Fill in parameters
parse_str(file_get_contents("php://input"), $params);

$data['_GET'] = $_GET;
$data['_POST'] = $_POST;
$data['_PUT'] = $params;
$data['_DELETE'] = $params;

// Respond to request with JSON
echo json_encode($data);
