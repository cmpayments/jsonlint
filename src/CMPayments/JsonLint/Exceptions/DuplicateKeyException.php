<?php namespace CMPayments\JsonLint\Exceptions;

/**
 * Class DuplicateKeyException
 *
 * @package CMPayments\JsonLint\Exceptions
 */
class DuplicateKeyException extends JsonLintException
{
    const PARSE_ERROR_DUPLICATE_KEY = 1;

    protected $messages = [
        self::PARSE_ERROR_DUPLICATE_KEY => 'Parse error on line %d, duplicate key: %s'
    ];

    private $key = null;

    /**
     * ApiException constructor.
     *
     * @param string $code
     * @param array  $key
     * @param array  $args
     * @param null   $message
     */
    public function __construct($code, $key, $args = [], $message = null)
    {
        $this->key = $key;

        parent::__construct($code, $args, $message);
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }
}
