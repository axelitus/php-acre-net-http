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
     * Base class for Transports which handle the sending of HTTP Requests.
     * This ensures that the response is a Response object.
     *
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
