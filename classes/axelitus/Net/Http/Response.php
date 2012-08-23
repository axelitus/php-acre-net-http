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

use InvalidArgumentException;
use axelitus\Acre\Net\Http\Method as Method;
use axelitus\Acre\Net\Http\Status as Status;

/**
 * Response Class
 *
 * @package     axelitus\Acre\Net\Http
 * @category    Net\Http
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Response extends MagicObject
{
    protected $_charset = 'utf-8';
    protected $_status = Status::_OK;
    protected $_headers = null;
    protected $_contentType = 'text/html';
    protected $_content = '';

    protected function __construct($options)
    {
        $options['headers'] = (!isset($options['headers']) or is_array($options['headers']))
            ? new HeaderCollection($options['header'])
            : ($options['headers'] instanceof HeaderCollection) ? $options['headers'] : new HeaderCollection();

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public static function forge($options = null)
    {
        $options = ($options == null) ? array() : $options;
        if (is_array($options)) {
            return new static($options);
        } elseif (is_string($options)) {
            return new static(array("content" => $options));
        } else {
            throw new InvalidArgumentException("The \$options parameter must be an array or a string.");
        }
    }

    protected function setCharset($charset)
    {
        $this->_charset = $charset;

        return $this;
    }

    protected function getCharset()
    {
        return $this->_charset;
    }

    protected function setStatus($status)
    {
        if (!Status::isValid($status)) {
            throw new InvalidArgumentException("{$status} is not a valid HTTP status code.");
        }

        $this->_status = $status;

        return $this;
    }

    protected function getStatus()
    {
        return $this->_status;
    }

    protected function setContentType($contentType)
    {
        $this->_contentType = $contentType;

        return $this;
    }

    protected function getContentType()
    {
        return $this->_contentType;
    }

    protected function setContent($content)
    {
        $this->_content = $content;
    }

    protected function getContent()
    {
        return $this->_content;
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
}
