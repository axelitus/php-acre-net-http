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
    protected $name = 'Acre';

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

    /**
     * Sends a request using the specified transport
     *
     * @param Request       $request
     * @param null|string   $transport
     * @return Response     The received response
     */
    public function send(Request $request, $transport = null)
    {
        $this->executeHook(static::HOOK_BEFORE_REQUEST_SEND, &$request, &$transport);

        $response = null;
        if ($transport === null) {
            $response = $this->default_transport->send($request);
        } else {
            // TODO: get the wanted transport if exists, if not throw an exception then use it to send the request
        }

        $this->executeHook(static::HOOK_AFTER_RESPONSE_RECEIVED, &$response, &$transport);

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
