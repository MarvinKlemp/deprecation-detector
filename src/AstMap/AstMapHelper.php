<?php

namespace SensioLabs\DeprecationDetector\AstMap;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitor;

class AstMapHelper
{
    public static function findClassLikeNodes($nodes)
    {
        $collectedNodes = [];

        foreach ($nodes as $i => &$node) {
            if ($node instanceof Node\Stmt\ClassLike) {
                $collectedNodes[] = $node;
            } elseif ($node instanceof Use_) {
                continue;
            } elseif (is_array($node)) {
                $collectedNodes = array_merge(static::findClassLikeNodes($node), $collectedNodes);
            } elseif ($node instanceof Node) {
                $collectedNodes = array_merge(static::findClassLikeNodes(
                    static::getSubNodes($node)
                ), $collectedNodes);
            }
        }

        return $collectedNodes;
    }

    /**
     * @param Node\Stmt\ClassLike $klass
     * @return array string
     */
    public static function findInheritances(Node\Stmt\ClassLike $class)
    {
        $buffer = [];

        if ($class instanceof Class_ && $class->namespacedName instanceof Name) {

            if ($class->extends instanceof Name) {
                $buffer[] = $class->extends->toString();
            }

            if (!empty($class->implements)) {
                foreach ($class->implements as $impl) {

                    if (!$impl instanceof Name) {
                        continue;
                    }

                    $buffer[] = $impl->toString();
                }
            }
        }

        if ($class instanceof Trait_ || $class instanceof Class_) {
            foreach ($class->stmts as $traitUses) {
                if (!$traitUses instanceof Node\Stmt\TraitUse) {
                    continue;
                }

                foreach ($traitUses->traits as $traitUsage) {
                    if (!$traitUsage instanceof FullyQualified) {
                        continue;
                    }

                    $buffer[] = $traitUsage->toString();
                }
            }
        }

        if ($class instanceof Interface_ && isset($class->namespacedName) && $class->namespacedName instanceof Name) {
            foreach ($class->extends as $extends) {
                $buffer[] = $extends->toString();
            }
        }

        return $buffer;
    }

    private static function getSubNodes(Node $node)
    {
        $subnodes = [];
        foreach ($node->getSubNodeNames() as $name) {
            $subnodes[] =& $node->$name;
        }
        return $subnodes;
    }
}
