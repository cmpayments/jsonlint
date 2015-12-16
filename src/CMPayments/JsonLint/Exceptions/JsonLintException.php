<?php namespace CMPayments\JsonLint\Exceptions;

/**
 * Class JsonLintException
 *
 * @author  Bas Peters <bp@cm.nl>
 * @author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 *
 * @package CMPayments\JsonLint\Exceptions
 */
class JsonLintException extends \ErrorException
{
    const TEMPORARILY_UNAVAILABLE = 1;

    const MESSAGES = [
        self::TEMPORARILY_UNAVAILABLE => 'This Service is temporarily unavailable'
    ];

    /**
     * @var array
     */
    private $args = [];

    /**
     * @var
     */
    private $description;

    /**
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param mixed $args
     */
    public function setArgs($args)
    {
        // check is $args is an array, if not, cast it into an array
        if (!is_array($args)) {

            $args = [$args];
        }

        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * ApiException constructor.
     *
     * @param string $code
     * @param array  $args
     * @param null   $message
     */
    public function __construct($code, $args = [], $message = null)
    {
        $this->setArgs($args);

        // parent constructor
        parent::__construct($this->getStringFromConstArray('MESSAGES', $code, 'This service is temporarily unavailable'), $code);

        // apply a description to the exception if one exists
        $this->setDescription($this->getStringFromConstArray('DESCRIPTIONS', $code));
    }

    /**
     * Retrieves a specific array key from a class constant
     *
     * @param      $constName
     * @param      $code
     * @param null $default
     *
     * @return null|string
     */
    private function getStringFromConstArray($constName, $code, $default = null)
    {
        $string = $default;

        // since this is an exception we cannot afford any new exceptions so we are extra careful
        if (defined('static::' . $constName)) {

            // PHP 5.6 is (for now) unable to check if array keys exist when this array is actually a (class) constant
            try {

                $string = static::$$constName[$code];
                $string = vsprintf($string, $this->getArgs());

            } catch (\Exception $e) { /* do nothing */ }
        }

        return $string;
    }
}
