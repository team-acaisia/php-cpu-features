<?php

declare(strict_types=1);

namespace Acaisia\CpuFeatures\Tests;

use Acaisia\CpuFeatures\FeatureSet;
use Acaisia\CpuFeatures\Kernel;

final class FeatureTest extends AbstractTestCase
{
    public const CURCPUSTRING = 'fpu vme de pse tsc msr pae mce cx8 apic sep mtrr pge mca cmov pat pse36 clflush mmx fxsr sse sse2 ht syscall nx mmxext fxsr_opt pdpe1gb rdtscp lm constant_tsc rep_good nopl nonstop_tsc cpuid extd_apicid aperfmperf rapl pni pclmulqdq monitor ssse3 fma cx16 sse4_1 sse4_2 movbe popcnt aes xsave avx f16c rdrand lahf_lm cmp_legacy svm extapic cr8_legacy abm sse4a misalignsse 3dnowprefetch osvw ibs skinit wdt tce topoext perfctr_core perfctr_nb bpext perfctr_llc mwaitx cpb cat_l3 cdp_l3 hw_pstate ssbd mba ibrs ibpb stibp vmmcall fsgsbase bmi1 avx2 smep bmi2 erms invpcid cqm rdt_a rdseed adx smap clflushopt clwb sha_ni xsaveopt xsavec xgetbv1 xsaves cqm_llc cqm_occup_llc cqm_mbm_total cqm_mbm_local clzero irperf xsaveerptr rdpru wbnoinvd arat npt lbrv svm_lock nrip_save tsc_scale vmcb_clean flushbyasid decodeassists pausefilter pfthreshold avic v_vmsave_vmload vgif v_spec_ctrl umip pku ospke vaes vpclmulqdq rdpid overflow_recov succor smca fsrm';
    public function testUnknownByte() {
        $this->markTestIncomplete('This has to be improved');
        $features = FeatureSet::createFromString(Kernel::v6_2, self::CURCPUSTRING);

        $numbers = [
            10620688,
            536870912,
            1975662591,
            802421759,
        ];

        foreach ($features->toArray() as $feature) {
            $word = $feature->getWord($features->getKernel());
            $bit = $feature->getBit($features->getKernel());
            if ($word == 0) {
                echo 'Checking word '.$word.' bit ' . $bit . PHP_EOL;
                if ($numbers[$word] & 1<<$bit) {
                    // nothing
                } else {
                    echo 'Not in number, but in list?' . PHP_EOL;
                }
            }
        }

    }
}