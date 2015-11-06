<?php

namespace SensioLabs\DeprecationDetector\AstMap;

use \SplFileInfo;

class AstMap
{
    /**
     * @var AstMapFile[]
     */
    private $astMapFiles;

    /**
     * @var array
     */
    private $classInheritMap;

    /**
     * @var array
     */
    private $flattenClassInheritMap;

    /**
     * @param AstMapFile[] $astMapFiles
     * @param array $classInheritanceMap
     * @param array $flattenClassInheritanceMap
     */
    public function __construct(
        array $astMapFiles = array(),
        array $classInheritanceMap = array(),
        array $flattenClassInheritanceMap = array()
    ) {
        $this->astMapFiles = $astMapFiles;
        $this->classInheritMap = $classInheritanceMap;
        $this->flattenClassInheritMap = $flattenClassInheritanceMap;
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

    public function setClassInherit($class, array $inheritClasses)
    {
        if (empty($inheritClasses)) {
            return;
        }

        $this->classInheritMap[$class] =$inheritClasses;
    }

    public function getAllInherits()
    {
        return $this->classInheritMap;
    }

    public function getClassInherits($class)
    {
        if (!isset($this->classInheritMap[$class])) {
            return array();
        }

        return $this->classInheritMap[$class];
    }

    public function setFlattenClassInherit($class, array $inheritClasses)
    {
        if (empty($inheritClasses)) {
            return;
        }

        $this->flattenClassInheritMap[$class] = $inheritClasses;
    }

    public function getAllFlattenClassInherits()
    {
        return $this->flattenClassInheritMap;
    }

    public function getFlattenClassInherits($class)
    {
        if (!isset($this->flattenClassInheritMap[$class])) {
            return array();
        }

        return $this->flattenClassInheritMap[$class];
    }
}
