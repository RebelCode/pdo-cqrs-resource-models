<?php

namespace RebelCode\Storage\Resource\Pdo\UnitTest;

use Dhii\Expression\ExpressionInterface;
use InvalidArgumentException;
use OutOfRangeException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Pdo\GetPdoExpressionHashMapCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class GetPdoExpressionHashMapCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\GetPdoExpressionHashMapCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return MockObject|TestSubject
     */
    public function createInstance()
    {
        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                        ->setMethods(
                            [
                                '_normalizeString',
                                '_getPdoValueHashString',
                                '_createOutOfRangeException',
                                '__',
                            ]
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_getPdoValueHashString')->willReturnArgument(0);
        $mock->method('_normalizeString')->willReturnCallback(
            function ($arg) {
                return strval($arg);
            }
        );
        $mock->method('_createOutOfRangeException')->willReturnCallback(
            function ($m, $c, $p, $v) {
                return new OutOfRangeException($m, $c, $p);
            }
        );
        $mock->method('__')->willReturnArgument(0);

        return $mock;
    }

    /**
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type  The expression type.
     * @param array  $terms The expression terms.
     *
     * @return ExpressionInterface The created expression instance.
     */
    public function createExpression($type, $terms)
    {
        return $this->mock('Dhii\Expression\ExpressionInterface')
                    ->getType($type)
                    ->getTerms($terms)
                    ->new();
    }

    /**
     * Creates an expression term mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type The term type.
     *
     * @return ExpressionInterface The created expression term instance.
     */
    public function createTerm($type)
    {
        return $this->mock('Dhii\Expression\TermInterface')
                    ->getType($type)
                    ->new();
    }

    /**
     * Creates a literal term mock instance.
     *
     * @since [*next-version*]
     *
     * @param mixed $value The term value.
     *
     * @return ExpressionInterface The created literal term instance.
     */
    public function createLiteralTerm($value)
    {
        return $this->mock('Dhii\Expression\LiteralTermInterface')
                    ->getType('literal')
                    ->getValue($value)
                    ->new();
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'An instance of the test subject could not be created'
        );
    }

    /**
     * Tests the expression value hash map getter method to assert whether the retrieved hash map contains the
     * correct value to hash mappings.
     *
     * @since [*next-version*]
     */
    public function testGetExpressionValueHashMap()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expression = $this->createExpression(
            'plus',
            [
                $this->createExpression(
                    'mult',
                    [
                        $this->createLiteralTerm('a'),
                        $this->createLiteralTerm('b'),
                    ]
                ),
                $this->createExpression(
                    'mult',
                    [
                        $this->createLiteralTerm('c'),
                        $this->createTerm('d'),
                    ]
                ),
            ]
        );

        $result = $reflect->_getPdoExpressionHashMap($expression, []);

        $this->assertArrayHasKey('a', $result, 'Retrieved hash map does not contain hash for "a".');
        $this->assertArrayHasKey('b', $result, 'Retrieved hash map does not contain hash for "b".');
        $this->assertArrayHasKey('c', $result, 'Retrieved hash map does not contain hash for "c".');
        $this->assertArrayNotHasKey('d', $result, 'Retrieved hash map incorrectly hash hash for "d".');
    }

    /**
     * Tests the expression value hash map getter method with a term condition to assert whether the retrieved hash
     * map contains the hash for the term's value.
     *
     * @since [*next-version*]
     */
    public function testGetExpressionValueHashMapTerm()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expression = $this->createLiteralTerm('a');

        $result = $reflect->_getPdoExpressionHashMap($expression, []);

        $this->assertArrayHasKey('a', $result, 'Retrieved hash map does not contain hash for "a".');
    }

    /**
     * Tests thee expression value hash map getter method with an ignore list to assert whether the retrieved hash map
     * does not contain mappings for the ignored values.
     *
     * @since [*next-version*]
     */
    public function testGetExpressionValueHashMapIgnore()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expression = $this->createExpression(
            'plus',
            [
                $this->createExpression(
                    'mult',
                    [
                        $this->createLiteralTerm('a'),
                        $this->createLiteralTerm('b'),
                    ]
                ),
                $this->createExpression(
                    'mult',
                    [
                        $this->createLiteralTerm('c'),
                        $this->createLiteralTerm('d'),
                    ]
                ),
            ]
        );
        $ignore = ['b', 'd'];

        $result = $reflect->_getPdoExpressionHashMap($expression, $ignore);

        $this->assertArrayHasKey('a', $result, 'Retrieved hash map does not contain hash for "a".');
        $this->assertArrayHasKey('c', $result, 'Retrieved hash map does not contain hash for "c".');

        $this->assertArrayNotHasKey('b', $result, 'Retrieved hash contains hash for "b".');
        $this->assertArrayNotHasKey('d', $result, 'Retrieved hash contains hash for "d".');
    }

    /**
     * Tests the expression value hash map getter method with a term condition to assert whether the retrieved hash
     * map does not contain the hash for the term's value when its ignored.
     *
     * @since [*next-version*]
     */
    public function testGetExpressionValueHashMapTermIgnore()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expression = $this->createLiteralTerm('a');

        $result = $reflect->_getPdoExpressionHashMap($expression, ['a']);

        $this->assertArrayNotHasKey('a', $result, 'Retrieved hash map contains hash for "a".');
    }

    /**
     * Tests the expression value hash map getter method when the string normalization fails to assert whether the
     * correct wrapping exception is thrown with the string normalization exception as an inner exception.
     *
     * @since [*next-version*]
     */
    public function testGetExpressionValueHashMapNormalizeStringException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expression = $this->createLiteralTerm('');

        $subject->method('_normalizeString')->willThrowException($inner = new InvalidArgumentException());

        try {
            $reflect->_getPdoExpressionHashMap($expression);

            $this->fail('Expected an OutOfRangeException to be thrown.');
        } catch (OutOfRangeException $outOfRangeException) {
            $this->assertSame(
                $inner,
                $outOfRangeException->getPrevious(),
                'Expected and actual inner exceptions do not match.'
            );
        }
    }

    /**
     * Tests the expression value hash map getter method when the value hashing fails to assert whether the correct
     * wrapping exception is thrown with the value hashing exception as an inner exception.
     *
     * @since [*next-version*]
     */
    public function testGetExpressionValueHashMapHashStringException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expression = $this->createLiteralTerm('');

        $subject->method('_getPdoValueHashString')->willThrowException($inner = new InvalidArgumentException());

        try {
            $reflect->_getPdoExpressionHashMap($expression);

            $this->fail('Expected an OutOfRangeException to be thrown.');
        } catch (OutOfRangeException $outOfRangeException) {
            $this->assertSame(
                $inner,
                $outOfRangeException->getPrevious(),
                'Expected and actual inner exceptions do not match.'
            );
        }
    }
}
