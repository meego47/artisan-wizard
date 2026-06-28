<?php

use Antcode\ArtisanWizard\Tests\TestCase;

/*
 * The Console tests are pure unit tests (value objects, schema reflection) and
 * don't need a booted Laravel application, so they run on Pest's default test
 * case. The Orchestra Testbench TestCase is applied only to feature tests under
 * tests/Feature — this keeps the unit tests fast and avoids coupling them to the
 * full framework bootstrap, which is fragile across the prefer-lowest CI matrix.
 */
uses(TestCase::class)->in('Feature');
