<?php

namespace SensioLabs\DeprecationDetector\Violation;

use SensioLabs\DeprecationDetector\Finder\ParsedPhpFileFinder;
use SensioLabs\DeprecationDetector\ProgressEvent;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\Violation\Violation as BaseViolation;
use SensioLabs\DeprecationDetector\Violation\ViolationChecker\ViolationCheckerInterface;
use SensioLabs\DeprecationDetector\Violation\ViolationFilter\ViolationFilterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ViolationDetector
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var ViolationCheckerInterface
     */
    private $violationChecker;

    /**
     * @param EventDispatcherInterface  $eventDispatcher
     * @param ViolationCheckerInterface $violationChecker
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ViolationCheckerInterface $violationChecker)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->violationChecker = $violationChecker;
    }

    /**
     * @param RuleSet             $ruleSet
     * @param ParsedPhpFileFinder $files
     *
     * @return BaseViolation[]
     */
    public function getViolations(RuleSet $ruleSet, ParsedPhpFileFinder $files, ViolationFilterInterface $filter)
    {
        $total = count($files);
        $this->eventDispatcher->dispatch(
            ProgressEvent::CHECKER,
            new ProgressEvent(0, $total)
        );

        $result = array();
        foreach ($files as $i => $file) {
            $unfilteredResult = $this->violationChecker->check($file, $ruleSet);
            foreach ($unfilteredResult as $unfilteredViolation) {
                if (false === $filter->isViolationFiltered($unfilteredViolation)) {
                    $result[] = $unfilteredViolation;
                }
            }

            $this->eventDispatcher->dispatch(
                ProgressEvent::CHECKER,
                new ProgressEvent(++$i, $total)
            );
        }

        return $result;
    }
}
