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
 * Transport Class
 *
 * @package     axelitus\Acre\Net\Http
 * @category    Net\Http
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
abstract class Transport
{
    /**
     * @var UserAgent|null      The UserAgent object that's communicating
     */
    protected $user_agent = null;

    /**
     * Forges a new Transport object.
     *
     * @static
     * @param UserAgent    $user_agent      The user agent
     * @param \ArrayAccess $options         The transport options
     * @param bool         $use_defaults    Whether to use the default options as base or ignore them
     * @return Transport    The newly forged Transport
     * @throws  \RuntimeException
     */
    public static function forge(UserAgent $user_agent, \ArrayAccess $options = null, $use_defaults = true)
    {
        if (!static::isAvailable()) {
            throw new \RuntimeException("This transport {".get_called_class()."} is not available.");
        }

        $transport = static::create($options, $use_defaults);

        // Attach the user agent to the transport
        $transport->setUserAgent($user_agent);

        return $transport;
    }

    /**
     * Verifies if the transport is available.
     * This function cannot be declared abstract, thus here it returns false every time so you have to override
     * it in the derived class.
     *
     * @static
     * @return bool     Whether the transport is available
     */
    public static function isAvailable() {
        return false;
    }

    /**
     * Creates the actual transport (this function is called from within the forge function).
     * This function cannot be declared abstract, thus here it returns null every time so you have to override
     * it in the derived class.
     *
     * @static
     * @param \ArrayAccess $options             The options to use for the transport
     * @param bool         $use_defaults        Whether to use the default options as a base or ignore them
     * @return Transport    The created transport
     */
    protected static function create(\ArrayAccess $options = null, $use_defaults = true)
    {
        return null;
    }

    /**
     * Attaches a User Agent to the transport.
     *
     * @final
     * @param UserAgent $user_agent
     */
    final public function setUserAgent(UserAgent $user_agent)
    {
        $this->user_agent = $user_agent;
    }

    /**
     * Base class for Transports which handle the sending of HTTP Requests.
     * This ensures that the response is a Response object.
     *
     * @final
     * @param Request $request      The request to be sent
     * @return Response     The response of the sent request
     * @throws \RuntimeException
     */
    final public function send(Request $request)
    {
        $response = $this->sendRequest($request);

        if(!($response instanceof Response)){
            throw new \RuntimeException("The return object of sendRequest() must be a Response object.");
        }

        return $response;
    }

    /**
     * Function that handles the actual sending of the request.
     * This method is not to be accessed directly.
     *
     * @param Request $request      The request to be sent
     * @return Response     The response of the sent request
     */
    abstract protected function sendRequest(Request $request);
}
