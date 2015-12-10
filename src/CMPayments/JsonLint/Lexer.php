<?php namespace CMPayments\JsonLint;

/**
 * Class Lexer
 *
 * @package CMPayments\JsonLint
 */
class Lexer
{
    /**
     * @var int
     */
    private $EOF = 1;

    /**
     * @var array
     */
    private $rules = [
        0  => '/^\s+/',
        1  => '/^-?([0-9]|[1-9][0-9]+)(\.[0-9]+)?([eE][+-]?[0-9]+)?\b/',
        2  => '{^"(?:\\\\["bfnrt/\\\\]|\\\\u[a-fA-F0-9]{4}|[^\0-\x09\x0a-\x1f\\\\"]+)*"}',
        3  => '/^\{/',
        4  => '/^\}/',
        5  => '/^\[/',
        6  => '/^\]/',
        7  => '/^,/',
        8  => '/^:/',
        9  => '/^true\b/',
        10 => '/^false\b/',
        11 => '/^null\b/',
        12 => '/^$/',
        13 => '/^./',
    ];

    /**
     * @var array
     */
    private $conditions = ["INITIAL" => ["rules" => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13], "inclusive" => true]];

    /**
     * @var
     */
    private $conditionStack;
    /**
     * @var
     */
    private $input;
    /**
     * @var
     */
    private $more;
    /**
     * @var
     */
    private $done;
    /**
     * @var
     */
    private $matched;

    /**
     * @var
     */
    public $match;
    /**
     * @var
     */
    public $yLineNo;
    /**
     * @var
     */
    public $yLength;
    /**
     * @var
     */
    public $yText;
    /**
     * @var
     */
    public $yLocation;

    /**
     * @return Undefined|int|null|string
     */
    public function lex()
    {
        $r = $this->next();

        if (!$r instanceof Undefined) {

            return $r;
        }

        return $this->lex();
    }

    /**
     * @param $input
     *
     * @return $this
     */
    public function setInput($input)
    {
        $this->input          = $input;
        $this->more           = false;
        $this->done           = false;
        $this->yLineNo       = $this->yLength = 0;
        $this->yText         = $this->matched = $this->match = '';
        $this->conditionStack = ['INITIAL'];
        $this->yLocation         = ['first_line' => 1, 'first_column' => 0, 'last_line' => 1, 'last_column' => 0];

        return $this;
    }

    /**
     * @return string
     */
    public function showPosition()
    {
        $pre = str_replace("\n", '', $this->getPastInput());
        $c   = str_repeat('-', max(0, strlen($pre) - 1));

        return $pre . str_replace("\n", '', $this->getUpcomingInput()) . "\n" . $c . "^";
    }

    /**
     * @return string
     */
    public function getPastInput()
    {
        $past = substr($this->matched, 0, strlen($this->matched) - strlen($this->match));

        return (strlen($past) > 20 ? '...' : '') . substr($past, -20);
    }

    /**
     * @return string
     */
    public function getUpcomingInput()
    {
        $next = $this->match;

        if (strlen($next) < 20) {

            $next .= substr($this->input, 0, 20 - strlen($next));
        }

        return substr($next, 0, 20) . (strlen($next) > 20 ? '...' : '');
    }

    /**
     * @param $str
     *
     * @throws \Exception
     */
    protected function parseError($str)
    {
        throw new \Exception($str);
    }

    /**
     * @return Undefined|int|null|string
     * @throws \Exception
     */
    private function next()
    {
        if ($this->done) {

            return $this->EOF;
        }

        if (!$this->input) {

            $this->done = true;
        }

        $token = null;
        $match = null;
        $col   = null;
        $lines = null;

        if (!$this->more) {

            $this->yText = '';
            $this->match  = '';
        }

        $rules    = $this->getCurrentRules();
        $rulesLen = count($rules);

        for ($i = 0; $i < $rulesLen; $i++) {

            if (preg_match($this->rules[$rules[$i]], $this->input, $match)) {

                preg_match_all('/\n.*/', $match[0], $lines);
                $lines = $lines[0];

                if ($lines) {

                    $this->yLineNo += count($lines);
                }

                $this->yLocation = [
                    'first_line'   => $this->yLocation['last_line'],
                    'last_line'    => $this->yLineNo + 1,
                    'first_column' => $this->yLocation['last_column'],
                    'last_column'  => $lines ? strlen($lines[count($lines) - 1]) - 1 : $this->yLocation['last_column'] + strlen($match[0]),
                ];

                $this->yText .= $match[0];
                $this->match .= $match[0];
                $this->yLength = strlen($this->yText);
                $this->more   = false;
                $this->input  = substr($this->input, strlen($match[0]));
                $this->matched .= $match[0];
                $token = $this->performAction($rules[$i]);

                if ($token) {

                    return $token;
                }

                return new Undefined();
            }
        }

        if ($this->input === '') {

            return $this->EOF;
        }

        $this->parseError('Lexical error on line ' . ($this->yLineNo + 1) . ". Unrecognized text.\n" . $this->showPosition());

        return null;
    }

    /**
     * @return mixed
     */
    private function getCurrentRules()
    {
        return $this->conditions[$this->conditionStack[count($this->conditionStack) - 1]]['rules'];
    }

    /**
     * @param $avoiding_name_collisions
     *
     * @return int|string
     */
    private function performAction($avoiding_name_collisions)
    {
        switch ($avoiding_name_collisions) {

            case 0:/* skip whitespace */
                return null;

            case 1:
                return 6;

            case 2:
                $this->yText = substr($this->yText, 1, $this->yLength - 2);

                return 4;

            case 3:
                return 17;

            case 4:
                return 18;

            case 5:
                return 23;

            case 6:
                return 24;

            case 7:
                return 22;

            case 8:
                return 21;

            case 9:
                return 10;

            case 10:
                return 11;

            case 11:
                return 8;

            case 12:
                return 14;

            case 13:
                return 'INVALID';

            default:
                return null;
        }
    }
}