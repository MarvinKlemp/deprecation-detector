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
                        $traverser->traverse($parser->parse($code))
                    )
                );
            } catch (\PhpParser\Error $e) {
            }
        }

        gc_enable();
    }
}