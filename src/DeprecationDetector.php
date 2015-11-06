<?php

namespace SensioLabs\DeprecationDetector;

use SensioLabs\DeprecationDetector\AstMap\AstMapGenerator;
use SensioLabs\DeprecationDetector\Console\Output\DefaultProgressOutput;
use SensioLabs\DeprecationDetector\Finder\ParsedPhpFileFinder;
use SensioLabs\DeprecationDetector\RuleSet\Loader\LoaderInterface;
use SensioLabs\DeprecationDetector\Violation\Violation;
use SensioLabs\DeprecationDetector\Violation\ViolationDetector;
use SensioLabs\DeprecationDetector\Violation\Renderer\RendererInterface;

class DeprecationDetector
{
    /**
     * @var AstMapGenerator
     */
    private $astMapGenerator;

    /**
     * @var LoaderInterface
     */
    private $ruleSetLoader;

    /**
     * @var ParsedPhpFileFinder
     */
    private $deprecationFinder;

    /**
     * @var ViolationDetector
     */
    private $violationDetector;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var DefaultProgressOutput
     */
    private $output;

    /**
     * @param AstMapGenerator       $astMapGenerator
     * @param LoaderInterface       $ruleSetLoader
     * @param ParsedPhpFileFinder   $deprecationFinder
     * @param ViolationDetector     $violationDetector
     * @param RendererInterface     $renderer
     * @param DefaultProgressOutput $output
     */
    public function __construct(
        AstMapGenerator $astMapGenerator,
        LoaderInterface $ruleSetLoader,
        ParsedPhpFileFinder $deprecationFinder,
        ViolationDetector $violationDetector,
        RendererInterface $renderer,
        DefaultProgressOutput $output
    ) {
        $this->astMapGenerator = $astMapGenerator;
        $this->ruleSetLoader = $ruleSetLoader;
        $this->deprecationFinder = $deprecationFinder;
        $this->violationDetector = $violationDetector;
        $this->renderer = $renderer;
        $this->output = $output;
    }

    /**
     * @param string $sourceArg
     * @param string $ruleSetArg
     *
     * @return Violation[]
     *
     * @throws \Exception
     */
    public function checkForDeprecations($sourceArg, $ruleSetArg)
    {
        $this->output->startProgress();

        $this->output->startRuleSetGeneration();

        // start RuleSetAstMapGeneration
        $map = $this->astMapGenerator->generateAstMap($sourceArg);
        // end RuleSetAstMapGeneration

        $ruleSet = $this->ruleSetLoader->loadRuleSet($ruleSetArg);
        $this->output->endRuleSetGeneration();

        $this->output->startUsageDetection();

        /** @var ParsedPhpFileFinder $files */
        $files = $this->deprecationFinder->in($sourceArg);
        $violations = $this->violationDetector->getViolations($ruleSet, $files);
        $this->output->endUsageDetection();

        $this->output->startOutputRendering();
        $this->renderer->renderViolations($violations);
        if ($files->hasParserErrors()) {
            $this->renderer->renderParserErrors($files->getParserErrors());
        }
        $this->output->endOutputRendering();

        $this->output->endProgress($files->count(), count($violations));

        return $violations;
    }
}
