<?php

namespace SensioLabs\DeprecationDetector\AstMap;

use PhpParser\NodeVisitor\NameResolver;
use Symfony\Component\Finder\Finder;
use \SplFileInfo;

class AstMapGenerator
{
    /**
     * @param string $path
     * @return AstMap
     */
    public function generateAstMap($path)
    {
        $files = $this->collectFiles($path);

        $this->createAstMapByFiles(
            $astMap = new AstMap(),
            $files
        );

        return $astMap;
    }

    /**
     * @param $path
     * @return array
     */
    private function collectFiles($path)
    {
        return iterator_to_array(
            (new Finder())
                ->in($path)
                ->name('*.php')
                ->files()
                ->followLinks()
                ->ignoreUnreadableDirs(true)
                ->ignoreVCS(true)
        );
    }

    /**
     * @param AstMap $astMap
     * @param SplFileInfo[] $files
     */
    public function createAstMapByFiles(AstMap $astMap, array $files)
    {
        $parser = new \PhpParser\Parser(new \PhpParser\Lexer\Emulative);
        $traverser = new \PhpParser\NodeTraverser;
        $traverser->addVisitor(new NameResolver());

        gc_disable();

        foreach ($files as $file) {

            try {
                $code = file_get_contents($file->getPathname());
                $astMap->add(
                    new AstMapFile(
                        $file,
                        $ast = $traverser->traverse($parser->parse($code))
                    )
                );

                foreach (AstMapHelper::findClassLikeNodes($ast) as $classLikeNodes) {
                    $astMap->setClassInherit(
                        $classLikeNodes->namespacedName->toString(),
                        AstMapHelper::findInheritances($classLikeNodes)
                    );
                }

            } catch (\PhpParser\Error $e) {
            }
        }

        gc_enable();

        $this->flattenInheritanceDependencies($astMap);
    }

    private function flattenInheritanceDependencies(AstMap $astMap)
    {

        foreach ($astMap->getAllInherits() as $class => $inherits) {

            $inerhitInerhits = [];

            foreach ($inherits as $inherit) {
                $inerhitInerhits =  array_merge($inerhitInerhits, $this->resolveDepsRecursive($inherit, $astMap));
            }


            $astMap->setFlattenClassInherit(
                $class,
                array_values(array_unique(array_filter($inerhitInerhits, function($v) use ($astMap, $class) {
                    return !in_array($v, $astMap->getClassInherits($class));
                })))
            );
        }
    }

    private function resolveDepsRecursive($class, AstMap $astMap, \ArrayObject $alreadyResolved = null)
    {
        if ($alreadyResolved == null) {
            $alreadyResolved = new \ArrayObject();
        }

        // recursion detected
        if (isset($alreadyResolved[$class])) {
            return [];
        }

        $alreadyResolved[$class] = true;

        $buffer = [];
        foreach ($astMap->getClassInherits($class) as $dep) {
            $buffer = array_merge($buffer, $this->resolveDepsRecursive($dep, $astMap, $alreadyResolved));
            $buffer[] = $dep;
        }

        return array_values(array_unique($buffer));
    }
}