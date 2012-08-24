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
    protected $_version = '1.1';
    protected $_headers = null;
    protected $_body = '';

    protected function __construct($options)
    {
        $options['headers'] = !isset($options['headers']) ? array() : $options['headers'];
        if (!$options['headers'] instanceof HeaderCollection) {
            $options['headers'] = is_array($options['headers']) ? new HeaderCollection($options['headers'])
                : new HeaderCollection();
        }

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
    }

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

    protected function setVersion($version)
    {
        $this->_version = $version;

        return $this;
    }

    protected function getVersion()
    {
        return $this->_version;
    }

    protected function setHeaders(HeaderCollection $headers)
    {
        $this->_headers = $headers;

        return $this;
    }

    protected function getHeaders()
    {
        return $this->_headers;
    }

    protected function setBody($body)
    {
        $this->_body = $body;

        return $this;
    }

    protected function getBody()
    {
        return $this->_body;
    }

    public function getBodyLength($setHeader = false)
    {
        $length = Str::length($this->_body);

        if ($setHeader) {
            $this->headers->contentLength = $length;
        }

        return $length;
    }

    protected abstract function startLine();

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
