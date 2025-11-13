<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures\Tests;

use Acaisia\CpuFeatures\FeatureSet;
use Acaisia\CpuFeatures\Kernel;
use PHPUnit\Framework\TestStatus\Warning;

final class TestUnknownValueDoesNotThrowExceptionTest extends AbstractTestCase
{

    public function testForwardCompatibility(): void {

        set_error_handler(static function (int $errno, string $errstr): never {
            throw new \Exception($errstr, $errno);
        }, E_USER_WARNING);


        // phpunit 10.0
        $this->expectExceptionMessage('"ibpb_exit_to_user" is not a valid backing value for enum Acaisia\CpuFeatures\Feature');

        FeatureSet::createFromString(Kernel::v6_2, 'ibpb_exit_to_user');

        restore_error_handler();
    }

    public function testExceptionOnNewSettings(): void {
        $this->expectException(\ValueError::class);
        FeatureSet::createFromString(Kernel::v6_2, 'ibpb_exit_to_user', true);
    }
}