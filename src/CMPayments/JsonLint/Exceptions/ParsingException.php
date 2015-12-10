<?php namespace CMPayments\JsonLint\Exceptions;

/**
 * Class ParsingException
 *
 * @package CMPayments\JsonLint\Exceptions
 */
class ParsingException extends \Exception
{
    /**
     * @var array
     */
    protected $details;

    /**
     * ParsingException constructor.
     *
     * @param string $message
     * @param array  $details
     */
    public function __construct($message, array $details = [])
    {
        $this->details = $details;

        parent::__construct($message);
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }
}
