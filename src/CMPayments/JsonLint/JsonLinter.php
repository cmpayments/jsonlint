<?php namespace CMPayments\JsonLint;

use CMPayments\JsonLint\Exceptions\DuplicateKeyException;
use CMPayments\JsonLint\Exceptions\ParseException;
use CMPayments\JsonLint\Exceptions\UndefinedException;
use stdClass;

/**
 * Class JsonParser
 *
 * @package CMPayments\JsonLint
 */
class JsonLinter
{
    const DETECT_KEY_CONFLICTS = 1;
    const ALLOW_DUPLICATE_KEYS = 2;
    const PARSE_TO_ASSOC       = 4;

    /**
     * @var
     */
    private $lexer;

    /**
     * @var
     */
    private $flags;

    /**
     * @var
     */
    private $stack;

    /**
     * @var
     */
    private $vStack; // semantic value stack

    /**
     * @var
     */
    private $lStack; // location stack

    /**
     * @var array
     */
    private $symbols = [
        'error'              => 2,
        'JSONString'         => 3,
        'STRING'             => 4,
        'JSONNumber'         => 5,
        'NUMBER'             => 6,
        'JSONNullLiteral'    => 7,
        'NULL'               => 8,
        'JSONBooleanLiteral' => 9,
        'TRUE'               => 10,
        'FALSE'              => 11,
        'JSONText'           => 12,
        'JSONValue'          => 13,
        'EOF'                => 14,
        'JSONObject'         => 15,
        'JSONArray'          => 16,
        '{'                  => 17,
        '}'                  => 18,
        'JSONMemberList'     => 19,
        'JSONMember'         => 20,
        ':'                  => 21,
        ','                  => 22,
        '['                  => 23,
        ']'                  => 24,
        'JSONElementList'    => 25,
        '$accept'            => 0,
        '$end'               => 1
    ];

    /**
     * @var array
     */
    private $terminals = [2 => "error", 4 => "STRING", 6 => "NUMBER", 8 => "NULL", 10 => "TRUE", 11 => "FALSE", 14 => "EOF", 17 => "{", 18 => "}", 21 => ":", 22 => ",", 23 => "[", 24 => "]"];

    /**
     * @var array
     */
    private $productions = [0, [3, 1], [5, 1], [7, 1], [9, 1], [9, 1], [12, 2], [13, 1], [13, 1], [13, 1], [13, 1], [13, 1], [13, 1], [15, 2], [15, 3], [20, 3], [19, 1], [19, 3], [16, 2], [16, 3], [25, 1], [25, 3]];

    /**
     * @var array
     */
    private $table = [
        [3 => 5, 4 => [1, 12], 5 => 6, 6 => [1, 13], 7 => 3, 8 => [1, 9], 9 => 4, 10 => [1, 10], 11 => [1, 11], 12 => 1, 13 => 2, 15 => 7, 16 => 8, 17 => [1, 14], 23 => [1, 15]],
        [1 => [3]],
        [14 => [1, 16]],
        [14 => [2, 7], 18 => [2, 7], 22 => [2, 7], 24 => [2, 7]],
        [14 => [2, 8], 18 => [2, 8], 22 => [2, 8], 24 => [2, 8]],
        [14 => [2, 9], 18 => [2, 9], 22 => [2, 9], 24 => [2, 9]],
        [14 => [2, 10], 18 => [2, 10], 22 => [2, 10], 24 => [2, 10]],
        [14 => [2, 11], 18 => [2, 11], 22 => [2, 11], 24 => [2, 11]],
        [14 => [2, 12], 18 => [2, 12], 22 => [2, 12], 24 => [2, 12]],
        [14 => [2, 3], 18 => [2, 3], 22 => [2, 3], 24 => [2, 3]],
        [14 => [2, 4], 18 => [2, 4], 22 => [2, 4], 24 => [2, 4]],
        [14 => [2, 5], 18 => [2, 5], 22 => [2, 5], 24 => [2, 5]],
        [14 => [2, 1], 18 => [2, 1], 21 => [2, 1], 22 => [2, 1], 24 => [2, 1]],
        [14 => [2, 2], 18 => [2, 2], 22 => [2, 2], 24 => [2, 2]],
        [3 => 20, 4 => [1, 12], 18 => [1, 17], 19 => 18, 20 => 19],
        [3 => 5, 4 => [1, 12], 5 => 6, 6 => [1, 13], 7 => 3, 8 => [1, 9], 9 => 4, 10 => [1, 10], 11 => [1, 11], 13 => 23, 15 => 7, 16 => 8, 17 => [1, 14], 23 => [1, 15], 24 => [1, 21], 25 => 22],
        [1 => [2, 6]],
        [14 => [2, 13], 18 => [2, 13], 22 => [2, 13], 24 => [2, 13]],
        [18 => [1, 24], 22 => [1, 25]],
        [18 => [2, 16], 22 => [2, 16]],
        [21 => [1, 26]],
        [14 => [2, 18], 18 => [2, 18], 22 => [2, 18], 24 => [2, 18]],
        [22 => [1, 28], 24 => [1, 27]],
        [22 => [2, 20], 24 => [2, 20]],
        [14 => [2, 14], 18 => [2, 14], 22 => [2, 14], 24 => [2, 14]],
        [3 => 20, 4 => [1, 12], 20 => 29],
        [3 => 5, 4 => [1, 12], 5 => 6, 6 => [1, 13], 7 => 3, 8 => [1, 9], 9 => 4, 10 => [1, 10], 11 => [1, 11], 13 => 30, 15 => 7, 16 => 8, 17 => [1, 14], 23 => [1, 15]],
        [14 => [2, 19], 18 => [2, 19], 22 => [2, 19], 24 => [2, 19]],
        [3 => 5, 4 => [1, 12], 5 => 6, 6 => [1, 13], 7 => 3, 8 => [1, 9], 9 => 4, 10 => [1, 10], 11 => [1, 11], 13 => 31, 15 => 7, 16 => 8, 17 => [1, 14], 23 => [1, 15]],
        [18 => [2, 17], 22 => [2, 17]],
        [18 => [2, 15], 22 => [2, 15]],
        [22 => [2, 21], 24 => [2, 21]]
    ];

    /**
     * @var array
     */
    private $defaultActions = [16 => [2, 6]];

    /**
     * @param string $input JSON string
     * @param int    $flags
     *
     * @return boolean|ParseException null if no error is found, a ParseException containing all details otherwise
     */
    public function lint($input, $flags = 0)
    {
        try {

            return $this->parse($input, $flags);
        } catch (\Exception $e) {

            return $e;
        }
    }

    /**
     * @param string $input
     * @param int    $flags
     *
     * @return bool
     * @throws DuplicateKeyException
     * @throws ParseException
     * @throws null
     */
    public function parse($input, $flags = 0)
    {
        $this->lexer = new Lexer();
        $this->lexer->setInput($input);

        if (!is_string($input)) {

            $e = new ParseException(ParseException::ERROR_NOT_A_STRING, [gettype($input)]);

            // we +1 the line number on which an error occurred to make it human readable
            $e->setJsonLineNo(($this->lexer->yLineNo + 1));

            throw $e;
        }

        $this->failOnBOM($input);

        $this->flags = $flags;

        $this->stack  = [0];
        $this->vStack = [null];
        $this->lStack = [];

        $yText   = '';
        $yLineNo = $recovering = 0;
        $eof     = 1;
        $terror  = 2;

        $yLocation      = $this->lexer->yLocation;
        $this->lStack[] = $yLocation;

        $symbol = $preErrorSymbol = $p = null;
        $yVal   = new stdClass;

        $isMultiLine = count(explode("\n", str_replace("\n\r", "\n", $input))) > 1;

        while (true) {

            // retrieve state number from top of stack
            $state = $this->stack[count($this->stack) - 1];

            // use default actions if available
            if (isset($this->defaultActions[$state])) {

                $action = $this->defaultActions[$state];
            } else {

                if ($symbol === null) {

                    $symbol = $this->lex();
                }

                // read action for current state and first input
                $action = isset($this->table[$state][$symbol]) ? $this->table[$state][$symbol] : false;
            }

            // handle parse error
            if (!$action || !$action[0]) {

                $e = null;

                if (!$recovering) {

                    // Report error
                    $expected = [];

                    foreach ($this->table[$state] as $p => $ignore) {

                        if (isset($this->terminals[$p]) && $p > 2) {

                            $expected[] = $this->terminals[$p];
                        }
                    }

                    if (in_array("STRING", $expected) && in_array(substr($this->lexer->yText, 0, 1), ['"', "'"])) {

                        if (substr($this->lexer->yText, 0, 1) === "'") {

                            $e = new ParseException(ParseException::ERROR_USED_SINGLE_QUOTES, array_merge(['match' => $this->lexer->match], $this->getExceptionArguments($symbol)));
                        } elseif (preg_match('{".+?(\\\\[^"bfnrt/\\\\u])}', $this->lexer->getUpcomingInput())) {

                            $e = new ParseException(ParseException::ERROR_UNESCAPED_BACKSLASH, array_merge(['match' => $this->lexer->match], $this->getExceptionArguments($symbol)));
                        } elseif (preg_match('{"(?:[^"]+|\\\\")*$}m', $this->lexer->getUpcomingInput())) {

                            $e = new ParseException(ParseException::ERROR_NOT_TERMINATED_OR_MULTI_LINE, array_merge(['match' => $this->lexer->match], $this->getExceptionArguments($symbol)));
                        } else {

                            $e = new ParseException(ParseException::ERROR_INVALID_STRING, $this->getExceptionArguments($symbol));
                        }
                    }

                    if (is_null($e)) {

                        $e = new ParseException(ParseException::ERROR_EXPECTED_INPUT_TO_BE_SOMETHING_ELSE, array_merge(
                            [
                                ((count($expected) > 1) ? ' one of' : ''),
                                implode('\', \'', $expected),
                                utf8_encode($this->lexer->match) // encode any special characters that might be entered by the user
                            ], $this->getExceptionArguments($symbol)
                        ));
                    }

                    if (substr(trim($this->lexer->getPastInput()), -1) === ',') {

                        $e->appendMessage($e->getItemFromVariableArray(ParseException::ERROR_APPEND_TRAILING_COMMA_ERROR));

                        // usually we +1 the line number on which an error occurred to make it human readable
                        // BUT when it involves a trailing comma the error is located on the line above the current one
                        // IF however the $input IS multi line, we do NOT increase the line number
                        $e->setJsonLineNo((($isMultiLine) ? $this->lexer->yLineNo : ($this->lexer->yLineNo + 1)));
                    } else {

                        // we +1 the line number on which an error occurred to make it human readable
                        $e->setJsonLineNo(($this->lexer->yLineNo + 1));
                    }

                    $e->setJsonMatch($this->lexer->match);
                    $e->setJsonToken((!empty($this->terminals[$symbol]) ? $this->terminals[$symbol] : $symbol));
                    $e->setJsonColumnNo($this->lexer->yColumnNo);
                    $e->setJsonExpected($expected);

                    throw $e;
                }

                // just recovered from another error
                if ($recovering == 3) {

                    if ($symbol == $eof) {

                        if (is_null($e)) {

                            throw new ParseException(ParseException::ERROR_PARSING_HALTED);
                        }
                    }

                    // discard current lookahead and grab another
                    $yText   = $this->lexer->yText;
                    $yLineNo = $this->lexer->yLineNo;
                    $symbol  = $this->lex();
                }

                // try to recover from error
                while (true) {

                    // check for error recovery rule in this state
                    if (array_key_exists($terror, $this->table[$state])) {

                        break;
                    }

                    if ($state == 0) {

                        if (is_null($e)) {

                            throw new ParseException(ParseException::ERROR_PARSING_HALTED);
                        }
                    }

                    $this->popStack(1);
                    $state = $this->stack[count($this->stack) - 1];
                }

                // save the lookahead token
                $preErrorSymbol = $symbol;

                // insert generic error symbol as new lookahead
                $symbol = $terror;

                // allow 3 real symbols to be shifted before reporting a new error
                $recovering = 3;
                $state      = $this->stack[count($this->stack) - 1];
                $action     = isset($this->table[$state][$terror]) ? $this->table[$state][$terror] : false;
            }

            // this shouldn't happen, unless resolve defaults are off
            if (is_array($action[0]) && is_array($action) && count($action) > 1) {

                throw new ParseException(ParseException::ERROR_PARSING_ERROR_MULTIPLE_ACTIONS, [$state, $symbol]);
            }

            switch ($action[0]) {

                // shift
                case 1:
                    $this->stack[]  = $symbol;
                    $this->vStack[] = $this->lexer->yText;
                    $this->lStack[] = $this->lexer->yLocation;
                    $this->stack[]  = $action[1]; // push state
                    $symbol         = null;

                    // normal execution/no error
                    if ($preErrorSymbol === null) {

                        $yText   = $this->lexer->yText;
                        $yLineNo = $this->lexer->yLineNo;

                        if ($recovering > 0) {

                            $recovering--;
                        }
                    } else {

                        // error just occurred, resume old lookahead f/ before error
                        $symbol         = $preErrorSymbol;
                        $preErrorSymbol = null;
                    }
                    break;

                // reduce
                case 2:
                    $len = $this->productions[$action[1]][1];

                    // perform semantic action
                    $yVal->token = $this->vStack[count($this->vStack) - $len];

                    // default location, uses first token for firsts, last for lasts
                    $yVal->store = [
                        'last_line'   => $this->lStack[count($this->lStack) - 1]['last_line'],
                        'last_column' => $this->lStack[count($this->lStack) - 1]['last_column'],
                    ];

                    $result = $this->performAction($yVal, $yText, $yLineNo, $action[1], $this->vStack);

                    if (!$result instanceof UndefinedException) {

                        return $result;
                    }

                    if ($len) {

                        $this->popStack($len);
                    }

                    // push non terminal (reduce)
                    $this->stack[]  = $this->productions[$action[1]][0];
                    $this->vStack[] = $yVal->token;
                    $this->lStack[] = $yVal->store;
                    $newState       = $this->table[$this->stack[count($this->stack) - 2]][$this->stack[count($this->stack) - 1]];
                    $this->stack[]  = $newState;
                    break;

                // accept
                case 3:

                    return true;
            }
        }

        return true;
    }

    /**
     * @param null|string $symbol
     *
     * @return array
     */
    private function getExceptionArguments($symbol = null)
    {
        $arr = [
            'lineNo'   => ($this->lexer->yLineNo + 1),
            'columnNo' => $this->lexer->yColumnNo,
            'match'    => $this->lexer->match
        ];

        if (!is_null($symbol)) {

            $arr['token'] = (!empty($this->terminals[$symbol]) ? $this->terminals[$symbol] : $symbol);
        }

        return $arr;
    }

    /**
     * @param stdClass $yVal
     * @param string   $yText
     * @param int      $yLineNo
     * @param int      $yState
     * @param array    $tokens
     *
     * @return UndefinedException
     * @throws DuplicateKeyException
     */
    private function performAction(stdClass $yVal, $yText, $yLineNo, $yState, &$tokens)
    {
        $len = count($tokens) - 1;

        switch ($yState) {

            case 1:
                $yText       = preg_replace_callback('{(?:\\\\["bfnrt/\\\\]|\\\\u[a-fA-F0-9]{4})}', [$this, 'stringInterpolation'], $yText);
                $yVal->token = $yText;
                break;

            case 2:
                if (strpos($yText, 'e') !== false || strpos($yText, 'E') !== false) {

                    $yVal->token = floatval($yText);
                } else {

                    $yVal->token = strpos($yText, '.') === false ? intval($yText) : floatval($yText);
                }
                break;

            case 3:
                $yVal->token = null;
                break;

            case 4:
                $yVal->token = true;
                break;

            case 5:
                $yVal->token = false;
                break;

            case 6:
                return $yVal->token = $tokens[$len - 1];

            case 13:

                if ($this->flags & self::PARSE_TO_ASSOC) {

                    $yVal->token = [];
                } else {

                    $yVal->token = new stdClass;
                }
                break;

            case 14:
                $yVal->token = $tokens[$len - 1];
                break;

            case 15:
                $yVal->token = [$tokens[$len - 2], $tokens[$len]];
                break;

            case 16:
                $property = $tokens[$len][0] === '' ? '_empty_' : $tokens[$len][0];

                if ($this->flags & self::PARSE_TO_ASSOC) {

                    $yVal->token            = [];
                    $yVal->token[$property] = $tokens[$len][1];
                } else {

                    $yVal->token            = new stdClass;
                    $yVal->token->$property = $tokens[$len][1];
                }
                break;

            case 17:
                if ($this->flags & self::PARSE_TO_ASSOC) {

                    $yVal->token =& $tokens[$len - 2];
                    $key         = $tokens[$len][0];

                    if (($this->flags & self::DETECT_KEY_CONFLICTS) && isset($tokens[$len - 2][$key])) {

                        $args = ['line' => ($yLineNo + 1), 'key' => $tokens[$len][0]];
                        $e    = new DuplicateKeyException(DuplicateKeyException::ERROR_PARSE_ERROR_DUPLICATE_KEY, $tokens[$len][0], $args);

                        $e->setJsonLineNo($args['line']);
                        $e->setJsonMatch($args['key']);
                        $e->setJsonColumnNo($this->lexer->yColumnNo);

                        throw $e;
                    } elseif (($this->flags & self::ALLOW_DUPLICATE_KEYS) && isset($tokens[$len - 2][$key])) {

                        $duplicateCount = 1;

                        do {

                            $duplicateKey = $key . '.' . $duplicateCount++;
                        } while (isset($tokens[$len - 2][$duplicateKey]));

                        $key = $duplicateKey;
                    }

                    $tokens[$len - 2][$key] = $tokens[$len][1];
                } else {

                    $yVal->token = $tokens[$len - 2];
                    $key         = $tokens[$len][0] === '' ? '_empty_' : $tokens[$len][0];

                    if (($this->flags & self::DETECT_KEY_CONFLICTS) && isset($tokens[$len - 2]->{$key})) {

                        $args = ['line' => ($yLineNo + 1), 'key' => $tokens[$len][0]];
                        $e    = new DuplicateKeyException(DuplicateKeyException::ERROR_PARSE_ERROR_DUPLICATE_KEY, $tokens[$len][0], $args);

                        $e->setJsonLineNo($args['line']);
                        $e->setJsonMatch($args['key']);
                        $e->setJsonColumnNo($this->lexer->yColumnNo);

                        throw $e;
                    } elseif (($this->flags & self::ALLOW_DUPLICATE_KEYS) && isset($tokens[$len - 2]->{$key})) {

                        $duplicateCount = 1;

                        do {

                            $duplicateKey = $key . '.' . $duplicateCount++;
                        } while (isset($tokens[$len - 2]->$duplicateKey));

                        $key = $duplicateKey;
                    }

                    $tokens[$len - 2]->$key = $tokens[$len][1];
                }
                break;

            case 18:

                $yVal->token = [];
                break;

            case 19:

                $yVal->token = $tokens[$len - 1];
                break;

            case 20:

                $yVal->token = [$tokens[$len]];
                break;

            case 21:

                $tokens[$len - 2][] = $tokens[$len];
                $yVal->token        = $tokens[$len - 2];
                break;
        }

        return new UndefinedException(UndefinedException::ERROR_UNDEFINED_VALIDATION);
    }

    /**
     * alter $match[0] to a new value
     *
     * @param array $match
     *
     * @return string
     */
    private function stringInterpolation($match)
    {
        switch ($match[0]) {

            case '\\\\':
                return '\\';

            case '\"':
                return '"';

            case '\b':
                return chr(8);

            case '\f':
                return chr(12);

            case '\n':
                return "\n";

            case '\r':
                return "\r";

            case '\t':
                return "\t";

            case '\/':
                return "/";

            default:
                return html_entity_decode('&#x' . ltrim(substr($match[0], 2), '0') . ';', 0, 'UTF-8');
        }
    }

    /**
     * Returns the sequence of elements from the arrays as specified by the offset and length ($n) parameters.
     *
     * @param int $n
     */
    private function popStack($n)
    {
        $this->stack  = array_slice($this->stack, 0, -(2 * $n));
        $this->vStack = array_slice($this->vStack, 0, -$n);
        $this->lStack = array_slice($this->lStack, 0, -$n);
    }

    /**
     * @return int
     */
    private function lex()
    {
        $token = $this->lexer->lex() ?: 1;

        // if token isn't its numeric value, convert
        if (!is_numeric($token)) {

            $token = isset($this->symbols[$token]) ? $this->symbols[$token] : $token;
        }

        return $token;
    }

    /**
     * The byte order mark (BOM) is a Unicode character, which is not allowed
     *
     * @param string $input
     *
     * @throws ParseException
     */
    private function failOnBOM($input)
    {
        // UTF-8 ByteOrderMark sequence
        $bom = "\xEF\xBB\xBF";

        if (substr($input, 0, 3) === $bom) {

            throw new ParseException(ParseException::ERROR_BYTE_ORDER_MARK_DETECTED);
        }
    }
}
