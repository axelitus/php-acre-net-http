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

use axelitus\Acre\Common\Str as Str;

/**
 * UserAgent Class
 *
 * @package     axelitus\Acre\Net\Http
 * @category    Net\Http
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class UserAgent
{
    /**
     * @var string  The before request send hook identifier
     */
    const HOOK_BEFORE_REQUEST_SEND = 'before_request_send';

    /**
     * @var string  The after response received hook identifier
     */
    const HOOK_AFTER_RESPONSE_RECEIVED = 'after_response_received';

    /**
     * @var string  The user agent name
     */
    protected $name = 'Acre User Agent';

    /**
     * @var string  The user agent version
     */
    protected $version = '0.1';

    /**
     * @var string  The user agent specs
     */
    protected $specs = '';

    /**
     * @var array   Contains the loaded transports
     */
    protected $transports = array();

    /**
     * @var Transport   The default transport to use
     */
    protected $default_transport = null;

    /**
     * @var array Contains the hooks for request/response manipulation in between processing
     */
    protected $hooks = array(
        self::HOOK_BEFORE_REQUEST_SEND => null,
        self::HOOK_AFTER_RESPONSE_RECEIVED => null
    );

    /**
     * Forges a new UserAgent object
     *
     * @return UserAgent
     */
    public static function forge()
    {
        return new static();
    }

    /**
     * Creates a new UserAgent object
     */
    protected function __construct()
    {
    }

    public function loadTransport($name, $transport_type, $options = null, $use_defaults = true, $replace = false, $set_as_default = false)
    {
        if ($this->hasTransport($name) and !$replace) {
            throw new \RuntimeException("There is already a transport with tht name loaded.");
        }

        if (is_object($transport_type) and $transport_type instanceof Transport) {
            // If a Transport was given, change the user agent to this instance
            $transport_type->setUserAgent($this);
            $this->transports[$name] = $transport_type;
        } else {
            if (!is_string($transport_type) or $transport_type == '') {
                throw new \InvalidArgumentException("The \$transport_type must be a Transport instance or a non-empty string.");
            }

            $class = __NAMESPACE__.'\Transport_'.$transport_type;
            if (!class_exists($class)) {
                throw new \RuntimeException("The Transport {$class} does not exist.");
            }

            $this->transports[$name] = $class::forge($this, $options, $use_defaults);

            // Verify if the transport was correctly loaded
            if (!is_object($this->transports[$name]) or !($this->transports[$name] instanceof Transport)) {
                throw new \RuntimeException("The Transport could not be loaded.");
            }
        }

        $transport = $this->getTransport($name);
        if ($transport === null) {
            throw new \RuntimeException("An error occurred and the Transport was not correctly loaded.");
        }

        $this->default_transport = ($this->default_transport === null or $set_as_default) ? $transport : $this->default_transport;

        return $transport;
    }

    public function hasTransport($name)
    {
        if (!is_string($name) or $name == '') {
            throw new \InvalidArgumentException("The \$name param must be a non-empty string.");
        }

        if (array_key_exists($name, $this->transports)) {
            return true;
        }

        return false;
    }

    public function getTransport($name, $default = null)
    {
        if ($this->hasTransport($name)) {
            return $this->transports[$name];
        }

        return $default;
    }

    public function removeTransport($name)
    {
        $transport = null;
        if ($this->hasTransport($name)) {
            $transport = $this->transports[$name];
            unset($this->transports[$name]);

            if ($this->default_transport == $transport) {
                $this->default_transport = (($default = reset($this->transports)) === false) ? null : $default;
            }
        }

        return $transport;
    }

    public function getTransports($as_objects = false)
    {
        if($as_objects) {
            return $this->transports;
        } else {
            $transports = array();
            foreach($this->transports as $name=>$obj) {
                $transports[$name] = Str::replace(get_class($obj), __NAMESPACE__.'\Transport_', '');
            }

            return $transports;
        }
    }

    /**
     * Sends a request using the specified transport
     *
     * @param Request       $request
     * @param null|string   $transport
     * @return Response     The received response
     */
    public function send(Request $request, $transport = null)
    {
        $this->executeHook(static::HOOK_BEFORE_REQUEST_SEND, $request, $transport);

        $response = null;
        if ($transport === null) {
            $request->headers->userAgent = (string)$this;
            $response = $this->default_transport->send($request);
        } else {
            // TODO: get the wanted transport if exists, if not throw an exception then use it to send the request
        }

        $this->executeHook(static::HOOK_AFTER_RESPONSE_RECEIVED, $response, $transport);

        return $response;
    }

    /**
     * Executes a defined hook
     *
     * @param string    $hook       The hook to execute
     * @param Message   $message    The message specific for that hook (request or response)
     * @param string    $transport  The transport to be used or has been used
     */
    public function executeHook($hook, Message &$message, &$transport)
    {
        if (isset($this->hooks[$hook]) and is_callable($this->hooks[$hook])) {
            call_user_func_array($this->hooks[$hook], array(&$message, &$transport));
        }
    }

    /**
     * Gets a string representation, in this case the user agent string
     *
     * @return string
     */
    public function __toString()
    {
        $user_agent = sprintf("%s/%s %s", $this->name, $this->version, $this->specs);

        return $user_agent;
    }
}
