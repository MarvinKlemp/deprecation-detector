<?php

namespace SensioLabs\DeprecationDetector\AstMap;

use \SplFileInfo;

class AstMap
{
    /**
     * @var AstMapFile[]
     */
    protected $astMapFiles;

    /**
     * @param AstMapFile[] $astMapFiles
     */
    public function __construct($astMapFiles = array())
    {
        $this->astMapFiles = $astMapFiles;
    }

    public function add(AstMapFile $file)
    {
        $this->astMapFiles[$file->key()] = $file;
    }

    /**
     * @return array
     */
    public function getAsts()
    {
        return $this->astMapFiles;
    }

    public function getAstByFile(SplFileInfo $file)
    {
        /** @TODO change API */
        if (!isset($this->astMapFiles[$file->getPathname()])) {
            return null;
        }

        return $this->astMapFiles[$file->getPathname()];
    }

}