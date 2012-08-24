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
class Request
{
    protected $_method = Method::GET;
    protected $_uri = null;

    protected function setMethod($method)
    {
        if (!Method::isValid($method)) {
            throw new InvalidArgumentException("{$method} is not a valid HTTP method.");
        }

        $this->_method = $method;

        return $this;
    }

    protected function getMethod()
    {
        return $this->_method;
    }

    protected function setUri(Uri $uri)
    {
        $this->_uri = $uri;

        return $this;
    }

    protected function getUri()
    {
        return $this->_uri;
    }

    protected function startLine()
    {
        $startLine = sprintf("%s %s HTTP/%s\r\n", $this->_method, $this->_uri, $this->_version);

        return $startLine;
    }
}
