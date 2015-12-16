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
    const LEXICAL_ERROR           = 2;

    const MESSAGES = [
        self::TEMPORARILY_UNAVAILABLE => 'This Service is temporarily unavailable',
        self::LEXICAL_ERROR           => 'Lexical error on line %d, unrecognized text'
    ];

    /**
     * @var array
     */
    private $args = [];

    /**
     * @var null
     */
    private $jsonLineNo = null;

    /**
     * @var null
     */
    private $jsonColumnNo = null;

    /**
     * @var null
     */
    private $jsonMatch = null;

    /**
     * @var null
     */
    private $jsonToken = null;

    /**
     * @var null
     */
    private $jsonExpected = null;

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
     * @return null
     */
    public function getJsonLineNo()
    {
        return $this->jsonLineNo;
    }

    /**
     * @param null $jsonLineNo
     */
    public function setJsonLineNo($jsonLineNo)
    {
        $this->jsonLineNo = $jsonLineNo;
    }

    /**
     * @return null
     */
    public function getJsonColumnNo()
    {
        return $this->jsonColumnNo;
    }

    /**
     * @param null $jsonColumnNo
     */
    public function setJsonColumnNo($jsonColumnNo)
    {
        $this->jsonColumnNo = $jsonColumnNo;
    }

    /**
     * @return null
     */
    public function getJsonMatch()
    {
        return $this->jsonMatch;
    }

    /**
     * @param null $jsonMatch
     */
    public function setJsonMatch($jsonMatch)
    {
        $this->jsonMatch = $jsonMatch;
    }

    /**
     * @return null
     */
    public function getJsonToken()
    {
        return $this->jsonToken;
    }

    /**
     * @param null $jsonToken
     */
    public function setJsonToken($jsonToken)
    {
        $this->jsonToken = $jsonToken;
    }

    /**
     * @return null
     */
    public function getJsonExpected()
    {
        return $this->jsonExpected;
    }

    /**
     * @param null $jsonExpected
     */
    public function setJsonExpected($jsonExpected)
    {
        $this->jsonExpected = $jsonExpected;
    }

    /**
     * @return array
     */
    public function getAllJsonDetails()
    {
        return [
            'match'    => $this->getJsonMatch(),
            'token'    => $this->getJsonToken(),
            'line'     => $this->getJsonLineNo(),
            'column'   => $this->getJsonColumnNo(),
            'expected' => $this->getJsonExpected()
        ];
    }

    /**
     * Append the current message with some more text
     *
     * @param $appendingText
     */
    public function appendMessage($appendingText)
    {
        $this->message = $this->getMessage() . $appendingText;
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

                $string = vsprintf(constant("static::$constName")[$code], $this->getArgs());
            } catch (\Exception $e) { /* do nothing */
            }
        }

        return $string;
    }
}