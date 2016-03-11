<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2016 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Micro\Core\Fake;

/**
 * Fake component interface.
 */
interface ComponentInterface
{
    public function getResult(string $value);
}
