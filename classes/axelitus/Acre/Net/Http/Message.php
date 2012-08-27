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

/**
 * Requires axelitus\Acre\Common package
 */
use axelitus\Acre\Common\Magic_Object as MagicObject;
use axelitus\Acre\Common\Str as Str;

use InvalidArgumentException;

/**
 * Message Class
 *
 * Represents an abstract HTTP message. This type is the base class for Response and Request classes.
 *
 * @see         http://www.ietf.org/rfc/rfc2616.txt     IETF RFC2616 Hypertext Transfer Protocol -- HTTP/1.1
 * @package     axelitus\Acre\Net\Http
 * @category    Net\Http
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
abstract class Message extends MagicObject
{
    /**
     * @var string  The HTTP protocol version (1.0 or 1.1) of the message
     */
    protected $_version = '1.1';

    /**
     * @var HeaderCollection    The message headers
     */
    protected $_headers = null;

    /**
     * @var string      The body (contents) of the message
     */
    protected $_body = '';

    /**
     * Protected constructor to prevent instantiation outside this class.
     *
     * @param array  $options   An array containing the initial message options
     */
    protected function __construct(array $options)
    {
        $options['headers'] = !isset($options['headers']) ? array() : $options['headers'];
        if (!$options['headers'] instanceof HeaderCollection) {
            $options['headers'] = is_array($options['headers']) ? HeaderCollection::forge($options['headers'])
                : new HeaderCollection();
        }

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Forges a new Message instance. This forges an instance of each derived type class correctly.
     *
     * @static
     * @param null|string|array $options    The message options or the body contents as a string
     * @return Message
     * @throws \InvalidArgumentException
     */
    public static function forge($options = null)
    {
        $options = ($options === null) ? array() : $options;
        if (is_array($options)) {
            return new static($options);
        } elseif (is_string($options)) {
            return new static(array("body" => $options));
        } else {
            throw new InvalidArgumentException("The \$options parameter must be an array or a string.");
        }
    }

    /**
     * Version setter.
     *
     * @param string $version   The protocol version
     * @return Message      This instance for chaining
     */
    protected function setVersion($version)
    {
        $this->_version = $version;

        return $this;
    }

    /**
     * Version getter.
     *
     * @return string   The protocol version
     */
    protected function getVersion()
    {
        return $this->_version;
    }

    /**
     * Headers setter.
     *
     * @param HeaderCollection $headers     The message headers
     * @return Message      This instance for chaining
     */
    protected function setHeaders(HeaderCollection $headers)
    {
        $this->_headers = ($headers === null)? HeaderCollection::forge() : $headers;

        return $this;
    }

    /**
     * Headers getter.
     *
     * @return HeaderCollection
     */
    protected function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Body setter.
     *
     * @param string    $body       The message contents body
     * @return Message      This instance for chaining
     */
    protected function setBody($body)
    {
        $this->_body = $body;

        return $this;
    }

    /**
     * Body getter.
     *
     * @return string       The message's contents body
     */
    protected function getBody()
    {
        return $this->_body;
    }

    /**
     * Calculates the contents body length.
     *
     * @param bool $setHeader   Whether to set the header in the message
     * @return int      The contents body length
     */
    public function getBodyLength($setHeader = false)
    {
        $length = Str::length($this->_body);

        if ($setHeader) {
            $this->headers->contentLength = $length;
        }

        return $length;
    }

    /**
     * The Message start line. This differs for Response and Request messages.
     *
     * @abstract
     * @return string
     */
    protected abstract function startLine();

    /**
     * The toString magic function to get a string representation of the object.
     *
     * @return string   The string representation of this object
     */
    public function __toString()
    {
        $message = $this->startLine();
        foreach ($this->_headers as $fieldName => $fieldValue) {
            if (is_array($fieldValue)) {
                foreach ($fieldValue as $value) {
                    $message .= sprintf("%s: %s\r\n", $fieldName, $value);
                }
            } else {
                $message .= sprintf("%s: %s\r\n", $fieldName, $fieldValue);
            }
        }

        if ($this->_body != '') {
            $message .= sprintf("\r\n%s", $this->_body);
        }

        return $message;
    }
}
