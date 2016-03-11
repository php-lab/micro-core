<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2016 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Micro\Core\Fake;

/**
 * Fake counter.
 */
class Counter
{
    protected $value = 0;

    public function increment()
    {
        $this->value++;
    }

    public function total(): int
    {
        return $this->value;
    }
}
