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
    // Due to newlines being different in Linux and Windows we need to use PCRE (*ANYCRLF)
    // to match them, that is \R escaped special char. The problem with \R is that in
    // character groups it has no special meaning so [^\R] will not match any non-newline character,
    // instead it will simply match any character which is not R.
    // Also the (?J) at the beginning prevents the compiler from complaining about duplicate
    // named groups.
    const REGEX = <<<'REGEX'
/(?J)
^
(?:(?#startline)(?<startline>
  (?#request)(?<request>
    (?#method)(?<method>OPTIONS|GET|HEAD|POST|PUT|DELETE|TRACE|CONNECT)
    (?:\ )+(?:(?#uri)(?<uri>[^\ |\r\n]+))?
    (?:\ )+HTTP\/(?#version)(?<version>\d.\d)
  )
  |(?#response)(?<response>
    HTTP\/(?#version)(?<version>\d.\d)
    (?:\ )+(?#status)(?<status>\d{3})
    (?:\ )+(?#phrase)(?<phrase>[^\r\n]+)
  )
))(?:\r?\n
(?:(?#headers)(?<headers>(?:(?:[^:\r\n]+)(?:\ )*:(?:\ )*(?:[^\r\n]+)\r?\n)*(?:[^:\r\n]+)(?:\ )*:(?:\ )*(?:[^\r\n]+))(?:\r?\n)?
(?:\r?\n\r?\n(?#body)(?<body>[^$]+))?)?)?
$
/x
REGEX;

    /**
     * @var Message type: request
     */
    const TYPE_REQUEST = 'request';

    /**
     * @var Message type: response
     */
    const TYPE_RESPONSE = 'response';

    /**
     * @var Message type: invalid
     */
    const TYPE_INVALID = 'invalid';

    /**
     * @var string  The HTTP protocol version (1.0 or 1.1) of the message
     */
    protected $version = '1.1';

    /**
     * @var HeaderCollection    The message headers
     */
    protected $headers = null;

    /**
     * @var string      The body (contents) of the message
     */
    protected $body = '';

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
                : HeaderCollection::forge();
        }

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Forges a new Message instance. This forges an instance of each derived type class correctly.
     *
     * @static
     * @param string|array $options    The message options or the body contents as a string
     * @return Message
     * @throws \InvalidArgumentException
     */
    public static function forge($options = array())
    {
        if (is_array($options)) {
            return new static($options);
        } elseif (is_string($options)) {
            return new static(array("body" => $options));
        } else {
            throw new InvalidArgumentException("The \$options parameter must be an array or a string.");
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
        if (!is_string($message)) {
            throw new InvalidArgumentException("The \$message parameter must be a string.");
        }

        $valid = (bool)preg_match(static::REGEX, $message, $matches);

        return $valid;
    }

    /**
     * Identifies the message type
     *
     * @param string|Message $message    The message string or object to identify the type
     * @param array|null     $matches    The named capturing groups from the match
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function type($message, &$matches = null)
    {
        if (is_object($message) and $message instanceof Message) {
            // Test for object
            if ($message instanceof Request) {
                return static::TYPE_REQUEST;
            } elseif ($message instanceof Response) {
                return static::TYPE_RESPONSE;
            }

            return static::TYPE_INVALID;
        } elseif (is_string($message)) {
            // Test for string
            if ($message == '') {
                throw new InvalidArgumentException("The \$message string cannot be empty.");
            }

            try {
                if (static::validate($message, $matches)) {
                    if ($matches['request'] != '') {
                        return static::TYPE_REQUEST;
                    } else {
                        return static::TYPE_RESPONSE;
                    }
                } else {
                    return static::TYPE_INVALID;
                }
            } catch (\Exception $ex) {
                return static::TYPE_INVALID;
            }
        }

        return static::TYPE_INVALID;
    }

    /**
     * Parses an HTTP message string into it's valid Request or Response derived classes.
     *
     * @param string $message   The HTTP message string to parse
     * @return Message      The proper HTTP parsed message
     * @throws \RuntimeException
     */
    public static function parse($message)
    {
        switch (static::type($message, $matches)) {
            case static::TYPE_REQUEST:
                $message = Request::forge();
                $message->method = $matches['method'];
                $message->uri = Uri::parse($matches['uri']);
                break;
            case static::TYPE_RESPONSE:
                $message = Response::forge();
                $message->status = $matches['status'];
                break;
            case static::TYPE_INVALID:
                throw new \RuntimeException("The \$message is not a valid HTTP message.");
                break;
        }

        $message->version = $matches['version'];
        $message->headers = HeaderCollection::parse($matches['headers']);
        $message->body = $matches['body'];

        return $message;
    }

    /**
     * Version setter.
     *
     * @param string $version   The protocol version
     * @return Message      This instance for chaining
     */
    protected function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Version getter.
     *
     * @return string   The protocol version
     */
    protected function getVersion()
    {
        return $this->version;
    }

    /**
     * Headers setter.
     *
     * @param HeaderCollection $headers     The message headers
     * @return Message      This instance for chaining
     */
    protected function setHeaders(HeaderCollection $headers)
    {
        $this->headers = ($headers === null) ? HeaderCollection::forge() : $headers;

        return $this;
    }

    /**
     * Headers getter.
     *
     * @return HeaderCollection
     */
    protected function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Body setter.
     *
     * @param string    $body       The message contents body
     * @return Message      This instance for chaining
     */
    protected function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Body getter.
     *
     * @return string       The message's contents body
     */
    protected function getBody()
    {
        return $this->body;
    }

    /**
     * Calculates the contents body length.
     *
     * @param bool $setHeader   Whether to set the header in the message
     * @return int      The contents body length
     */
    public function getBodyLength($setHeader = false)
    {
        $length = Str::length($this->body);

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
    abstract protected function startLine();

    /**
     * The toString magic function to get a string representation of the object.
     *
     * @return string   The string representation of this object
     */
    public function __toString()
    {
        $message = $this->startLine();

        if (!$this->headers->isEmpty()) {
            $message .= sprintf("%s\r\n", $this->headers);
        }

        if ($this->body != '') {
            $message .= sprintf("\r\n%s", $this->body);
        }

        return $message;
    }
}
