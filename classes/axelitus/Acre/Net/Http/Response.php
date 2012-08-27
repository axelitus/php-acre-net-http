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

use InvalidArgumentException;

/**
 * Requires axelitus\Acre\Common package
 */
use axelitus\Acre\Net\Http\Status as Status;

/**
 * Response Class
 *
 * @package     axelitus\Acre\Net\Http
 * @category    Net\Http
 * @author      Axel Pardemann (dev@axelitus.mx)
 */
class Response extends Message
{
    /**
     * @var int     The response status code
     */
    protected $_status = Status::_OK;

    /**
     * Status setter.
     *
     * @param $status
     * @return Response     This instance for chaining
     * @throws \InvalidArgumentException
     */
    protected function setStatus($status)
    {
        if (!Status::isValid($status)) {
            throw new InvalidArgumentException("{$status} is not a valid HTTP status code.");
        }

        $this->_status = $status;

        return $this;
    }

    /**
     * Status getter.
     *
     * @return int
     */
    protected function getStatus()
    {
        return $this->_status;
    }

    /**
     * Gets the messages start line.
     *
     * @return string
     */
    protected function startLine()
    {
        $startLine = sprintf("HTTP/%s %s\r\n", $this->_version, Status::phrase($this->_status, true));

        return $startLine;
    }
}
