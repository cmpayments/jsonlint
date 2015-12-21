<?php

use CMPayments\JsonLint\JsonLinter;
use CMPayments\JsonLint\Exceptions\ParseException;
use CMPayments\JsonLint\Exceptions\DuplicateKeyException;

/**
 * Class JsonLinterTest
 */
class JsonLintTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $valid = array(
        '42',
        '42.3',
        '0.3',
        '-42',
        '-42.3',
        '-0.3',
        '2e1',
        '2E1',
        '-2e1',
        '-2E1',
        '2E+2',
        '2E-2',
        '-2E+2',
        '-2E-2',
        'true',
        'false',
        'null',
        '""',
        '[]',
        '{}',
        '"string"',
        '["a", "sdfsd"]',
        '{"foo":"bar", "bar":"baz", "":"buz"}',
        '{"":"foo", "_empty_":"bar"}',
        '"\u00c9v\u00e9nement"',
        '"http:\/\/foo.com"',
        '"zo\\\\mg"',
        '{"test":"\u00c9v\u00e9nement"}',
        '["\u00c9v\u00e9nement"]',
        '"foo/bar"',
        '{"test":"http:\/\/foo\\\\zomg"}',
        '["http:\/\/foo\\\\zomg"]',
        '{"":"foo"}',
        '{"a":"b", "b":"c"}',
    );

    /**
     * @var array
     */
    protected $invalid = array(
        '',
        '{',
        '}',
        ''
    );

    /**
     * @var array
     */
    protected $otherThanString = array(
        array(),
        true,
        1,
        2.5
    );

    /**
     * @dataProvider provideValidStrings
     */
    public function testParsesValidStrings($input)
    {
        $parser = new JsonLinter();
        $this->assertEquals(json_decode($input), $parser->parse($input));
    }

    /**
     * @return array
     */
    public function provideValidStrings()
    {
        return $this->provideArrayForValidation($this->valid);
    }

    /**
     * @return array
     */
    public function provideInvalidStrings()
    {
        return $this->provideArrayForValidation($this->invalid);
    }

    /**
     * @return array
     */
    public function provideOtherValuesThanString()
    {
        return $this->provideArrayForValidation($this->otherThanString);
    }

    /**
     * @return array
     */
    private function provideArrayForValidation($input)
    {
        $strings = array();

        foreach ($input as $v) {
            $strings[] = array($v);
        }

        return $strings;
    }

    /**
     * @dataProvider provideInvalidStrings
     */
    public function testInValidString($input)
    {
        $parser = new JsonLinter();
        try {

            $parser->parse($input);
        } catch (ParseException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertEquals(ParseException::ERROR_EXPECTED_INPUT_TO_BE_SOMETHING_ELSE, $e->getCode(), 'Invalid string should be detected');
        }
    }

    /**
     * @dataProvider provideOtherValuesThanString
     */
    public function testOtherStringValues($input)
    {
        $parser = new JsonLinter();
        try {

            $parser->parse($input);
        } catch (ParseException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertEquals(ParseException::ERROR_NOT_A_STRING, $e->getCode(), 'Invalid type should be detected');
        }
    }

    /**
     * @throws null
     */
    public function testObjectInsteadOfString()
    {
        $parser = new JsonLinter();
        try {

            $parser->parse(new stdClass());
        } catch (ParseException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertEquals(ParseException::ERROR_NOT_A_STRING, $e->getCode(), 'Invalid type should be detected, becuase an object was given');
        }
    }

    /**
     * @throws null
     */
    public function testErrorOnTrailingComma_1()
    {
        $parser = new JsonLinter();
        try {
            $parser->parse('{"foo":"bar",}');
        } catch (ParseException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertContains($e->getItemFromVariableArray(ParseException::ERROR_APPEND_TRAILING_COMMA_ERROR), $e->getMessage(), 'Invalid trailing comma should be detected');
        }
    }

    public function testErrorOnTrailingComma_2()
    {
        $parser = new JsonLinter();
        try {
            $parser->parse('{
    "foo":"bar",
}');
        } catch (ParseException $e) {

            $this->assertEquals(2, $e->getJsonLineNo());
            $this->assertContains($e->getItemFromVariableArray(ParseException::ERROR_APPEND_TRAILING_COMMA_ERROR), $e->getMessage(), 'Invalid trailing comma should be detected');
        }
    }

    /**
     * @throws null
     */
    public function testErrorOnInvalidQuotes()
    {
        $parser = new JsonLinter();
        try {
            $parser->parse('{
    "foo": \'bar\',
}');
        } catch (ParseException $e) {

            $this->assertEquals(2, $e->getJsonLineNo());
            $this->assertEquals(ParseException::ERROR_USED_SINGLE_QUOTES, $e->getCode(), 'Invalid quotes for string should be detected');
        }
    }

    /**
     * @throws null
     */
    public function testErrorOnUnescapedBackslash()
    {
        $parser = new JsonLinter();
        try {

            $parser->parse('{
    "foo": "bar\z",
}');
        } catch (ParseException $e) {

            $this->assertEquals(2, $e->getJsonLineNo());
            $this->assertEquals(ParseException::ERROR_UNESCAPED_BACKSLASH, $e->getCode(), 'Invalid unescaped string should be detected');
        }
    }

    /**
     * @throws null
     */
    public function testErrorOnUnterminatedString()
    {
        $parser = new JsonLinter();
        try {

            $parser->parse('{"bar": "foo}');
        } catch (ParseException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertEquals(ParseException::ERROR_NOT_TERMINATED_OR_MULTI_LINE, $e->getCode(), 'Invalid unterminated string should be detected');
        }
    }

    /**
     * @throws null
     */
    public function testErrorOnMultilineString()
    {
        $parser = new JsonLinter();
        try {
            $parser->parse('{"bar": "foo
bar"}');
        } catch (ParseException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertEquals(ParseException::ERROR_NOT_TERMINATED_OR_MULTI_LINE, $e->getCode(), 'Invalid multi-line string should be detected');
        }
    }

    /**
     * @throws null
     */
    public function testErrorAtBeginning()
    {
        $parser = new JsonLinter();
        try {

            $parser->parse('

');
        } catch (ParseException $e) {

            $this->assertEquals(3, $e->getJsonLineNo());
            $this->assertEquals(ParseException::ERROR_EXPECTED_INPUT_TO_BE_SOMETHING_ELSE, $e->getCode(), 'Empty string should be invalid');
        }
    }

    /**
     * @throws ParseException
     * @throws null
     */
    public function testParsesMultiInARow()
    {
        $parser = new JsonLinter();
        foreach ($this->valid as $input) {

            $this->assertEquals(json_decode($input), $parser->parse($input));
        }
    }

    /**
     * @throws ParseException
     */
    public function testDetectsKeyOverrides()
    {
        $parser = new JsonLinter();

        try {

            $parser->parse('{"a":"b", "a":"c"}', JsonLinter::DETECT_KEY_CONFLICTS);
        } catch (DuplicateKeyException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertEquals(DuplicateKeyException::ERROR_PARSE_ERROR_DUPLICATE_KEY, $e->getCode(), 'Duplicate keys should not be allowed');
            $this->assertEquals('a', $e->getKey());
            $this->assertEquals(array('line' => 1, 'key' => 'a'), $e->getArgs());
        }
    }

    /**
     * @throws ParseException
     */
    public function testDetectsKeyOverridesWithEmpty()
    {
        $parser = new JsonLinter();

        try {

            $parser->parse('{
    "":"b",
    "_empty_":"a"
}', JsonLinter::DETECT_KEY_CONFLICTS);
        } catch (DuplicateKeyException $e) {

            $this->assertEquals(3, $e->getJsonLineNo());
            $this->assertEquals(DuplicateKeyException::ERROR_PARSE_ERROR_DUPLICATE_KEY, $e->getCode(), 'Duplicate keys should not be allowed');
            $this->assertEquals('_empty_', $e->getKey());
            $this->assertEquals(array('line' => 3, 'key' => '_empty_'), $e->getArgs());
        }
    }

    /**
     * @throws ParseException
     */
    public function testDuplicateKeys()
    {
        $parser = new JsonLinter();

        $result = $parser->parse('{"a":"b", "a":"c", "a":"d"}', JsonLinter::ALLOW_DUPLICATE_KEYS);
        $this->assertThat($result,
                          $this->logicalAnd(
                              $this->objectHasAttribute('a'),
                              $this->objectHasAttribute('a.1'),
                              $this->objectHasAttribute('a.2')
                          )
        );
    }

    /**
     * @throws ParseException
     */
    public function testDuplicateKeysWithEmpty()
    {
        $parser = new JsonLinter();

        $result = $parser->parse('{"":"a", "_empty_":"b"}', JsonLinter::ALLOW_DUPLICATE_KEYS);
        $this->assertThat($result,
                          $this->logicalAnd(
                              $this->objectHasAttribute('_empty_'),
                              $this->objectHasAttribute('_empty_.1')
                          )
        );
    }

    /**
     * @throws ParseException
     */
    public function testParseToArray()
    {
        $parser = new JsonLinter();

        $json   = '{"one":"a", "two":{"three": "four"}, "": "empty"}';
        $result = $parser->parse($json, JsonLinter::PARSE_TO_ASSOC);
        $this->assertEquals(json_decode($json, true), $result);
    }

    /**
     * @throws null
     */
    public function testFileWithBOM()
    {
        try {
            $parser = new JsonLinter();
            $parser->parse(file_get_contents(dirname(__FILE__) . '/bom.json'));
            $this->fail('BOM should be detected');
        } catch (ParseException $e) {

            $this->assertEquals(ParseException::ERROR_BYTE_ORDER_MARK_DETECTED, $e->getCode());
        }
    }
}
