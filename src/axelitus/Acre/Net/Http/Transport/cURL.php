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
 * Transport_cURL Class
 *
 * @package     axelitus\Acre\Net\Http
 * @category    Net\Http
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Transport_cURL extends Transport
{
    /**
     * @var UserAgent|null      The UserAgent object that's communicating
     */
    protected $user_agent = null;

    /**
     * @var null|resource   The cURL instance
     */
    protected $curl = null;

    /**
     * @var string      The cURL version
     */
    protected $curl_version = '';

    /**
     * @var array       The cURL options
     */
    protected $curl_options = array();

    /**
     * @static
     * @var array   The default cURL options to be used
     */
    protected static $default_curl_options = array(
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false
    );

    /**
     * Verifies if cURL is available
     *
     * @static
     * @return bool     Whether cURL is available
     */
    public static function isAvailable()
    {
        if (in_array('curl', get_loaded_extensions())) {
            return true;
        }

        return false;
    }

    /**
     * Forges a new cURL Transport
     *
     * @static
     * @param UserAgent    $user_agent      The user agent
     * @param \ArrayAccess $cURL_options    The cURL options to use/merge
     * @param bool         $use_defaults    Whether to use the default cURL options as base
     * @return Transport_cURL   The new cURL Transport object
     */
    public static function forge(UserAgent $user_agent, \ArrayAccess $cURL_options = null, $use_defaults = true)
    {
        $default = ($use_defaults) ? static::$default_curl_options : array();

        $options = Arr::forge($default);
        $options->merge((($cURL_options === null) ? array() : $cURL_options));

        $transport = new static($user_agent, $options);

        return $transport;
    }

    /**
     * Creates a new instance of cURL Transport class.
     *
     * @param UserAgent    $user_agent      The user agent
     * @param \ArrayAccess $cURL_options    The cURL options
     * @throws \Exception
     */
    protected function __construct(UserAgent $user_agent, \ArrayAccess $cURL_options)
    {
        if (!static::isAvailable()) {
            throw new \Exception("cURL extension is not available, cannot use this transport.");
        }

        $this->user_agent = $user_agent;

        $this->cURLInit();
        $this->cURLSetOptions($cURL_options);
    }

    /**
     * Initializes the cURL object
     */
    protected function cURLInit()
    {
        // Initialize cURL
        $this->curl_version = curl_version();
        $this->curl_version = $this->curl_version['version'];
        $this->curl = curl_init();
    }

    /**
     * Sets a cURL option to the options array and to the cURL instance if available
     *
     * @param int       $key      The cURL option identifier
     * @param mixed     $value    The value to set
     */
    public function cURLSetOption($key, $value)
    {
        $this->curl_options[$key] = $value;
        if ($key == CURLOPT_ENCODING) {
            if (version_compare($this->version, '7.10.5', '>=')) {
                $this->curl_options[$key] = '';
            }
        }
        $this->cURLLoadOption($key);
    }

    /**
     * Loads a cURL option into the cURL instance
     *
     * @param int   $key      The option to load
     */
    public function cURLLoadOption($key)
    {
        if ($this->curl !== null) {
            if (isset($this->curl_options[$key])) {
                curl_setopt($this->curl, $key, $this->curl_options[$key]);
            }
        }
    }

    /**
     * Sets multiple cURL options into the options array and the cURL instance if available
     *
     * @param \ArrayAccess $options     The options to set
     */
    public function cURLSetOptions(\ArrayAccess $options)
    {
        foreach ($options as $key => $value) {
            $this->cURLSetOption($key, $value);
        }
    }

    /**
     * Sends a Request using cURL as transport
     *
     * @param Request $request      The request to be sent
     * @return Response             The received response
     * @throws \RuntimeException
     */
    protected function sendRequest(Request $request)
    {
        $this->preparecURL($request);

        $response = curl_exec($this->curl);
        if (($curl_err = curl_errno($this->curl)) !== 0) {
            // Fix a known encoding error?
            if ($curl_err === 23 || $curl_err === 61) {
                $this->cURLSetOption(CURLOPT_ENCODING, 'none');

                // Second attempt with new encoding options
                $response = curl_exec($this->curl);

                if (($curl_err = curl_errno($this->curl)) !== 0) {
                    throw new \RuntimeException("A cURL error occurred ({$curl_err}): \"".curl_error($this->curl)."\".");
                }
            } else {
                throw new \RuntimeException("A cURL error occurred ({$curl_err}): \"".curl_error($this->curl)."\".");
            }
        }

        $response = Response::parse($response);

        curl_close($this->curl);

        return $response;
    }

    /**
     * Prepares some cURL options depending of request type
     *
     * @param Request $request      The request to be sent
     */
    protected function preparecURL(Request $request)
    {
        switch ($request->method) {
            case Method::CONNECT:
                break;
            case Method::DELETE:
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $request->method);
                break;
            case Method::GET:
                break;
            case Method::HEAD:
                curl_setopt($this->curl, CURLOPT_NOBODY, true);
                break;
            case Method::OPTIONS:
                break;
            case Method::POST:
                curl_setopt($this->curl, CURLOPT_POST, true);
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $request->body);
                break;
            case Method::PUT:
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $request->method);
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $request->body);
                break;
            case Method::TRACE:
                break;
            default:
                break;
        }

        $this->cURLSetOption(CURLOPT_URL, (string)$request->uri);
        $this->cURLSetOption(CURLOPT_HTTPHEADER, $request->headers->stringArray());
        $this->cURLSetOption(CURLOPT_USERAGENT, (string)$this->user_agent);

        // Forces to receive the complete HTTP message
        $this->cURLSetOption(CURLOPT_HEADER, true);
    }
}
