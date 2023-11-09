<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures\Tests;

use Acaisia\CpuFeatures\Exception\UnknownKernelException;
use Acaisia\CpuFeatures\Kernel;

final class KernelTest extends AbstractTestCase
{
    public function testUnknownByte() {
        $this->expectException(UnknownKernelException::class);
        Kernel::fromByte(-1);
    }

    public function testUnknownByteTooHigh() {
        $this->expectException(UnknownKernelException::class);
        Kernel::fromByte(27);
    }
}