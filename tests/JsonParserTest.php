<?php

use CMPayments\JsonLint\JsonParser;
use CMPayments\JsonLint\Exceptions\ParsingException;
use CMPayments\JsonLint\Exceptions\DuplicateKeyException;

/**
 * Class JsonParserTest
 */
class JsonParserTest extends PHPUnit_Framework_TestCase
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
        $parser = new JsonParser();
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
        $parser = new JsonParser();
        try {

            $parser->parse($input);
        } catch (ParsingException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertEquals(ParsingException::EXPECTED_INPUT_TO_BE_SOMETHING_ELSE, $e->getCode(), 'Invalid string should be detected');
        }
    }

    /**
     * @dataProvider provideOtherValuesThanString
     */
    public function testOtherStringValues($input)
    {
        $parser = new JsonParser();
        try {

            $parser->parse($input);
        } catch (ParsingException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertEquals(ParsingException::NOT_A_STRING, $e->getCode(), 'Invalid type should be detected');
        }
    }

    /**
     * @throws null
     */
    public function testObjectInsteadOfString()
    {
        $parser = new JsonParser();
        try {

            $parser->parse(new stdClass());
        } catch (ParsingException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertEquals(ParsingException::NOT_A_STRING, $e->getCode(), 'Invalid type should be detected, becuase an object was given');
        }
    }

    /**
     * @throws null
     */
    public function testErrorOnTrailingComma_1()
    {
        $parser = new JsonParser();
        try {
            $parser->parse('{"foo":"bar",}');
        } catch (ParsingException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertContains($e->getItemFromVariableArray(ParsingException::APPEND_TRAILING_COMMA_ERROR), $e->getMessage(), 'Invalid trailing comma should be detected');
        }
    }

    public function testErrorOnTrailingComma_2()
    {
        $parser = new JsonParser();
        try {
            $parser->parse('{
    "foo":"bar",
}');
        } catch (ParsingException $e) {

            $this->assertEquals(2, $e->getJsonLineNo());
            $this->assertContains($e->getItemFromVariableArray(ParsingException::APPEND_TRAILING_COMMA_ERROR), $e->getMessage(), 'Invalid trailing comma should be detected');
        }
    }

    /**
     * @throws null
     */
    public function testErrorOnInvalidQuotes()
    {
        $parser = new JsonParser();
        try {
            $parser->parse('{
    "foo": \'bar\',
}');
        } catch (ParsingException $e) {

            $this->assertEquals(2, $e->getJsonLineNo());
            $this->assertEquals(ParsingException::USED_SINGLE_QUOTES, $e->getCode(), 'Invalid quotes for string should be detected');
        }
    }

    /**
     * @throws null
     */
    public function testErrorOnUnescapedBackslash()
    {
        $parser = new JsonParser();
        try {

            $parser->parse('{
    "foo": "bar\z",
}');
        } catch (ParsingException $e) {

            $this->assertEquals(2, $e->getJsonLineNo());
            $this->assertEquals(ParsingException::UNESCAPED_BACKSLASH, $e->getCode(), 'Invalid unescaped string should be detected');
        }
    }

    /**
     * @throws null
     */
    public function testErrorOnUnterminatedString()
    {
        $parser = new JsonParser();
        try {

            $parser->parse('{"bar": "foo}');
        } catch (ParsingException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertEquals(ParsingException::NOT_TERMINATED_OR_MULTI_LINE, $e->getCode(), 'Invalid unterminated string should be detected');
        }
    }

    /**
     * @throws null
     */
    public function testErrorOnMultilineString()
    {
        $parser = new JsonParser();
        try {
            $parser->parse('{"bar": "foo
bar"}');
        } catch (ParsingException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertEquals(ParsingException::NOT_TERMINATED_OR_MULTI_LINE, $e->getCode(), 'Invalid multi-line string should be detected');
        }
    }

    /**
     * @throws null
     */
    public function testErrorAtBeginning()
    {
        $parser = new JsonParser();
        try {

            $parser->parse('

');
        } catch (ParsingException $e) {

            $this->assertEquals(3, $e->getJsonLineNo());
            $this->assertEquals(ParsingException::EXPECTED_INPUT_TO_BE_SOMETHING_ELSE, $e->getCode(), 'Empty string should be invalid');
        }
    }

    /**
     * @throws ParsingException
     * @throws null
     */
    public function testParsesMultiInARow()
    {
        $parser = new JsonParser();
        foreach ($this->valid as $input) {

            $this->assertEquals(json_decode($input), $parser->parse($input));
        }
    }

    /**
     * @throws ParsingException
     */
    public function testDetectsKeyOverrides()
    {
        $parser = new JsonParser();

        try {

            $parser->parse('{"a":"b", "a":"c"}', JsonParser::DETECT_KEY_CONFLICTS);
        } catch (DuplicateKeyException $e) {

            $this->assertEquals(1, $e->getJsonLineNo());
            $this->assertEquals(DuplicateKeyException::PARSE_ERROR_DUPLICATE_KEY, $e->getCode(), 'Duplicate keys should not be allowed');
            $this->assertEquals('a', $e->getKey());
            $this->assertEquals(array('line' => 1, 'key' => 'a'), $e->getArgs());
        }
    }

    /**
     * @throws ParsingException
     */
    public function testDetectsKeyOverridesWithEmpty()
    {
        $parser = new JsonParser();

        try {

            $parser->parse('{
    "":"b",
    "_empty_":"a"
}', JsonParser::DETECT_KEY_CONFLICTS);
        } catch (DuplicateKeyException $e) {

            $this->assertEquals(3, $e->getJsonLineNo());
            $this->assertEquals(DuplicateKeyException::PARSE_ERROR_DUPLICATE_KEY, $e->getCode(), 'Duplicate keys should not be allowed');
            $this->assertEquals('_empty_', $e->getKey());
            $this->assertEquals(array('line' => 3, 'key' => '_empty_'), $e->getArgs());
        }
    }

    /**
     * @throws ParsingException
     */
    public function testDuplicateKeys()
    {
        $parser = new JsonParser();

        $result = $parser->parse('{"a":"b", "a":"c", "a":"d"}', JsonParser::ALLOW_DUPLICATE_KEYS);
        $this->assertThat($result,
                          $this->logicalAnd(
                              $this->objectHasAttribute('a'),
                              $this->objectHasAttribute('a.1'),
                              $this->objectHasAttribute('a.2')
                          )
        );
    }

    /**
     * @throws ParsingException
     */
    public function testDuplicateKeysWithEmpty()
    {
        $parser = new JsonParser();

        $result = $parser->parse('{"":"a", "_empty_":"b"}', JsonParser::ALLOW_DUPLICATE_KEYS);
        $this->assertThat($result,
                          $this->logicalAnd(
                              $this->objectHasAttribute('_empty_'),
                              $this->objectHasAttribute('_empty_.1')
                          )
        );
    }

    /**
     * @throws ParsingException
     */
    public function testParseToArray()
    {
        $parser = new JsonParser();

        $json   = '{"one":"a", "two":{"three": "four"}, "": "empty"}';
        $result = $parser->parse($json, JsonParser::PARSE_TO_ASSOC);
        $this->assertEquals(json_decode($json, true), $result);
    }

    /**
     * @throws null
     */
    public function testFileWithBOM()
    {
        try {
            $parser = new JsonParser();
            $parser->parse(file_get_contents(dirname(__FILE__) . '/bom.json'));
            $this->fail('BOM should be detected');
        } catch (ParsingException $e) {

            $this->assertEquals(ParsingException::BYTE_ORDER_MARK_DETECTED, $e->getCode());
        }
    }
}
