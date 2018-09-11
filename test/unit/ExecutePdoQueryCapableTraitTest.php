<?php

namespace RebelCode\Storage\Resource\Pdo\UnitTest;

use PDO;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\Pdo\ExecutePdoQueryCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ExecutePdoQueryCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\Pdo\ExecutePdoQueryCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param PDO|null $pdo Optional PDO instance.
     *
     * @return MockObject|TestSubject
     */
    public function createInstance(PDO $pdo = null)
    {
        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                        ->setMethods(['_getPdo']);

        $mock = $builder->getMockForTrait();
        $mock->method('_getPdo')->willReturn($pdo);

        return $mock;
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
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests the query execution method.
     *
     * @since [*next-version*]
     */
    public function testExecutePdoQuery()
    {
        $query = uniqid('query-');
        $args = [
            uniqid('var-') => uniqid('value-'),
            uniqid('var-') => uniqid('value-'),
            uniqid('var-') => uniqid('value-'),
        ];

        $statement = $this->getMock('\PDOStatement', ['execute']);
        $statement->expects($this->once())
                  ->method('execute')
                  ->with($args)
                  ->willReturn(null);

        $pdo = $this->getMock('\PDO', ['prepare'], ['sqlite::memory:']);
        $pdo->expects($this->once())
            ->method('prepare')
            ->with($query)
            ->willReturn($statement);

        $subject = $this->createInstance($pdo);
        $reflect = $this->reflect($subject);

        $this->assertSame(
            $statement,
            $reflect->_executePdoQuery($query, $args),
            'Expected and retrieved statements are not the same.'
        );
    }
}
