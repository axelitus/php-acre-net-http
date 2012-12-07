<?php
/**
 * Part of the axelitus\Acre\Net\Http Package.
 *
 * @package     axelitus\Acre\Net\Http
 * @version     0.1
 * @author      Axel Pardemann (dev@axelitus.mx)
 * @license     MIT License
 * @copyright   2012 - Axel Pardemann
 * @link        http://axelitus.mx/
 */

namespace axelitus\Acre\Net\Http;

use InvalidArgumentException;
use axelitus\Acre\Net\Http\Method as Method;

/**
 * Requires axelitus\Acre\Net\Uri package
 */
use axelitus\Acre\Net\Uri\Uri as Uri;

/**
 * Request Class
 *
 * @package     axelitus\Acre\Net\Http
 * @category    Net\Http
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Request extends Message
{
    /**
     * @var string      The request's method
     */
    protected $method = Method::GET;

    /**
     * @var Uri     The request's uri
     */
    protected $uri = null;

    /**
     * Custom Request message constructor to ensure the uri is an object
     *
     * @param array $options    An array containing the initial request message options
     */
    protected function __construct(array $options)
    {
        parent::__construct($options);

        if ($this->uri === null) {
            $this->setUri(Uri::forge());
        }
    }

    /**
     * Tests if the given string is valid (using the regex). It can additionally return the named capturing
     * group(s) using the $matches parameter as a reference.
     *
     * @static
     * @param string        $message    The http to test for validity
     * @param array|null    $matches    The named capturing groups from the match
     * @return bool
     * @throws \InvalidArgumentException
     */
    public static function validate($message, &$matches = null)
    {
        if (!parent::validate($message, $matches)) {
            return false;
        }

        if ($matches['request'] == '' or $matches['response'] != '') {
            return false;
        }

        return true;
    }

    /**
     * Method setter.
     *
     * @param $method
     * @return Request
     * @throws \InvalidArgumentException
     */
    protected function setMethod($method)
    {
        if (!Method::isValid($method)) {
            throw new InvalidArgumentException("{$method} is not a valid HTTP method.");
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Method getter.
     *
     * @return string
     */
    protected function getMethod()
    {
        return $this->method;
    }

    /**
     * Uri setter.
     *
     * @param \axelitus\Acre\Net\Uri\Uri $uri
     * @return Request
     */
    protected function setUri($uri)
    {
        if (is_string($uri)) {
            $uri = Uri::forge($uri);
        } else if (!is_object($uri) and $uri instanceof Uri) {
            throw new \InvalidArgumentException("The uri must be a string or an instance of Uri.");
        }

        $this->uri = $uri;

        return $this;
    }

    /**
     * Uri getter.
     *
     * @return \axelitus\Acre\Net\Uri\Uri|null
     */
    protected function getUri()
    {
        return $this->uri;
    }

    /**
     * Gets the messages start line.
     *
     * @return string
     */
    protected function startLine()
    {
        if ($this->uri->authority->host == '') {
            $startLine = sprintf("%s %s HTTP/%s\r\n", $this->method, $this->uri, $this->version);
        } else {
            $startLine = sprintf("%s %s HTTP/%s\r\n", $this->method, ((count($this->uri->path) == 0) ? "/" : $this->uri->path), $this->version);
            $startLine .= sprintf("Host: %s\r\n", $this->uri->authority->host);
        }

        return $startLine;
    }
}
