<?php

namespace SensioLabs\DeprecationDetector\AstMap;

use SplFileInfo;

class AstMapFile
{
    /**
     * @var SplFileInfo
     */
    private $file;

    /**
     * @var string
     */
    private $code;

    /**
     * @param SplFileInfo $file
     * @param string $code
     */
    public function __construct(SplFileInfo $file, $code)
    {
        $this->file = $file;
        $this->code = $code;
    }

    /**
     * @return SplFileInfo
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function key()
    {
        return $this->file->getPathname();
    }
}