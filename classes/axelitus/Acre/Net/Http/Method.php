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
 * Method Class
 *
 * @see     http://www.ietf.org/rfc/rfc2616.txt     IETF RFC2616 Hypertext Transfer Protocol -- HTTP/1.1 (Sections 5.1.1 and 9)
 * @package     axelitus\Acre\Net\Http
 * @category    Net\Http
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Method
{
    /**
     * @var string  Represents a request for information about the communication options available.
     **/
    const OPTIONS = 'OPTIONS';
    
    /**
     * @var string  Means retrieve whatever information is identified by the Request-URI.
     **/
    const GET = 'GET';
    
    /**
     * @var string  Is identical to GET except that the server MUST NOT return a message-body in the response.
     **/
    const HEAD = 'HEAD';
    
    /**
     * @var string  Is used to request that the origin server accept the enclosed entity for the resource identified by the Request-URI.
     **/
    const POST = 'POST';
    
    /**
     * @var string  Requests that the enclosed entity be stored under the supplied Request-URI.
     **/
    const PUT = 'PUT';          
    
    /**
     * @var string  Requests that the origin server delete the resource identified by the Request-URI.
     **/
    const DELETE = 'DELETE';

    /**
     * @var string  Is used to invoke a remote, application-layer loop-back of the request message.
     **/
    const TRACE = 'TRACE';

    /**
     * @var string  This specification reserves the method name CONNECT for use with a proxy that can dynamically switch to being a tunnel.
     **/
    const CONNECT = 'CONNECT';  // 
}
