<?php namespace CMPayments\JsonLint\Exceptions;

/**
 * Class ParsingException
 *
 * @package CMPayments\JsonLint\Exceptions
 */
class ParsingException extends JsonLintException
{
    const ERROR_NOT_A_STRING                        = 1;
    const ERROR_INVALID_STRING                      = 2;
    const ERROR_USED_SINGLE_QUOTES                  = 3;
    const ERROR_UNESCAPED_BACKSLASH                 = 4;
    const ERROR_NOT_TERMINATED_OR_MULTI_LINE        = 5;
    const ERROR_EXPECTED_INPUT_TO_BE_SOMETHING_ELSE = 6;
    const ERROR_APPEND_TRAILING_COMMA_ERROR         = 7;
    const ERROR_PARSING_HALTED                      = 8;
    const ERROR_PARSING_ERROR_MULTIPLE_ACTIONS      = 9;
    const ERROR_BYTE_ORDER_MARK_DETECTED            = 10;

    protected $messages = [
        self::ERROR_NOT_A_STRING                        => 'Input is not a string but is of type: \'%s\'',
        self::ERROR_INVALID_STRING                      => 'Invalid JSON on line %d at column %d',
        self::ERROR_USED_SINGLE_QUOTES                  => 'You used single quotes instead of double quotes on string \'%s\' on line %d at column %d',
        self::ERROR_UNESCAPED_BACKSLASH                 => 'You have an unescaped backslash at: \'%s\' on line %d at approximately column %d',
        self::ERROR_NOT_TERMINATED_OR_MULTI_LINE        => 'You forgot to terminated the string \'%s\' on line %d at column %d (or attempted to write a multi line string which is also invalid).',
        self::ERROR_EXPECTED_INPUT_TO_BE_SOMETHING_ELSE => 'Expected%s: \'%s\' when trying to match \'%s\' on line %d, column %d',
        self::ERROR_APPEND_TRAILING_COMMA_ERROR         => '. It appears you have an extra trailing comma',
        self::ERROR_PARSING_HALTED                      => 'Parsing halted.',
        self::ERROR_PARSING_ERROR_MULTIPLE_ACTIONS      => 'Parse Error: multiple actions possible at state: %s, token: %s',
        self::ERROR_BYTE_ORDER_MARK_DETECTED            => 'BOM detected, make sure your input does not include a Unicode Byte-Order-Mark'
    ];
}
