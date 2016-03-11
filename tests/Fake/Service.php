<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2016 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Micro\Core\Fake;

/**
 * Fake service.
 */
class Service
{
    protected $component;
    protected $format;

    public function __construct(ComponentInterface $component, string $format = null)
    {
        $this->component = $component;
        $this->format = $format;
    }

    public function getComponent(): ComponentInterface
    {
        return $this->component;
    }

    public function setFormat(string $format)
    {
        $this->format = $format;
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}
