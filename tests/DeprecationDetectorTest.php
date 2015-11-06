<?php

namespace SensioLabs\DeprecationDetector\Tests;

use Prophecy\Argument;
use SensioLabs\DeprecationDetector\DeprecationDetector;

class DeprecationDetectorTest extends \PHPUnit_Framework_TestCase
{
    public function testClassIsInitializable()
    {
        $ruleSetLoader = $this->prophesize('SensioLabs\DeprecationDetector\RuleSet\Loader\LoaderInterface');
        $deprecationUsageFinder = $this->prophesize('SensioLabs\DeprecationDetector\AstMap\AstMapUsageTraverser');
        $violationDetector = $this->prophesize('SensioLabs\DeprecationDetector\Violation\ViolationDetector');
        $renderer = $this->prophesize('SensioLabs\DeprecationDetector\Violation\Renderer\RendererInterface');
        $defaultOutput = $this->prophesize(
            'SensioLabs\DeprecationDetector\Console\Output\DefaultProgressOutput'
        );

        $detector = new DeprecationDetector(
            $ruleSetLoader->reveal(),
            $deprecationUsageFinder->reveal(),
            $violationDetector->reveal(),
            $renderer->reveal(),
            $defaultOutput->reveal()
        );

        $this->assertInstanceOf('SensioLabs\DeprecationDetector\DeprecationDetector', $detector);
    }

    public function testCheckForDeprecations()
    {
        $this->markTestSkipped();

        $sourceArg = 'path/to/ruleset';
        $ruleSetArg = 'path/to/source/code';
        $fileCount = 10;
        $violationCount = 2;

        $files = $this->prophesize('\ArrayIterator');

        $ruleSet = $this->prophesize('SensioLabs\DeprecationDetector\RuleSet\RuleSet');
        $ruleSetLoader = $this->prophesize('SensioLabs\DeprecationDetector\RuleSet\Loader\LoaderInterface');
        $ruleSetLoader->loadRuleSet($ruleSetArg)->willReturn($ruleSet->reveal());

        $deprecationUsageFinder = $this->prophesize('SensioLabs\DeprecationDetector\Finder\DeprecationUsageFinder');
        $deprecationUsageFinder->find($sourceArg)->willReturn($files->reveal());
        //$deprecationFinder->hasParserErrors()->willReturn(false);
        $files->count()->willReturn($fileCount);

        $aViolation = $this->prophesize('SensioLabs\DeprecationDetector\Violation\Violation');
        $anotherViolation = $this->prophesize('SensioLabs\DeprecationDetector\Violation\Violation');
        $violations = array(
            $aViolation->reveal(),
            $anotherViolation->reveal(),
        );

        $violationDetector = $this->prophesize('SensioLabs\DeprecationDetector\Violation\ViolationDetector');
        $violationDetector->getViolations($ruleSet->reveal(), $files->reveal())->willReturn($violations);

        $renderer = $this->prophesize('SensioLabs\DeprecationDetector\Violation\Renderer\RendererInterface');
        $renderer->renderViolations($violations)->shouldBeCalled();
        $renderer->renderParserErrors(Argument::any())->shouldNotBeCalled();

        $defaultOutput = $this->prophesize(
            'SensioLabs\DeprecationDetector\Console\Output\DefaultProgressOutput'
        );
        $defaultOutput->startProgress()->shouldBeCalled();
        $defaultOutput->startRuleSetGeneration()->shouldBeCalled();
        $defaultOutput->endRuleSetGeneration()->shouldBeCalled();
        $defaultOutput->startUsageDetection()->shouldBeCalled();
        $defaultOutput->endUsageDetection()->shouldBeCalled();
        $defaultOutput->startOutputRendering()->shouldBeCalled();
        $defaultOutput->endOutputRendering()->shouldBeCalled();
        $defaultOutput->endProgress($fileCount, $violationCount)->shouldBeCalled();

        $detector = new DeprecationDetector(
            $ruleSetLoader->reveal(),
            $deprecationUsageFinder->reveal(),
            $violationDetector->reveal(),
            $renderer->reveal(),
            $defaultOutput->reveal()
        );

        $this->assertSame($violations, $detector->checkForDeprecations($sourceArg, $ruleSetArg));
    }

    public function testCheckForDeprecationsRendersParserErrorsIfThereAreAny()
    {
        $this->markTestSkipped();

        $sourceArg = 'path/to/ruleset';
        $ruleSetArg = 'path/to/source/code';
        $parserErrors = array();
        $fileCount = 10;
        $violationCount = 2;

        $files = $this->prophesize('\ArrayIterator');

        $ruleSet = $this->prophesize('SensioLabs\DeprecationDetector\RuleSet\RuleSet');
        $ruleSetLoader = $this->prophesize('SensioLabs\DeprecationDetector\RuleSet\Loader\LoaderInterface');
        $ruleSetLoader->loadRuleSet($ruleSetArg)->willReturn($ruleSet->reveal());

        $deprecationUsageFinder = $this->prophesize('SensioLabs\DeprecationDetector\Finder\DeprecationUsageFinder');
        $deprecationUsageFinder->find($sourceArg)->willReturn($files->reveal());
        //$deprecationUsageFinder->hasParserErrors()->willReturn(true);
        $files->count()->willReturn($fileCount);
        //$deprecationUsageFinder->getParserErrors()->willReturn($parserErrors);

        $aViolation = $this->prophesize('SensioLabs\DeprecationDetector\Violation\Violation');
        $anotherViolation = $this->prophesize('SensioLabs\DeprecationDetector\Violation\Violation');
        $violations = array(
            $aViolation->reveal(),
            $anotherViolation->reveal(),
        );

        $violationDetector = $this->prophesize('SensioLabs\DeprecationDetector\Violation\ViolationDetector');
        $violationDetector->getViolations($ruleSet->reveal(), $files->reveal())->willReturn($violations);

        $renderer = $this->prophesize('SensioLabs\DeprecationDetector\Violation\Renderer\RendererInterface');
        $renderer->renderViolations($violations)->shouldBeCalled();
        //$renderer->renderParserErrors($parserErrors)->shouldBeCalled();

        $defaultOutput = $this->prophesize(
            'SensioLabs\DeprecationDetector\Console\Output\DefaultProgressOutput'
        );
        $defaultOutput->startProgress()->shouldBeCalled();
        $defaultOutput->startRuleSetGeneration()->shouldBeCalled();
        $defaultOutput->endRuleSetGeneration()->shouldBeCalled();
        $defaultOutput->startUsageDetection()->shouldBeCalled();
        $defaultOutput->endUsageDetection()->shouldBeCalled();
        $defaultOutput->startOutputRendering()->shouldBeCalled();
        $defaultOutput->endOutputRendering()->shouldBeCalled();
        $defaultOutput->endProgress($fileCount, $violationCount)->shouldBeCalled();

        $detector = new DeprecationDetector(
            $ruleSetLoader->reveal(),
            $deprecationUsageFinder->reveal(),
            $violationDetector->reveal(),
            $renderer->reveal(),
            $defaultOutput->reveal()
        );

        $this->assertSame($violations, $detector->checkForDeprecations($sourceArg, $ruleSetArg));
    }
}
