<?php
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2010-2015 Mike van Riel<mike@phpdoc.org>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\Void;

/**
 * @coversDefaultClass \phpDocumentor\Reflection\DocBlock\Tags\Method
 * @covers ::<private>
 */
class MethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Method('myMethod');

        $this->assertSame('method', $fixture->getName());
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::isStatic
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getName
     */
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $arguments = [
            ['name' => 'argument1', 'type' => new String_()],
            ['name' => 'argument2', 'type' => new Object_()]
        ];
        $fixture = new Method('myMethod', $arguments, new Void(), true, new Description('My Description'));

        $this->assertSame(
            '@method static void myMethod(string $argument1, object $argument2) My Description',
            $fixture->render()
        );
    }

    /**
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::__construct
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::render
     */
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Method('myMethod');

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    /**
     * @covers ::__construct
     * @covers ::getMethodName
     */
    public function testHasMethodName()
    {
        $expected = 'myMethod';

        $fixture = new Method($expected);

        $this->assertSame($expected, $fixture->getMethodName());
    }

    /**
     * @covers ::__construct
     * @covers ::getArguments
     */
    public function testHasArguments()
    {
        $arguments = [
            [ 'name' => 'argument1', 'type' => new String_() ]
        ];

        $fixture = new Method('myMethod', $arguments);

        $this->assertSame($arguments, $fixture->getArguments());
    }

    /**
     * @covers ::__construct
     * @covers ::getArguments
     */
    public function testArgumentsMayBePassedAsString()
    {
        $arguments = ['argument1'];
        $expected = [
            [ 'name' => $arguments[0], 'type' => new Void() ]
        ];

        $fixture = new Method('myMethod', $arguments);

        $this->assertEquals($expected, $fixture->getArguments());
    }

    /**
     * @covers ::__construct
     * @covers ::getArguments
     */
    public function testArgumentTypeCanBeInferredAsVoid()
    {
        $arguments = [ [ 'name' => 'argument1' ] ];
        $expected = [
            [ 'name' => $arguments[0]['name'], 'type' => new Void() ]
        ];

        $fixture = new Method('myMethod', $arguments);

        $this->assertEquals($expected, $fixture->getArguments());
    }

    /**
     * @covers ::__construct
     * @covers ::getReturnType
     */
    public function testHasReturnType()
    {
        $expected = new String_();

        $fixture = new Method('myMethod', [], $expected);

        $this->assertSame($expected, $fixture->getReturnType());
    }

    /**
     * @covers ::__construct
     * @covers ::getReturnType
     */
    public function testReturnTypeCanBeInferredAsVoid()
    {
        $fixture = new Method('myMethod', []);

        $this->assertEquals(new Void(), $fixture->getReturnType());
    }

    /**
     * @covers ::__construct
     * @covers ::isStatic
     */
    public function testMethodCanBeStatic()
    {
        $expected = false;
        $fixture = new Method('myMethod', [], null, $expected);
        $this->assertSame($expected, $fixture->isStatic());

        $expected = true;
        $fixture = new Method('myMethod', [], null, $expected);
        $this->assertSame($expected, $fixture->isStatic());
    }

    /**
     * @covers ::__construct
     * @covers \phpDocumentor\Reflection\DocBlock\Tags\BaseTag::getDescription
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     */
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Method('myMethod', [], null, false, $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     * @uses   \phpDocumentor\Reflection\DocBlock\Description
     * @uses   \phpDocumentor\Reflection\DocBlock\Tags\Method::isStatic
     */
    public function testStringRepresentationIsReturned()
    {
        $arguments = [
            ['name' => 'argument1', 'type' => new String_()],
            ['name' => 'argument2', 'type' => new Object_()]
        ];
        $fixture = new Method('myMethod', $arguments, new Void(), true, new Description('My Description'));

        $this->assertSame(
            'static void myMethod(string $argument1, object $argument2) My Description',
            (string)$fixture
        );
    }

    /**
     * @covers ::create
     * @uses \phpDocumentor\Reflection\DocBlock\Tags\Method::<public>
     * @uses \phpDocumentor\Reflection\DocBlock\DescriptionFactory
     * @uses \phpDocumentor\Reflection\TypeResolver
     * @uses \phpDocumentor\Reflection\DocBlock\Description
     * @uses \phpDocumentor\Reflection\Fqsen
     * @uses \phpDocumentor\Reflection\Types\Context
     */
    public function testFactoryMethod()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $description  = new Description('My Description');
        $expectedArguments = [
            [ 'name' => 'argument1', 'type' => new String_() ],
            [ 'name' => 'argument2', 'type' => new Void() ]
        ];

        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Method::create(
            'static void myMethod(string $argument1, $argument2) My Description',
            $resolver,
            $descriptionFactory,
            $context
        );

        $this->assertSame('static void myMethod(string $argument1, void $argument2) My Description', (string)$fixture);
        $this->assertSame('myMethod', $fixture->getMethodName());
        $this->assertEquals($expectedArguments, $fixture->getArguments());
        $this->assertInstanceOf(Void::class, $fixture->getReturnType());
        $this->assertSame($description, $fixture->getDescription());
    }

    /**
     * @covers ::create
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryMethodFailsIfBodyIsNotString()
    {
        Method::create([]);
    }

    /**
     * @covers ::create
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryMethodFailsIfBodyIsEmpty()
    {
        Method::create('');
    }

    /**
     * @covers ::create
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryMethodReturnsNullIfBodyIsIncorrect()
    {
        $this->assertNull(Method::create('body('));
    }

    /**
     * @covers ::create
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryMethodFailsIfResolverIsNull()
    {
        Method::create('body');
    }

    /**
     * @covers ::create
     * @expectedException \InvalidArgumentException
     */
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull()
    {
        Method::create('body', new TypeResolver());
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCreationFailsIfBodyIsNotString()
    {
        new Method([]);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCreationFailsIfBodyIsEmpty()
    {
        new Method('');
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCreationFailsIfStaticIsNotBoolean()
    {
        new Method('body', [], null, []);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testCreationFailsIfArgumentRecordContainsInvalidEntry()
    {
        new Method('body', [ [ 'name' => 'myName', 'unknown' => 'nah' ] ]);
    }
}
