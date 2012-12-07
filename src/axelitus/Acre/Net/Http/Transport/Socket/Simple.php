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
 * Transport_FSocket Class
 *
 * @package     axelitus\Acre\Net\Http
 * @category    Net\Http
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Transport_Socket_Simple extends Transport_Socket
{
    /**
     * Verifies if sockets are available through fsockopen
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
     * Creates a new Simple Socket Transport
     *
     * @static
     * @param \ArrayAccess $options         The socket options to use/merge
     * @param bool         $use_defaults    Whether to use the default options as base
     * @return Transport_Socket_Simple   The new Simple Socket Transport object
     */
    public static function create(\ArrayAccess $socket_options = null, $use_defaults = true)
    {
        $default = ($use_defaults) ? static::$default_options : array();
        $options = Arr::forge($default);
        $options->merge((($socket_options === null) ? array() : $socket_options));

        $transport = new static($options);

        return $transport;
    }

    /**
     * Creates a new instance of Simple Socket Transport class.
     *
     * @param UserAgent    $user_agent      The user agent
     * @param \ArrayAccess $cURL_options    The socket options
     * @throws \Exception
     */
    protected function __construct(\ArrayAccess $options)
    {
    }

    /**
     * Sends a Request using sockets as transport through fsockopen
     *
     * @param Request $request      The request to be sent
     * @return Response             The received response
     * @throws \RuntimeException
     */
    protected function sendRequest(Request $request)
    {
        // Ensure we have a timeout set and a buffer size
        $this->options[self::SOCKET_TIMEOUT] = (!isset($this->options[self::SOCKET_TIMEOUT]) ? static::$default_options[self::SOCKET_TIMEOUT] : $this->options[self::SOCKET_TIMEOUT]);
        $this->options[self::SOCKET_BUFFER_SIZE] = (!isset($this->options[self::SOCKET_BUFFER_SIZE]) ? static::$default_options[self::SOCKET_BUFFER_SIZE] : $this->options[self::SOCKET_BUFFER_SIZE]);

        $this->socket = @fsockopen($request->uri->authority->host, $request->uri->authority->port, $errno, $errstr, $this->options[self::SOCKET_TIMEOUT]);
        if ($this->socket === false or !is_resource($this->socket)) {
            throw new \RuntimeException("The socket could not be opened. The request will not be sent. Error [{$errno}]: {$errstr}.");
        }

        $write = (string)$request;
        fwrite($this->socket, $write."\r\n");

        $response = '';
        while (!feof($this->socket)) {
            $info = stream_get_meta_data($this->socket);
            if ($info['timed_out']) {
                throw new \RuntimeException('The socket timed out.');
            }

            $response .= fread($this->socket, $this->options[self::SOCKET_BUFFER_SIZE]);
        }

        $response = Response::parse($response);

        return $response;
    }
}
