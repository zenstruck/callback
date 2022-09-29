# CHANGELOG

## [v1.5.0](https://github.com/zenstruck/callback/releases/tag/v1.5.0)

August 31st, 2022 - [v1.4.2...v1.5.0](https://github.com/zenstruck/callback/compare/v1.4.2...v1.5.0)

* eed9a53 [minor] remove scrutinizer badge by @kbond
* 6bd1c49 [minor] remove scrutinizer (#8) by @kbond
* 68747ed [feature] add `Callback::function()` (#8) by @kbond
* d8b7878 [feature] support intersection type support (#8) by @kbond
* 319fb96 [minor] improve output of `Callback::__toString()` (#8) by @kbond
* 68e8468 [minor] small perf optimizations (#8) by @kbond

## [v1.4.2](https://github.com/zenstruck/callback/releases/tag/v1.4.2)

March 26th, 2022 - [v1.4.1...v1.4.2](https://github.com/zenstruck/callback/compare/v1.4.1...v1.4.2)

* 3eb5356 [bug] support arguments with "self" typehint (#6) by @kbond
* a3ff9ac [bug] fix Argument::supports() for Stringable object (#6) by @kbond
* dfb961b [minor] use supported php-cs-fixer version (#5) by @kbond
* b9fdbc2 [ci] add php 8.1 to test matrix by @kbond

## [v1.4.1](https://github.com/zenstruck/callback/releases/tag/v1.4.1)

October 18th, 2021 - [v1.4.0...v1.4.1](https://github.com/zenstruck/callback/compare/v1.4.0...v1.4.1)

* 8961f8d [minor] add $options argument to Parameter::typed() by @kbond
* 29d8277 [bug] fix using UnionParameter on parameters with default values by @kbond
* a4fb33d [ci] use reusable actions (#4) by @kbond

## [v1.4.0](https://github.com/zenstruck/callback/releases/tag/v1.4.0)

July 20th, 2021 - [v1.3.0...v1.4.0](https://github.com/zenstruck/callback/compare/v1.3.0...v1.4.0)

* 3eae5eb [minor] ensure works with `declare(strict_types=1)` (#3) by @kbond
* fef4acf [bug] allow default parameter values (#3) by @kbond
* c9f7823 [minor] add Argument::EXACT (#3) by @kbond
* e238a8c [minor] make TypedParameter options configurable (#3) by @kbond
* 40b56af [minor] add non-parameter invoke tests (#3) by @kbond
* 4b84bef [feature] add "strict" supports/allowed modes (#3) by @kbond
* 1d0031f [feature] add Argument::allows() (#3) by @kbond
* 9fd65e3 [feature] improve Argument::supports() (#3) by @kbond

## [v1.3.0](https://github.com/zenstruck/callback/releases/tag/v1.3.0)

July 16th, 2021 - [v1.2.0...v1.3.0](https://github.com/zenstruck/callback/compare/v1.2.0...v1.3.0)

* d5d19c1 [feature] access callback arguments, support PHP 8 union types (#2) by @kbond
* 10ae748 [minor] update php-cs-fixer to v3 by @kbond
* 9a20734 [minor] disable codecov pr annotations by @kbond
* 01f812f [minor] lock php-cs-fixer version in ci (bug in latest release) by @kbond

## [v1.2.0](https://github.com/zenstruck/callback/releases/tag/v1.2.0)

January 11th, 2021 - [v1.1.0...v1.2.0](https://github.com/zenstruck/callback/compare/v1.1.0...v1.2.0)

* b759cfc [feature] add callback as string to exceptions by @kbond
* 8f6d7ab [feature] make callback stringable by @kbond

## [v1.1.0](https://github.com/zenstruck/callback/releases/tag/v1.1.0)

January 9th, 2021 - [v1.0.0...v1.1.0](https://github.com/zenstruck/callback/compare/v1.0.0...v1.1.0)

* 82cd9ac [bug] fix optional parameter issue by @kbond
* 6934887 [feature] allow invoke parameters to be optional by @kbond
* eed6f12 [bug] fix deprecation by @kbond
* 0d084b2 [minor] add Parameter::factory() helper method by @kbond
* f6a7190 [BC BREAK] major refactor by @kbond

## [v1.0.0](https://github.com/zenstruck/callback/releases/tag/v1.0.0)

January 8th, 2021 - _[Initial Release](https://github.com/zenstruck/callback/commits/v1.0.0)_
