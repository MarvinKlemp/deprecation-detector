<?php

namespace SensioLabs\DeprecationDetector\Violation;

use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\Violation\Violation as BaseViolation;
use SensioLabs\DeprecationDetector\Violation\ViolationChecker\ViolationCheckerInterface;
use SensioLabs\DeprecationDetector\Violation\ViolationFilter\ViolationFilterInterface;
use \ArrayIterator;

class ViolationDetector
{
    /**
     * @var ViolationCheckerInterface
     */
    private $violationChecker;

    /**
     * @var ViolationFilterInterface
     */
    private $violationFilter;

    /**
     * @param ViolationCheckerInterface $violationChecker
     * @param ViolationFilterInterface  $violationFilter
     */
    public function __construct(
        ViolationCheckerInterface $violationChecker,
        ViolationFilterInterface $violationFilter
    ) {
        $this->violationChecker = $violationChecker;
        $this->violationFilter = $violationFilter;
    }

    /**
     * @param RuleSet             $ruleSet
     * @param ArrayIterator       $files
     *
     * @return BaseViolation[]
     */
    public function getViolations(RuleSet $ruleSet, ArrayIterator $files)
    {
        $result = array();
        foreach ($files as $i => $file) {
            $unfilteredResult = $this->violationChecker->check($file, $ruleSet);
            foreach ($unfilteredResult as $unfilteredViolation) {
                if (false === $this->violationFilter->isViolationFiltered($unfilteredViolation)) {
                    $result[] = $unfilteredViolation;
                }
            }
        }

        return $result;
    }
}
