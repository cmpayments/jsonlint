<?php namespace CMPayments\JsonLint\Exceptions;

/**
 * Class ParsingException
 *
 * @package CMPayments\JsonLint\Exceptions
 */
class ParsingException extends JsonLintException
{
    const NOT_A_STRING                        = 1;
    const INVALID_STRING                      = 2;
    const USED_SINGLE_QUOTES                  = 3;
    const UNESCAPED_BACKSLASH                 = 4;
    const NOT_TERMINATED_OR_MULTI_LINE        = 5;
    const EXPECTED_INPUT_TO_BE_SOMETHING_ELSE = 6;
    const APPEND_TRAILING_COMMA_ERROR         = 7;
    const PARSING_HALTED                      = 8;
    const PARSING_ERROR_MULTIPLE_ACTIONS      = 9;
    const BYTE_ORDER_MARK_DETECTED            = 10;

    const MESSAGES = [
        self::NOT_A_STRING                        => 'Input is not a string but is of type: \'%s\'',
        self::INVALID_STRING                      => 'Invalid JSON on line %d at column %d',
        self::USED_SINGLE_QUOTES                  => 'You used single quotes instead of double quotes on line %d at column %d',
        self::UNESCAPED_BACKSLASH                 => 'You have an unescaped backslash at: \'%s\' on line %d at approximately column %d',
        self::NOT_TERMINATED_OR_MULTI_LINE        => 'You forgot to terminated the string, or attempted to write a multi line string which is invalid on line %d at column %d',
        self::EXPECTED_INPUT_TO_BE_SOMETHING_ELSE => 'Expected%s: \'%s\' when trying to match \'%s\' on line %d, column %d',
        self::APPEND_TRAILING_COMMA_ERROR         => '. It appears you have an extra trailing comma',
        self::PARSING_HALTED                      => 'Parsing halted.',
        self::PARSING_ERROR_MULTIPLE_ACTIONS      => 'Parse Error: multiple actions possible at state: %s, token: %s',
        self::BYTE_ORDER_MARK_DETECTED            => 'BOM detected, make sure your input does not include a Unicode Byte-Order-Mark'
    ];
}