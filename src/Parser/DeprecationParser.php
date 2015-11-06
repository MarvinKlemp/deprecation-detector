<?php

namespace SensioLabs\DeprecationDetector\Parser;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use SensioLabs\DeprecationDetector\AstMap\AstMapFile;
use SensioLabs\DeprecationDetector\FileInfo\PhpFileInfo;
use SensioLabs\DeprecationDetector\Visitor\DeprecationVisitorInterface;
use \PhpParser\Node;

class DeprecationParser implements ParserInterface
{
    /**
     * @var DeprecationVisitorInterface[]
     */
    protected $deprecationVisitors = array();

    /**
     * @param DeprecationVisitorInterface[] $visitors
     */
    public function __construct(array $visitors, NodeTraverser $baseTraverser)
    {
        $this->traverser = $baseTraverser;

        array_map(array($this, 'addDeprecationVisitor'), $visitors);
    }

    /**
     * @param DeprecationVisitorInterface $visitor
     */
    public function addDeprecationVisitor(DeprecationVisitorInterface $visitor)
    {
        $this->deprecationVisitors[] = $visitor;
        $this->traverser->addVisitor($visitor);
    }

    /**
     * @param PhpFileInfo $phpFileInfo
     * @param AstMapFile  $astMapFile
     *
     * @return PhpFileInfo
     */
    public function parseFile(PhpFileInfo $phpFileInfo, AstMapFile $astMapFile)
    {
        foreach ($this->deprecationVisitors as $visitor) {
            $visitor->setPhpFileInfo($phpFileInfo);
        }

        $this->traverser->traverse($astMapFile->code());

        return $phpFileInfo;
    }
}
