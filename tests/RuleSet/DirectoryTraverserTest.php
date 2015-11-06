<?php

namespace SensioLabs\DeprecationDetector\Tests\RuleSet;

use SensioLabs\DeprecationDetector\RuleSet\DirectoryTraverser;

class DirectoryTraverserTest extends \PHPUnit_Framework_TestCase
{
    public function testClassIsInitializable()
    {
        $ruleSetAstMapGenerator = $this->prophesize('SensioLabs\DeprecationDetector\AstMap\AstMapGenerator');
        $deprecationParser = $this->prophesize('SensioLabs\DeprecationDetector\Parser\DeprecationParser');

        $directoryTraverser = new DirectoryTraverser(
            $ruleSetAstMapGenerator->reveal(),
            $deprecationParser->reveal()
        );

        $this->assertInstanceOf('SensioLabs\DeprecationDetector\RuleSet\DirectoryTraverser', $directoryTraverser);
    }

    public function testTraverse()
    {
        /** @TODO: Refactor DirectoryTraverser and Finder\DeprecationUsageFinder */
        $this->markTestSkipped();

        $aPhpFileInfo = $this->prophesize('SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo');
        $aPhpFileInfo->hasDeprecations()->willReturn(true);
        $aPhpFileInfo->classDeprecations()->willReturn(array());
        $aPhpFileInfo->methodDeprecations()->willReturn(array());
        $aPhpFileInfo->interfaceDeprecations()->willReturn(array());

        $anotherPhpFileInfo = $this->prophesize('SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo');
        $anotherPhpFileInfo->hasDeprecations()->willReturn(false);

        $deprecationFileFinder = $this->prophesize('SensioLabs\DeprecationDetector\Finder\ParsedPhpFileFinder');
        $deprecationFileFinder->in('some_dir')->willReturn(array(
            $aPhpFileInfo->reveal(),
            $anotherPhpFileInfo->reveal(),
        ));

        $ruleSetAstMapGenerator = $this->prophesize('SensioLabs\DeprecationDetector\AstMap\AstMapGenerator');
        $deprecationParser = $this->prophesize('SensioLabs\DeprecationDetector\Parser\DeprecationParser');

        $ruleSet = $this->prophesize('SensioLabs\DeprecationDetector\RuleSet\RuleSet');
        $ruleSet->merge($aPhpFileInfo->reveal())->shouldBeCalled();
        $ruleSet->merge($anotherPhpFileInfo->reveal())->shouldNotBeCalled();

        $directoryTraverser = new DirectoryTraverser(
            $ruleSetAstMapGenerator->reveal(),
            $deprecationParser->reveal()
        );
        $directoryTraverser->traverse('some_dir', $ruleSet->reveal());
    }
}
