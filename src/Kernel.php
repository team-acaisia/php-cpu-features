<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures;


/**
 * Linux kernels (required for the CPU featureset flags to be parsed correctly)
 */
enum Kernel: string {
    case v6_6 = '6.6';
    case v6_5 = '6.5';
    case v6_4 = '6.4';
    case v6_3 = '6.3';
    case v6_2 = '6.2';
    case v6_1 = '6.1';
    case v6_0 = '6.0';

    case v5_19 = '5.19';
    case v5_18 = '5.18';
    case v5_17 = '5.17';
    case v5_16 = '5.16';
    case v5_15 = '5.15';
    case v5_14 = '5.14';
    case v5_13 = '5.13';
    case v5_12 = '5.12';
    case v5_11 = '5.11';
    case v5_10 = '5.10';
    case v5_9 = '5.9';
    case v5_8 = '5.8';
    case v5_7 = '5.7';
    case v5_6 = '5.6';
    case v5_5 = '5.5';
    case v5_4 = '5.4';
    case v5_3 = '5.3';
    case v5_2 = '5.2';
    case v5_1 = '5.1';
    case v5_0 = '5.0';
}