<?php

/**
 * This PHP file generates new source files for ENUMs and feature flags, based off of the linux source
 */

declare(strict_types=1);

use Acaisia\CpuFeatures\Kernel;

require __DIR__ . "/../vendor/autoload.php";

const CACHE = true;
const DEBUG = false;

// This regex parses the source code;
$regex = '~^#define X86_FEATURE_([0-9A-Z_]+)\s*\(\s*([0-9]+)\*32\+\s*([0-9]+)\)\s*\/\*\s(\"[a-z0-9_]*\")*(.*)\*\/$~m';

$kernels = [];
$features = [];

class Feature {
    public function __construct(
        public Kernel $kernel,
        public int $word,
        public int $bit,
        public string $display,
        public string $description,
        public bool $hidden,
    )
    {
    }
}

function getData(Kernel $kernel) {
    if (CACHE) {
        $cacheFile = '/tmp/gen_php_cpu_'.$kernel->name;
        if (file_exists($cacheFile)) {
            return file_get_contents($cacheFile);
        }
    }

    $data = file_get_contents(sprintf(
        'https://raw.githubusercontent.com/torvalds/linux/v%s/arch/x86/include/asm/cpufeatures.h',
        $kernel->value
    ));
    if (CACHE) {
        file_put_contents($cacheFile, $data);
    }
    return $data;
}

foreach (array_reverse(Kernel::cases()) as $kernel) {
    $data = getData($kernel);

    $matches = [];
    preg_match_all($regex, $data, $matches, PREG_SET_ORDER);

    $kernels[$kernel->name] = $matches; // Store for later marching through
    if (DEBUG) {
        echo "====== KERNEL " . $kernel->name . "\n";
        echo "    Word\tBit\tName\t\t\t\t\t\tDescription\n";
    }
    $unique = 0;
    foreach ($matches as list($line, $featureString, $word, $bit, $display, $description)) {
        $description = trim($description);

        // Parse the display value. If its "" its hidden. If its set, it overrides the flag name
        $hidden = false;
        $displayParsed = strtolower($featureString); // Take the feature name as is in the definition
        if ($display == '""') {
            $hidden = true;
        } else if (strlen($display) >= 3) {
            $displayParsed = trim($display, '"');
        }

        // Add unique names
        $featureObj = new Feature($kernel, (int) $word,(int) $bit, $displayParsed, $description, $hidden);

        // Some debug printing
        if (DEBUG) {
            $tabs = str_repeat("\t", (int) ceil(4 - (strlen($featureObj->display) / 8)));
            printf("    %s\t%s\t%s\t%s\n", $featureObj->word, $featureObj->bit, $featureObj->display . $tabs . ($featureObj->hidden ? "[HIDDEN]" : "\t"), $featureObj->description);
        }

        // Now check if we can add this uniquely by name
        if (!array_key_exists($featureString, $features)) {
            $features[$featureString] = [];
            $unique ++;
        }

        // Add it in this list
        $features[$featureString][$featureObj->kernel->name] = $featureObj;
    }

    echo 'Added kernel ' . $kernel->name . '. ' . $unique . ' new unique flags introduced. Kernel has ' . count($matches) . ' available flags.' . PHP_EOL;
}

/// This part is a test to check if all the kernels have the same value for this flag (to simplify lookups)
if (DEBUG) {
    foreach ($features as $fkey => $featureString) {
        $firstDesc = null;
        $firstHidden = null;
        $firstWord = null;
        $firstBit = null;
        $firstDisplay = null;
        /**
         * @var Kernel $kernel
         * @var Feature $value
         */
        foreach ($featureString as $kernel => $value) {
            if ($firstDesc === null) {
                $firstDesc = $value->description;
            }
            if ($firstHidden === null) {
                $firstHidden = $value->hidden;
            }
            if ($firstWord === null) {
                $firstWord = $value->word;
            }
            if ($firstBit === null) {
                $firstBit = $value->bit;
            }
            if ($firstDisplay === null) {
                $firstDisplay = $value->display;
            }
            if ($firstDesc !== $value->description) {
                echo 'Feature ' . $fkey . '@'.$kernel.' has a different DESC   from the first: ['.$firstDesc.'] VS [' . $value->description .'] '. PHP_EOL;
            }
            if ($firstHidden !== $value->hidden) {
                echo 'Feature ' . $fkey . '@'.$kernel.' has a different HIDDEN from the first: ['.$firstHidden.'] VS [' . $value->hidden .'] '. PHP_EOL;
            }
            if ($firstWord !== $value->word) {
                echo 'Feature ' . $fkey . '@'.$kernel.' has a different WORD from the first: ['.$firstWord.'] VS [' . $value->word .'] '. PHP_EOL;
            }
            if ($firstBit !== $value->bit) {
                echo 'Feature ' . $fkey . '@'.$kernel.' has a different BIT from the first: ['.$firstBit.'] VS [' . $value->bit .'] '. PHP_EOL;
            }
            if ($firstDisplay !== $value->display) {
                echo 'Feature ' . $fkey . '@'.$kernel.' has a different DISPLAY from the first: ['.$firstDisplay.'] VS [' . $value->display .'] '. PHP_EOL;
            }
        }
    }

    // WORD and BIT can differ quite a bit
    // HIDDEN never differs
    // DISPLAY never differs
    // DESCRIPTION only differs a little bit, so we always take the description from the latest kernel
}

echo ' Total unique feature flags: ' . count($features) . PHP_EOL;
echo 'Generating main feature flag enum file...';

$template = file_get_contents('./enum.tmpl.php');

/// Template variables to parse
const REPL_CASES = '    case TEMPLATE_CASES = "TEMPLATE";';
const HIDDEN_FUNCTION = '    public function getHidden(): bool { return false; }';
const DESCRIPTION_FUNCTION = '    public function getDescription(): string { return ""; }';
const MAP_BIT = '    private const MAP_BIT = [Kernel::v5_0->value => [self::TEMPLATE_CASES->value => 1]];';
const MAP_WORD = '    private const MAP_WORD = [Kernel::v5_0->value => [self::TEMPLATE_CASES->value => 1]];';

/// Initialize strings
$replacements[REPL_CASES] = '';

$replacements[HIDDEN_FUNCTION] = <<<PHP
    public function getHidden(): bool {
        return match (\$this) {

PHP;
$replacements[DESCRIPTION_FUNCTION] = <<<PHP
    public function getDescription(): string {
        return match (\$this) {

PHP;
$replacements[MAP_BIT] = <<<PHP
    private const MAP_BIT = [

PHP;
$replacements[MAP_WORD] = <<<PHP
    private const MAP_WORD = [

PHP;
/**
 * Fill the strings!
 *
 * @var string $key
 * @var Feature[] $featureArray
 */
foreach ($features as $key => $featureArray) {
    $replacements[REPL_CASES] .= sprintf('    case X86_%s = "%s"; // %s' . "\n", strtoupper($key), $featureArray[array_key_last($featureArray)]->display, $featureArray[array_key_last($featureArray)]->description);
    $replacements[HIDDEN_FUNCTION] .= sprintf('            self::X86_%s => %s,' . "\n", strtoupper($key), $featureArray[array_key_last($featureArray)]->hidden ? 'true' : 'false');
    $replacements[DESCRIPTION_FUNCTION] .= sprintf('            self::X86_%s => \'%s\',' . "\n", strtoupper($key), str_replace('\'', '\\\'', $featureArray[array_key_last($featureArray)]->description));

    $replacements[MAP_BIT] .= sprintf('        self::X86_%s->value => [' . "\n", strtoupper($key));
    foreach ($featureArray as $kernel => $feature) {
        $replacements[MAP_BIT] .= sprintf('            Kernel::%s->value => %s,' . "\n", $kernel, $feature->bit);
    }
    $replacements[MAP_BIT] .= '        ],' . PHP_EOL;

    $replacements[MAP_WORD] .= sprintf('        self::X86_%s->value => [' . "\n", strtoupper($key));
    foreach ($featureArray as $kernel => $feature) {
        $replacements[MAP_WORD] .= sprintf('            Kernel::%s->value => %s,' . "\n", $kernel, $feature->word);
    }
    $replacements[MAP_WORD] .= '        ],' . PHP_EOL;
}

// Finalize any strings
$replacements[HIDDEN_FUNCTION] .= <<<PHP
        };
    }
PHP;
$replacements[DESCRIPTION_FUNCTION] .= <<<PHP
        };
    }
PHP;
$replacements[MAP_BIT] .= <<<PHP
    ];
PHP;
$replacements[MAP_WORD] .= <<<PHP
    ];
PHP;
// Write out
$template = str_replace(array_keys($replacements), $replacements, $template);
file_put_contents(__DIR__ . '/../src/Feature.php', $template);

echo 'Written file!' . PHP_EOL;