#!/usr/bin/php-cgi
<?php
/*
 * File: index.cgi
 * By: Erik Bernhardson
 * Date: 2-25-2009
 *
 * This is a generic frontend to the simplified MVC
 * framework built for torrentwatch
 * Its supposed to be bad form to roll your own, but I
 * couldn't find anything simple enough to run quickly
 * on the NMT platform.
 *
 * This implementation is *incredibly* bare bones
 * and based roughly on Kohana PHP
 */

define('IN_PRODUCTION', False);

error_reporting(E_ALL &~ E_STRICT);
ini_set('display_errors', TRUE);

define('DOCROOT', dirname(realpath(__FILE__)).'/');
define('SYSPATH', DOCROOT.'system/');
define('APPPATH', DOCROOT.'nmtdvr/');
define('MODPATH', DOCROOT.'modules/');
define('USERPATH', DOCROOT.'userdata/');

// Get the Benchmarking started as early as possible
require_once SYSPATH.'Benchmark.php';
Benchmark::start('total_execution');
Benchmark::start('framework_setup');

// Setup our include directories
ini_set('include_path', '.:'.APPPATH.':'.SYSPATH.':'.MODPATH);

// Include the basic Classes
require SYSPATH.'Event.php';
require SYSPATH.'SimpleMvc.php';

SimpleMvc::setup();

Event::run('system.ready');

Benchmark::stop('framework_setup');
Event::run('system.routing');
Event::run('system.execute');
Event::run('system.shutdown');

Benchmark::stop('total_execution');
file_put_contents(USERPATH.'benchmarks.log', 
                  print_r(Benchmark::get(True), True)."\n", 
                  FILE_APPEND);

