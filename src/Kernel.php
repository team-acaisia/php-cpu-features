<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures;


use Acaisia\CpuFeatures\Exception\UnknownKernelException;

/**
 * Linux kernels (required for the CPU featureset flags to be parsed correctly)
 */
enum Kernel: string {
    case v5_0 = '5.0';
    case v5_1 = '5.1';
    case v5_2 = '5.2';
    case v5_3 = '5.3';
    case v5_4 = '5.4';
    case v5_5 = '5.5';
    case v5_6 = '5.6';
    case v5_7 = '5.7';
    case v5_8 = '5.8';
    case v5_9 = '5.9';
    case v5_10 = '5.10';
    case v5_11 = '5.11';
    case v5_12 = '5.12';
    case v5_13 = '5.13';
    case v5_14 = '5.14';
    case v5_15 = '5.15';
    case v5_16 = '5.16';
    case v5_17 = '5.17';
    case v5_18 = '5.18';
    case v5_19 = '5.19';
    case v6_0 = '6.0';
    case v6_1 = '6.1';
    case v6_2 = '6.2';
    case v6_3 = '6.3';
    case v6_4 = '6.4';
    case v6_5 = '6.5';
    case v6_6 = '6.6';

    /**
     * Returns the byte used for serialisation
     * @return int
     */
    public function getByteVersion(): int
    {
        return match ($this) {
            self::v5_0 => 0,
            self::v5_1 => 1,
            self::v5_2 => 2,
            self::v5_3 => 3,
            self::v5_4 => 4,
            self::v5_5 => 5,
            self::v5_6 => 6,
            self::v5_7 => 7,
            self::v5_8 => 8,
            self::v5_9 => 9,
            self::v5_10 => 10,
            self::v5_11 => 11,
            self::v5_12 => 12,
            self::v5_13 => 13,
            self::v5_14 => 14,
            self::v5_15 => 15,
            self::v5_16 => 16,
            self::v5_17 => 17,
            self::v5_18 => 18,
            self::v5_19 => 19,
            self::v6_0 => 20,
            self::v6_1 => 21,
            self::v6_2 => 22,
            self::v6_3 => 23,
            self::v6_4 => 24,
            self::v6_5 => 25,
            self::v6_6 => 26,
        };
    }

    /**
     * Create one from a serialized byte
     * @param int $byte
     * @return self
     */
    public static function fromByte(int $byte): self
    {
        if ($byte < 0 || $byte > 26) {
            throw new UnknownKernelException('Byte value ' . $byte . ' is not mapped to a known kernel.');
        }

        return match ($byte) {
            0 => self::v5_0,
            1 => self::v5_1,
            2 => self::v5_2,
            3 => self::v5_3,
            4 => self::v5_4,
            5 => self::v5_5,
            6 => self::v5_6,
            7 => self::v5_7,
            8 => self::v5_8,
            9 => self::v5_9,
            10 => self::v5_10,
            11 => self::v5_11,
            12 => self::v5_12,
            13 => self::v5_13,
            14 => self::v5_14,
            15 => self::v5_15,
            16 => self::v5_16,
            17 => self::v5_17,
            18 => self::v5_18,
            19 => self::v5_19,
            20 => self::v6_0,
            21 => self::v6_1,
            22 => self::v6_2,
            23 => self::v6_3,
            24 => self::v6_4,
            25 => self::v6_5,
            26 => self::v6_6,
        };
    }

    /**
     * Create one from a linux string (like "5.15.0-92-generic")
     * @param string $releaseString
     * @return self
     */
    public static function fromReleaseString(string $releaseString): self
    {
        preg_match('~(([0-9]\.[0-9]{1,3})\.[0-9])\-([0-9]+)\-(.*)~', $releaseString, $matches);
        if (count($matches) != 5) {
            throw new UnknownKernelException('Could not map "' . $releaseString . '" to a known kernel.');
        }

        try {
            return Kernel::from($matches[2]);
        } catch (\ValueError $e) {
            throw new UnknownKernelException('Could not map "' . $releaseString . '" to a known kernel.');
        }
    }
}