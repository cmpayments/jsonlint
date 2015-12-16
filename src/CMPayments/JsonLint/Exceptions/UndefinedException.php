<?php namespace CMPayments\JsonLint\Exceptions;

/**
 * Class UndefinedException
 *
 * @package CMPayments\JsonLint
 */
class UndefinedException extends JsonLintException
{
    const UNDEFINED_VALIDATION = 1;

    const MESSAGES = [
        self::UNDEFINED_VALIDATION => 'This type of error in your JSON has not been defined',
    ];
}