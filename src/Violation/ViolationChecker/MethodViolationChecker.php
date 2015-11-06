<?php

namespace SensioLabs\DeprecationDetector\Violation\ViolationChecker;

use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\FileInfo\Usage\MethodUsage;
use SensioLabs\DeprecationDetector\RuleSet\RuleSet;
use SensioLabs\DeprecationDetector\TypeGuessing\AncestorResolver;
use SensioLabs\DeprecationDetector\Violation\Violation;

class MethodViolationChecker implements ViolationCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(PhpFileInfo $phpFileInfo, RuleSet $ruleSet)
    {
        $violations = array();

        foreach ($phpFileInfo->methodUsages() as $methodUsage) {
            $className = $methodUsage->className();

            if ($ruleSet->hasMethod($methodUsage->name(), $className)) {
                $violations[] = new Violation(
                    $methodUsage,
                    $phpFileInfo,
                    $ruleSet->getMethod($methodUsage->name(), $className)->comment()
                );
            }
        }

        return $violations;
    }
}
