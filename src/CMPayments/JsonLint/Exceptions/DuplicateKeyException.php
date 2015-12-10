<?php namespace CMPayments\JsonLint\Exceptions;

/**
 * Class DuplicateKeyException
 *
 * @package CMPayments\JsonLint\Exceptions
 */
class DuplicateKeyException extends ParsingException
{
    /**
     * DuplicateKeyException constructor.
     *
     * @param string $message
     * @param array  $key
     * @param array  $details
     */
    public function __construct($message, $key, array $details = [])
    {
        $details['key'] = $key;

        parent::__construct($message, $details);
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->details['key'];
    }
}
