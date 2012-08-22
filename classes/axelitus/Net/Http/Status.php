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
 * Status Class
 *
 * @see         http://www.ietf.org/rfc/rfc2616.txt     IETF RFC2616 Hypertext Transfer Protocol -- HTTP/1.1 (Sections 6.1.1 and 10)
 * @package     axelitus\Acre\Net\Http
 * @category    Net\Http
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Status
{
    // Informational - Request received, continuing process.

    /**
     * @var int     Continue    As continue is a reserved word an underscore was added to each constant to prefix them
     *                          and remain consistent.
     **/
    const __CONTINUE = 100;

    /**
     * @var int     Switching Protocols
     **/
    const _SWITCHING_PROTOCOLS = 101;


    // Success - The action was successfully received, understood, and accepted.
    
    /**
     * @var int     OK
     **/
    const _OK = 200;
    
    /**
     * @var int     Created
     **/
    const _CREATED = 201;
    
    /**
     * @var int     Accepted
     **/
    const _ACCEPTED = 202;
    
    /**
     * @var int     Non-Authoritative Information
     **/
    const _NON_AUTHORITATIVE_INFORMATION = 203;
    
    /**
     * @var int     No Content
     **/
    const _NO_CONTENT = 204;
    
    /**
     * @var int     Reset Content
     **/
    const _RESET_CONTENT = 205;
    
    /**
     * @var int     Partial Content
     **/
    const _PARTIAL_CONTENT = 206;


    // Redirection - Further action must be taken in order to complete the request.
    
    /**
     * @var int     Multiple Choices
     **/
    const _MULTIPLE_CHOICES = 300;
    
    /**
     * @var int     Moved Permanently
     **/
    const _MOVED_PERMANENTLY = 301;
    
    /**
     * @var int     Found
     **/
    const _FOUND = 302;
    
    /**
     * @var int     See Other
     **/
    const _SEE_OTHER = 303;
    
    /**
     * @var int     Not Modified
     **/
    const _NOT_MODIFIED = 304;
    
    /**
     * @var int     Use Proxy
     **/
    const _USE_PROXY = 305;
    
    /**
     * @var int     Temporary Redirect
     **/
    const _TEMPORARY_REDIRECT = 307;


    // Client Error - The request contains bad syntax or cannot be fulfilled
    
    /**
     * @var int     Bad Request
     **/
    const _BAD_REQUEST = 400;
    
    /**
     * @var int     Unauthorized
     **/
    const _UNAUTHORIZED = 401;
    
    /**
     * @var int     Payment Required
     **/
    const _PAYMENT_REQUIRED = 402;
    
    /**
     * @var int     Forbidden
     **/
    const _FORBIDDEN = 403;
    
    /**
     * @var int     Not Found
     **/
    const _NOT_FOUND = 404;
    
    /**
     * @var int     Method Not Allowed
     **/
    const _METHOD_NOT_ALLOWED = 405;
    
    /**
     * @var int     Not Acceptable
     **/
    const _NOT_ACCEPTABLE = 406;
    
    /**
     * @var int     Proxy Authentication Required
     **/
    const _PROXY_AUTHENTICATION_REQUIRED = 407;
    
    /**
     * @var int     Request Time-out
     **/
    const _REQUEST_TIME_OUT = 408;
    
    /**
     * @var int     Conflict
     **/
    const _CONFLICT = 409;
    
    /**
     * @var int     Gone
     **/
    const _GONE = 410;
    
    /**
     * @var int     Length Required
     **/
    const _LENGTH_REQUIRED = 411;
    
    /**
     * @var int     Precondition Failed
     **/
    const _PRECONDITION_FAILED = 412;
    
    /**
     * @var int     Request Entity Too Large
     **/
    const _REQUEST_ENTITY_TOO_LARGE = 413;
    
    /**
     * @var int     Request-URI Too Large
     **/
    const _REQUEST_URI_TOO_LARGE = 414;
    
    /**
     * @var int     Unsupported Media Type
     **/
    const _UNSUPPORTED_MEDIA_TYPE = 415;
    
    /**
     * @var int     Requested range not satisfiable
     **/
    const _REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    
    /**
     * @var int     Expectation Failed
     **/
    const _EXPECTATION_FAILED = 417;


    // Server Error - The server failed to fulfill an apparently valid request
    
    /**
     * @var int     Internal Server Error
     **/
    const _INTERNAL_SERVER_ERROR = 500;
    
    /**
     * @var int     Not Implemented
     **/
    const _NOT_IMPLEMENTED = 501;
    
    /**
     * @var int     Bad Gateway
     **/
    const _BAD_GATEWAY = 502;
    
    /**
     * @var int     Service Unavailable
     **/
    const _SERVICE_UNAVAILABLE = 503;
    
    /**
     * @var int     Gateway Time-out
     **/
    const _GATEWAY_TIME_OUT = 504;
    
    /**
     * @var int     HTTP Version not supported
     **/
    const _HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * @var array   Contains all HTTP/1.1 status codes with their Reason-Phrases.
     **/
    private $_codes = array(
        // Informational - Request received, continuing process.
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success - The action was successfully received, understood, and accepted.
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection - Further action must be taken in order to complete the request.
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        // Client Error - The request contains bad syntax or cannot be fulfilled
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',

        // Server Error - The server failed to fulfill an apparently valid request
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported'
    );
}
