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
abstract class Transport_Socket extends Transport
{
    protected $socket = null;

    protected $options = array();

    const SOCKET_TIMEOUT = 'socket_timeout';

    const SOCKET_BUFFER_SIZE = 'socket_buffer_size';

    /**
     * @static
     * @var array   The default cURL options to be used
     */
    protected static $default_options = array(
        self::SOCKET_TIMEOUT => 10,
        self::SOCKET_BUFFER_SIZE => 1024
    );
}
