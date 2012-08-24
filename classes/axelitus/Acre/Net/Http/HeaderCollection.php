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
use OutOfBoundsException;
use Iterator;
use ArrayAccess;

/**
 * Requires axelitus\Acre\Common package
 */
use axelitus\Acre\Common\Str as Str;

/**
 * HeaderCollection Class
 *
 * @see         http://www.ietf.org/rfc/rfc2616.txt     IETF RFC2616 Hypertext Transfer Protocol -- HTTP/1.1
 * @see         http://en.wikipedia.org/wiki/List_of_HTTP_header_fields     List of HTTP header fields
 * @package     axelitus\Acre\Net\Http
 * @category    Net\Http
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class HeaderCollection implements Iterator, ArrayAccess
{
    protected $_headers = array();

    public function __construct($headers = array())
    {
        foreach ($headers as $header => $value) {
            if (is_numeric($header) or !is_string($header)) {
                throw new InvalidArgumentException("The header {$header} is not a valid header.");
            }

            $this->setHeader($header, $value);
        }
    }

    public function __set($header, $value)
    {
        $header = Str::separated($header, 'ucfirst', '-');
        $this->setHeader($header, $value);
    }

    public function __get($header)
    {
        $header = Str::separated($header, 'ucfirst', '-');
        return $this->getHeader($header);
    }

    public function setHeader($header, $value, $append = false)
    {
        $value = static::nullToString($value);
        if (!$append or !array_key_exists($header, $this->_headers)) {
            $this->_headers[$header] = is_array($value) ? array_values($value) : array($value);
        } else {
            array_push($this->headers[$header], is_array($value) ? array_values($value) : $value);
        }

        return $this;
    }

    public function getHeader($header)
    {
        if (!array_key_exists($header, $this->_headers)) {
            return null;
        }

        $header = current($this->_headers);
        return static::flattenHeader($header);
    }

    public function removeHeader($header, $index = null)
    {
        if ($index === null or count($this->_headers[$header]) == 0) {
            unset($this->_headers[$header]);
        } elseif (is_numeric($index) and array_key_exists($header, $this->_headers)) {
            if (!array_key_exists($index, $this->_headers[$header])) {
                throw new OutOfBoundsException("The \$index value {$index} does not exists.");
            }

            unset($this->_headers[$header][$index]);
            $this->_headers[$header] = array_values($this->_headers[$header]);
        }
    }

    protected static function nullToString($value)
    {
        if ($value === null) {
            return '';
        } elseif (is_array($value)) {
            foreach ($value as &$item) {
                $item = static::nullToString($item);
            }
        }

        return $value;
    }

    protected static function flattenHeader($header)
    {
        return (is_array($header) and count($header) == 1) ? $header[0] : $header;
    }

    //<editor-fold desc="Implements Iterator">
    public function current()
    {
        $header = current($this->_headers);
        return static::flattenHeader($header);
    }

    public function key()
    {
        $header = key($this->_headers);
        return static::flattenHeader($header);
    }

    public function next()
    {
        $header = next($this->_headers);
        return static::flattenHeader($header);
    }

    public function rewind()
    {
        $header = reset($this->_headers);
        return static::flattenHeader($header);
    }

    public function valid()
    {
        if (empty($this->_headers) or current($this->_headers) === false) {
            return false;
        }

        return true;
    }

    //</editor-fold>

    //<editor-fold desc="Implements ArrayAccess">
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_headers);
    }

    public function offsetGet($offset)
    {
        return array_key_exists($offset, $this->_headers) ? $this->_headers[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->setHeader($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->removeHeader($offset);
    }
    //</editor-fold>
}
