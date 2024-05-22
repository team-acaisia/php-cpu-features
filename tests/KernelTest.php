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
        Kernel::fromByte(31);
    }

    /**
     * @dataProvider provideFromReleaseString
     */
    public function testFromReleaseString(Kernel|null $expected, string $input): void
    {
        if (null === $expected) {
            $this->expectException(UnknownKernelException::class);
            Kernel::fromReleaseString($input);
            return;
        }

        $this->assertEquals($expected, Kernel::fromReleaseString($input));
    }

    public static function provideFromReleaseString(): array
    {
        return [
            [Kernel::v5_1, '5.1.0-45-some-build'],
            [Kernel::v5_1, '  5.1.0-45-some-build  '],
            [Kernel::v5_13, '  5.13.0-4565-some-build-with-longer-name  '],
            [Kernel::v6_6, '6.6.0-184-another'],
            [Kernel::v5_15, '5.15.0-92-generic'],
            [Kernel::v6_8, '6.8.0-31-generic'],
            [null, '5.452515.0-92-generic'],
            [null, '5.999.0-92-generic'],
            [null, '5.99.0-92-generic'],
            [null, '9.99.0-92-generic'],
            [null, '9.99.0-92-generic'],
            [null, ''],
            [null, '5.15'],
            [null, '5.15.0'],
            [null, '5.15.0-92'],
        ];
    }
}