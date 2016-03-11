<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2016 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Micro\Core\Fake;

/**
 * Fake logger.
 */
class Logger
{
    protected $log = [];

    public function log(string $message)
    {
        $this->log[] = $message;
    }

    public function export(): array
    {
        return $this->log;
    }
}
