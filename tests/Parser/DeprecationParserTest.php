<?php

namespace SensioLabs\DeprecationDetector\Tests\Parser;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use Prophecy\Argument;
use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\Parser\DeprecationParser;
use SensioLabs\DeprecationDetector\Visitor\DeprecationVisitorInterface;

class DeprecationParserTest extends \PHPUnit_Framework_TestCase
{
    public function testClassIsInitializable()
    {
        $deprecationParser = new DeprecationParser(
            $this->prophesize(Parser::class)->reveal(),
            [],
            $this->prophesize(NodeTraverser::class)->reveal()
        );

        $this->assertInstanceOf(DeprecationParser::class, $deprecationParser);
    }

    public function testAddDeprecationVisitorCallsAddVisitor()
    {
        $visitor = $this->prophesize(DeprecationVisitorInterface::class);
        $visitor = $visitor->reveal();

        $baseTraverser = $this->prophesize(NodeTraverser::class);
        $baseTraverser->addVisitor($visitor)->shouldBeCalled();

        $deprecationParser = new DeprecationParser(
            $this->prophesize(Parser::class)->reveal(),
            [],
            $baseTraverser->reveal()
        );
        $deprecationParser->addDeprecationVisitor($visitor);
    }

    public function testParseFile()
    {
        $contents = '';
        $phpFileInfo = $this->prophesize(PhpFileInfo::class);
        $phpFileInfo->getContents()->shouldBeCalled()->willReturn($contents);
        $phpFileInfo = $phpFileInfo->reveal();

        $visitor = $this->prophesize(DeprecationVisitorInterface::class);
        $visitor->setPhpFileInfo($phpFileInfo)->shouldBeCalled();
        $anotherVisitor = $this->prophesize(DeprecationVisitorInterface::class);
        $anotherVisitor->setPhpFileInfo($phpFileInfo)->shouldBeCalled();

        $baseTraverser = $this->prophesize(NodeTraverser::class);
        $baseTraverser->addVisitor($visitor)->shouldBeCalled();
        $baseTraverser->addVisitor($anotherVisitor)->shouldBeCalled();
        $baseTraverser->traverse(Argument::any())->shouldBeCalled();

        $parser = $this->prophesize(Parser::class);
        $parser->parse($contents)->shouldBeCalled()->willReturn([]);

        $deprecationParser = new DeprecationParser(
            $parser->reveal(),
            [$visitor->reveal(), $anotherVisitor->reveal()],
            $baseTraverser->reveal()
        );
        $deprecationParser->parseFile($phpFileInfo);
    }
}
