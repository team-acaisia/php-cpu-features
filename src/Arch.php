<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures;


/**
 * Linux archs (required for the CPU featureset flags to be parsed correctly)
 */
enum Arch: string {
    case X86 = 'x86'; //We only support x86 for now
}