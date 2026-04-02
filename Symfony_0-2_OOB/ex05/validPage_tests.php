<?php

declare(strict_types=1);

include './Elem.php';

function assertSameBool(string $name, bool $expected, bool $actual, array &$results): void
{
    $ok = ($expected === $actual);
    $results[] = [$name, $ok, $expected, $actual];
}

function printResults(array $results): int
{
    $failures = 0;
    foreach ($results as [$name, $ok, $expected, $actual]) {
        if ($ok) {
            echo "[OK]   {$name}\n";
            continue;
        }

        $failures++;
        $exp = $expected ? 'true' : 'false';
        $got = $actual ? 'true' : 'false';
        echo "[FAIL] {$name} (expected {$exp}, got {$got})\n";
    }

    echo "\nTotal: " . count($results) . ", Failures: {$failures}\n";
    return $failures;
}

function buildValidPage(): Elem
{
    $page = new Elem('html');
    $page->pushElement(new Elem('head'));
    $page->pushElement(new Elem('meta', 'charset="UTF-8"'));
    $page->pushElement(new Elem('title', 'Valid Page'));
    $page->pushElement(new Elem('body'));
    $page->pushElement(new Elem('div'));
    $page->pushElement(new Elem('p', 'Hello world'));

    return $page;
}

$results = [];

// 1) Valid minimal page
$page = buildValidPage();
$page->getHTML();
assertSameBool('valid minimal page', true, $page->validPage(), $results);

// 2) html must contain exactly one head
$page = new Elem('html');
$page->pushElement(new Elem('head'));
$page->pushElement(new Elem('head'));
$page->pushElement(new Elem('meta', 'charset="UTF-8"'));
$page->pushElement(new Elem('title', 'dup head'));
$page->pushElement(new Elem('body'));
$page->getHTML();
assertSameBool('invalid when duplicate head', false, $page->validPage(), $results);

// 3) head must contain one title + one meta charset
$page = new Elem('html');
$page->pushElement(new Elem('head'));
$page->pushElement(new Elem('title', 'missing meta'));
$page->pushElement(new Elem('body'));
$page->getHTML();
assertSameBool('invalid when meta charset missing', false, $page->validPage(), $results);

$page = new Elem('html');
$page->pushElement(new Elem('head'));
$page->pushElement(new Elem('meta', 'charset="UTF-8"'));
$page->pushElement(new Elem('title', 'one'));
$page->pushElement(new Elem('title', 'two'));
$page->pushElement(new Elem('body'));
$page->getHTML();
assertSameBool('invalid when duplicate title', false, $page->validPage(), $results);

// 4) p must contain only text
$page = new Elem('html');
$page->pushElement(new Elem('head'));
$page->pushElement(new Elem('meta', 'charset="UTF-8"'));
$page->pushElement(new Elem('title', 'p nested tag'));
$page->pushElement(new Elem('body'));
$page->pushElement(new Elem('p', '<span>nested</span>'));
$page->getHTML();
assertSameBool('invalid when p contains nested tag', false, $page->validPage(), $results);

// 5) table must contain only tr
$page = new Elem('html');
$page->pushElement(new Elem('head'));
$page->pushElement(new Elem('meta', 'charset="UTF-8"'));
$page->pushElement(new Elem('title', 'table children'));
$page->pushElement(new Elem('body'));
$page->pushElement(new Elem('table'));
$page->pushElement(new Elem('div'));
$page->getHTML();
assertSameBool('invalid when table has non-tr child', false, $page->validPage(), $results);

// 6) tr must contain only th or td
$page = new Elem('html');
$page->pushElement(new Elem('head'));
$page->pushElement(new Elem('meta', 'charset="UTF-8"'));
$page->pushElement(new Elem('title', 'tr children'));
$page->pushElement(new Elem('body'));
$page->pushElement(new Elem('table'));
$page->pushElement(new Elem('tr'));
$page->pushElement(new Elem('p', 'bad in tr'));
$page->getHTML();
assertSameBool('invalid when tr has non-cell child', false, $page->validPage(), $results);

// 7) ul/ol must contain only li
$page = new Elem('html');
$page->pushElement(new Elem('head'));
$page->pushElement(new Elem('meta', 'charset="UTF-8"'));
$page->pushElement(new Elem('title', 'ol children'));
$page->pushElement(new Elem('body'));
$page->pushElement(new Elem('ol'));
$page->pushElement(new Elem('p', 'bad in ol'));
$page->getHTML();
assertSameBool('invalid when ol has non-li child', false, $page->validPage(), $results);

$page = new Elem('html');
$page->pushElement(new Elem('head'));
$page->pushElement(new Elem('meta', 'charset="UTF-8"'));
$page->pushElement(new Elem('title', 'ul with li'));
$page->pushElement(new Elem('body'));
$page->pushElement(new Elem('ul'));
$page->pushElement(new Elem('li', 'item'));
$page->getHTML();
assertSameBool('valid when ul has only li', true, $page->validPage(), $results);

$failures = printResults($results);
exit($failures === 0 ? 0 : 1);
