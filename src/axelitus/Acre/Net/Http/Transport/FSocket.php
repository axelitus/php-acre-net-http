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

use axelitus\Acre\Common\Arr as Arr;

/**
 * Transport_Socket Class
 *
 * @package     axelitus\Acre\Net\Http
 * @category    Net\Http
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Transport_FSocket extends Transport
{
    protected $socket = null;

    protected $user_agent = null;

    protected $options = array();

    const BUFFER_SIZE = 1160;

    /**
     * @static
     * @var array   The default cURL options to be used
     */
    protected static $default_options = array(
        CONNECTION_TIMEOUT => 10,
    );

    /**
     * Verifies if sockets are available
     *
     * @static
     * @return bool     Whether cURL is available
     */
    public static function isAvailable()
        {
            if (function_exists('fsockopen')) {
                return true;
            }

            return false;
        }

    /**
     * Forges a new Socket Transport
     *
     * @static
     * @param UserAgent    $user_agent      The user agent
     * @param \ArrayAccess $options         The cURL options to use/merge
     * @param bool         $use_defaults    Whether to use the default options as base
     * @return Transport_cURL   The new Socket Transport object
     */
    public static function forge(UserAgent $user_agent, \ArrayAccess $socket_options = null, $use_defaults = true)
    {
        if (!static::isAvailable()) {
            throw new \Exception("Sockets are disabled, cannot use this transport.");
        }

        $default = ($use_defaults) ? static::$default_options : array();
        $options = Arr::forge($default);
        $options->merge((($socket_options === null) ? array() : $socket_options));

        $transport = new static($user_agent, $options);

        return $transport;
    }

    /**
     * Creates a new instance of Socket Transport class.
     *
     * @param UserAgent    $user_agent      The user agent
     * @param \ArrayAccess $cURL_options    The cURL options
     * @throws \Exception
     */
    protected function __construct(UserAgent $user_agent, \ArrayAccess $options)
    {
        $this->user_agent = $user_agent;
    }

    /**
     * Sends a Request using sockets as transport
     *
     * @param Request $request      The request to be sent
     * @return Response             The received response
     * @throws \RuntimeException
     */
    protected function sendRequest(Request $request)
    {
        // Ensure we have a timeout set and a buffersize
        $this->options[CONNECTION_TIMEOUT] = (!isset($this->options[CONNECTION_TIMEOUT])? static::$default_options[CONNECTION_TIMEOUT] : $this->options[CONNECTION_TIMEOUT]);

        $this->socket = @fsockopen($request->uri->authority->host, $request->uri->authority->port, $errno, $errstr, $this->options[CONNECTION_TIMEOUT]);
        if ($this->socket === false or !is_resource($this->socket)) {
            throw new \RuntimeException("The socket could not be opened. The request will not be sent. Error [{$errno}]: {$errstr}.");
        }

        $write = (string)$request;
        fwrite($this->socket, $write);

        $response = '';
        while (!feof($this->socket)) {
            $info = stream_get_meta_data($this->socket);
            if ($info['timed_out']) {
                throw new \RuntimeException('The socket timed out.');
            }

            $response .= fread($this->socket, self::BUFFER_SIZE);
        }

        return Response::parse($response);
    }
}
