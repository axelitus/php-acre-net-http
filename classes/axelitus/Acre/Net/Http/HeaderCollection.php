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
use IteratorAggregate;
use ArrayIterator;

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
class HeaderCollection implements Countable, ArrayAccess, IteratorAggregate
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
        $this->load($headers);
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
     * The header labels must be given camelCased and they will get separated by a dash '-'.
     * Examples:
     * 'contentType' will become 'Content-Type'
     * 'contentMD5' will become 'Content-MD5'
     *
     * @param string    $label      The header label camelCased
     * @param mixed     $value      The header's value
     */
    public function __set($label, $value)
    {
        $label = Str::separated($label, 'ucfirst', '-');
        $this->set($label, $value);
    }

    /**
     * Magic getter to get headers like echo $headers->contentType;
     * The header labels must be given camelCased and they will get separated by a dash '-'.
     * Examples:
     * 'contentType' will become 'Content-Type'
     * 'contentMD5' will become 'Content-MD5'
     *
     * @param string    $label      The header label camelCased
     * @return mixed    The header's value
     */
    public function __get($label)
    {
        $label = Str::separated($label, 'ucfirst', '-');
        return $this->get($label);
    }

    /**
     * Sets the header with the given value. By default it replaces the existing header but multiple can be appended
     * using the $append parameter. The label of the header must be the actual dash-separated header label.
     * Examples:
     * 'Content-Type', 'Content-MD5', etc.
     *
     * @param string    $label      The header's label name
     * @param mixed     $value      The header's value
     * @param bool      $append     Whether to append instead of replace
     * @return HeaderCollection     This instance for chaining
     */
    public function set($label, $value, $append = false)
    {
        if (!is_string($label) or $label == '' or is_numeric($label)) {
            throw new InvalidArgumentException("The \$label parameter must be a non-empty, non-numeric string.");
        }

        $label = static::cleanLabel($label);
        $value = static::cleanValue($value);

        if (!$append or !$this->has($label)) {
            $this->_headers[$label] = is_array($value) ? $value : array($value);
        } else {
            array_push($this->_headers[$label], $value);
        }

        return $this;
    }

    /**
     * Gets the header with the given name. The name of the header must be the actual dash-separated header label:
     * 'Content-Type', 'Content-MD5', etc.
     *
     * @param string    $header     The header's label name
     * @param bool      $asStrList  Whether to return the header value as a char separated list
     * @param
     * @return mixed    The header's value
     */
    public function get($label, $asStrList = false, $separator = ',')
    {
        if (!$this->has($label)) {
            throw new OutOfBoundsException(sprintf("Header %s does not exist.", $label));
        }

        $value = $header = static::flatten($this->_headers[$label]);
        if (is_array($header) and $asStrList) {
            $value = '';
            foreach ($header as $val) {
                $value .= sprintf("%s, ", $val);
            }
            $value = Str::sub($value, 0, -2);
        }

        return $value;
    }

    /**
     * Removes the header completely (all entries) or a specific entry of the header (for multi-entry headers).
     *
     * @param string    $label      The header label name
     * @param null|int  $index      The sub-entry for multi-entry headers
     * @throws \OutOfBoundsException
     */
    public function remove($label, $index = null)
    {
        if ($this->has($label)) {
            if ($index === null or count($this->_headers[$label]) == 0) {
                unset($this->_headers[$label]);
            } else {
                if (!$this->hasHeaderEntry($label, $index)) {
                    throw new OutOfBoundsException("The \$index value {$index} for header {$label} does not exists.");
                }

                unset($this->_headers[$label][$index]);

                // Re-index header entries array
                $this->_headers[$label] = array_values($this->_headers[$label]);
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
    public function has($label)
    {
        if (!is_string($label) or $label == '' or is_numeric($label)) {
            throw new InvalidArgumentException("The \$label parameter must be a non-empty, non-numeric string.");
        }

        $label = static::cleanLabel($label);
        return array_key_exists($label, $this->_headers);
    }

    /**
     * Verifies if the collection has the given header and the header has the given indexed entry.
     *
     * @param string    $label      The header label name
     * @param int       $index      The header's entry index
     * @return bool
     * @throws \OutOfBoundsException
     */
    public function hasEntry($label, $index)
    {
        return ($this->has($label) and array_key_exists($index, $this->_headers[$label]));
    }

    /**
     * Loads a new headers array. It replaces the existing headers. The headers will be appended if multiple
     * same-labeled headers are found.
     *
     * @see add
     * @param array $headers    The new associative array of headers
     */
    public function load(array $headers)
    {
        $this->_headers = array();
        $this->add($headers);
    }

    /**
     * Adds the given array to the existing headers.
     *
     * @param array $headers    The new header values as an associative array
     * @param bool  $append     Whether to append the headers instead of replacing them
     */
    public function add(array $headers, $append = true)
    {
        foreach ($headers as $header => $value) {
            if (!is_string($header) or $header == '' or is_numeric($header)) {
                throw new InvalidArgumentException("The header {$header} is not a valid header.");
            }

            $this->set($header, $value, $append);
        }
    }

    /**
     * Cleans a header label.
     *
     * @static
     * @param string   $label      The label to clean
     * @return string   The cleaned value
     */
    public static function cleanLabel($label)
    {
        // Sanitize the label
        $label = preg_replace('/[^a-zA-Z0-9_-]/', '', $label);

        // Normalize (each word in caps)
        $label = Str::ucwords(Str::lower(Str::replace($label, array('_', '-'), ' ')));

        return Str::replace($label, ' ', '-');
    }

    /**
     * Cleans a header value.
     *
     * @static
     * @param mixed     $value      The value to clean
     * @return mixed    The cleaned value
     */
    public static function cleanValue($value)
    {
        if ($value === null) {
            return '';
        } elseif (is_array($value)) {
            foreach ($value as &$item) {
                $item = static::cleanValue($item);
            }
        } elseif (is_string($value)) {
            $value = Str::replace($value, array("\r", "\n"), '');
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
    protected static function flatten(array $header)
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
        return $this->has($offset);
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
        return $this->get($offset);
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
        $this->set($offset, $value);
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
        $this->remove($offset);
    }

    //</editor-fold>

    //<editor-fold desc="Implements IteratorAggregate">
    /**
     * Implements IteratorAggregate Interface
     *
     * @see     http://www.php.net/manual/en/class.iteratoraggregate.php     The IteratorAggregate interface
     * @return  Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_headers);
    }

    //</editor-fold>

    /**
     * Builds a valid Headers string.
     *
     * @param bool   $asStrList     Whether multi-valued headers will be presented as a char-separated list
     *                              instead of appearing multiple times.
     * @param string $separator     The separator to use for the char-separated list
     * @return string   The Headers string
     */
    public function build($asStrList = true, $separator = ',')
    {
        $headers = '';
        if ($asStrList) {
            foreach (array_keys($this->_headers) as $label) {
                $headers .= sprintf("%s: %s\r\n", $label, $this->get($label, true, $separator));
            }
        } else {
            foreach ($this->_headers as $label => $value) {
                if (is_array($value)) {
                    foreach ($value as $entry) {
                        $headers .= sprintf("%s: %s\r\n", $label, $entry);
                    }
                } else {
                    $headers .= sprintf("%s: %s\r\n", $label, $value);
                }
            }
        }

        return trim($headers, "\r\n");
    }

    /**
     * The toString magic function to get a string representation of the object.
     *
     * @return string   The string representation of this object
     */
    public function __toString()
    {
        return $this->build();
    }

}
