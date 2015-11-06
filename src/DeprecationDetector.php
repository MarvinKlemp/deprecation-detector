<?php

namespace SensioLabs\DeprecationDetector;

use SensioLabs\DeprecationDetector\AstMap\AstMapGenerator;
use SensioLabs\DeprecationDetector\Console\Output\DefaultProgressOutput;
use SensioLabs\DeprecationDetector\AstMap\AstMapUsageTraverser;
use SensioLabs\DeprecationDetector\RuleSet\Loader\LoaderInterface;
use SensioLabs\DeprecationDetector\Violation\Violation;
use SensioLabs\DeprecationDetector\Violation\ViolationDetector;
use SensioLabs\DeprecationDetector\Violation\Renderer\RendererInterface;
use \ArrayIterator;

class DeprecationDetector
{
    /**
     * @var LoaderInterface
     */
    private $ruleSetLoader;

    /**
     * @var AstMapUsageTraverser
     */
    private $astMapUsageTraverser;

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
     * @param LoaderInterface           $ruleSetLoader
     * @param AstMapUsageTraverser      $astMapUsageTraverser
     * @param ViolationDetector         $violationDetector
     * @param RendererInterface         $renderer
     * @param DefaultProgressOutput     $output
     */
    public function __construct(
        LoaderInterface $ruleSetLoader,
        AstMapUsageTraverser $astMapUsageTraverser,
        ViolationDetector $violationDetector,
        RendererInterface $renderer,
        DefaultProgressOutput $output
    ) {
        $this->ruleSetLoader = $ruleSetLoader;
        $this->astMapUsageTraverser = $astMapUsageTraverser;
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
        $ruleSet = $this->ruleSetLoader->loadRuleSet($ruleSetArg);
        $this->output->endRuleSetGeneration();


        $this->output->startUsageDetection();
        /** @var ArrayIterator $files */
        $files = $this->astMapUsageTraverser->traverse($sourceArg);
        $violations = $this->violationDetector->getViolations($ruleSet, $files);
        $this->output->endUsageDetection();

        $this->output->startOutputRendering();
        $this->renderer->renderViolations($violations);
        /*if ($files->hasParserErrors()) {
            $this->renderer->renderParserErrors($files->getParserErrors());
        }*/
        $this->output->endOutputRendering();

        $this->output->endProgress($files->count(), count($violations));

        return $violations;
    }
}
