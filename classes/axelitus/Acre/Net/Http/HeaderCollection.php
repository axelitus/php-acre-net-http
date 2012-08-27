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
use Countable;
use ArrayAccess;
use Iterator;

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
class HeaderCollection implements Countable, Iterator, ArrayAccess
{
    /**
     * @var array   The headers in the collection
     */
    protected $_headers = array();

    /**
     * Protected constructor to prevent instantiation outside this class.
     *
     * @param array $headers    The associative array containing the initial headers
     */
    protected function __construct(array $headers = array())
    {
        foreach ($headers as $header => $value) {
            if (!is_string($header) or $header == '' or is_numeric($header)) {
                throw new InvalidArgumentException("The header {$header} is not a valid header.");
            }

            $this->setHeader($header, $value);
        }
    }

    /**
     * Forges a new instance of HeaderCollection.
     *
     * @static
     * @param array $headers    The associative array containing the initial headers
     * @return HeaderCollection
     */
    public static function forge(array $headers = array())
    {
        return new static($headers);
    }

    /**
     * Magic setter to set headers like $headers->contentType = 'utf-8;
     * The header name must be given camelCased and it will get separated by a dash '-':
     * 'contentType' will become 'Content-Type'
     * 'contentMD5' will become 'Content-MD5'
     *
     * @param string    $header     The header name (label) camelCased
     * @param mixed     $value      The header's value
     */
    public function __set($header, $value)
    {
        $header = Str::separated($header, 'ucfirst', '-');
        $this->setHeader($header, $value);
    }

    /**
     * Magic getter to get headers like echo $headers->contentType;
     * The header name must be given camelCased and it will get separated by a dash '-':
     * 'contentType' will become 'Content-Type'
     * 'contentMD5' will become 'Content-MD5'
     *
     * @param string    $header     The header name (label) camelCased
     * @return mixed    The header's value
     */
    public function __get($header)
    {
        $header = Str::separated($header, 'ucfirst', '-');
        return $this->getHeader($header);
    }

    /**
     * Sets the header with the given value. By default it replaces the existing header, but multiple can be appended
     * using the $append parameter. The name of the header must be the actual dash-separated header label:
     * 'Content-Type', 'Content-MD5', etc.
     *
     * @param string    $header      The header's label name
     * @param mixed     $value       The header's value
     * @param bool      $append      Whether to append instead of replace
     * @return HeaderCollection     This instance for chaining
     */
    public function setHeader($header, $value, $append = false)
    {
        if (!is_string($header) or $header == '' or is_numeric($header)) {
            throw new InvalidArgumentException("The \$header parameter must be a non-empty, non-numeric string.");
        }

        $value = static::nullToString($value);
        if (!$append or !$this->hasHeader($header)) {
            $this->_headers[$header] = is_array($value) ? array_values($value) : array($value);
        } else {
            array_push($this->headers[$header], is_array($value) ? array_values($value) : $value);
        }

        return $this;
    }

    /**
     * Gets the header with the given name. The name of the header must be the actual dash-separated header label:
     * 'Content-Type', 'Content-MD5', etc.
     *
     * @param string    $header     The header's label name
     * @return mixed    The header's value
     */
    public function getHeader($header)
    {
        if (!$this->hasHeader($header)) {
            return null;
        }

        return static::flattenHeaderValue($this->_headers[$header]);
    }

    /**
     * Removes the header completely (all entries) or a specific entry of the header (for multi-entry headers).
     *
     * @param string    $header     The header label name
     * @param null|int  $index      The sub-entry for multi-entry headers
     * @throws \OutOfBoundsException
     */
    public function removeHeader($header, $index = null)
    {
        if ($this->hasHeader($header)) {
            if ($index === null or count($this->_headers[$header]) == 0) {
                unset($this->_headers[$header]);
            } else {
                if (!$this->hasHeaderEntry($header, $index)) {
                    throw new OutOfBoundsException("The \$index value {$index} for header {$header} does not exists.");
                }

                unset($this->_headers[$header][$index]);

                // Re-index header entries array
                $this->_headers[$header] = array_values($this->_headers[$header]);
            }
        }
    }

    /**
     * Verifies if the collection has the given header.
     *
     * @param string    $header     The header label name
     * @return bool     Whether the collection has the header
     * @throws \InvalidArgumentException
     */
    public function hasHeader($header)
    {
        if (!is_string($header) or $header == '' or is_numeric($header)) {
            throw new InvalidArgumentException("The \$header parameter must be a non-empty, non-numeric string.");
        }

        return array_key_exists($header, $this->_headers);
    }

    /**
     * Verifies if the collection has the given header and the header has the given indexed entry.
     *
     * @param string    $header     The header label name
     * @param int       $index      The header's entry index
     * @return bool
     * @throws \OutOfBoundsException
     */
    public function hasHeaderEntry($header, $index)
    {
        if (!$this->hasHeader($header)) {
            throw new OutOfBoundsException("The header {$header} does not exist.");
        }

        return array_key_exists($index, $this->_headers[$header]);
    }

    /**
     * Sets the headers array. It replaces the entire array.
     *
     * @param array $headers    The new associative array of headers
     * @param bool  $append     Whether to append the headers instead of replacing them
     */
    public function setHeaders(array $headers, $append = true)
    {
        $this->_headers = array();
        $this->addHeaders($headers);
    }

    /**
     * Adds the given array to the existing headers.
     *
     * @param array $headers    The new header values as an associative array
     * @param bool  $append     Whether to append the headers instead of replacing them
     */
    public function addHeaders(array $headers, $append = true)
    {
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value, $append);
        }
    }

    /**
     * Converts the null values to empty strings, other values are returned untouched.
     * If array is given the conversion will be done in all elements recursively.
     *
     * @static
     * @param $value    The value to convert
     * @return mixed    The converted value
     */
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

    /**
     * Flattens the header value if it is an array and contains only one entry.
     *
     * @static
     * @param mixed $header     The header to flatten
     * @return mixed    The flattened header value
     */
    protected static function flattenHeaderValue(array $header)
    {
        return (is_array($header) and count($header) == 1) ? $header[0] : $header;
    }

    //<editor-fold desc="Countable Interface">
    /**
     * Implements Countable interface
     * This method returns only the count of the different headers. It does not include the sub-count
     * of the multi-entry headers.
     *
     * @see     http://fr.php.net/manual/en/class.countable.php     The Countable interface
     * @return  int
     */
    public function count()
    {
        return count($this->_headers);
    }

    //</editor-fold>

    //<editor-fold desc="Implements ArrayAccess">
    /**
     * Implements ArrayAccess Interface
     *
     * @see     http://php.net/manual/en/class.arrayaccess.php      The ArrayAccess interface
     * @param   mixed   $offset
     * @return  bool
     */
    public function offsetExists($offset)
    {
        return $this->hasHeader($offset);
    }

    /**
     * Implements ArrayAccess Interface
     *
     * @see     http://php.net/manual/en/class.arrayaccess.php      The ArrayAccess interface
     * @param   mixed   $offset
     * @return  mixed
     */
    public function offsetGet($offset)
    {
        return $this->hasHeader($offset) ? $this->_headers[$offset] : null;
    }

    /**
     * Implements ArrayAccess Interface
     *
     * @see     http://php.net/manual/en/class.arrayaccess.php      The ArrayAccess interface
     * @param   mixed   $offset
     * @param   mixed   $value
     * @return  void
     */
    public function offsetSet($offset, $value)
    {
        $this->setHeader($offset, $value);
    }

    /**
     * Implements ArrayAccess Interface
     *
     * @see     http://php.net/manual/en/class.arrayaccess.php      The ArrayAccess interface
     * @param   mixed   $offset
     * @return  void
     */
    public function offsetUnset($offset)
    {
        $this->removeHeader($offset);
    }
    //</editor-fold>

    //<editor-fold desc="Implements Iterator">
    /**
     * Implements Iterator Interface
     *
     * @see     http://www.php.net/manual/en/class.iterator.php     The Iterator interface
     * @return  mixed
     */
    public function current()
    {
        $header = current($this->_headers);
        return static::flattenHeaderValue($header);
    }

    /**
     * Implements Iterator Interface
     *
     * @see     http://www.php.net/manual/en/class.iterator.php     The Iterator interface
     * @return  int|string
     */
    public function key()
    {
        return key($this->_headers);
    }

    /**
     * Implements Iterator Interface
     *
     * @see     http://www.php.net/manual/en/class.iterator.php     The Iterator interface
     * @return  void
     */
    public function next()
    {
        next($this->_headers);
    }

    /**
     * Implements Iterator Interface
     *
     * @see     http://www.php.net/manual/en/class.iterator.php     The Iterator interface
     * @return  void
     */
    public function rewind()
    {
        reset($this->_headers);
    }

    /**
     * Implements Iterator Interface
     *
     * @see     http://www.php.net/manual/en/class.iterator.php     The Iterator interface
     * @return  bool
     */
    public function valid()
    {
        return !is_null($this->key());
    }

    //</editor-fold>
}
