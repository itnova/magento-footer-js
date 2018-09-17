# Changelog
All notable changes to this extension will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 0.1.3.3
### Changed
- Base64 encode all inline scripts.
- Don't add defer tag if the script is inline.

## [0.1.3.2] - 2017-09-01
### Changed
- Applied a better `fix` for adding the `defer` keyword to the moved script tags.
- Also check if we need to exclude a js block because of a `pagespeed_no_defer` flag.

## [0.1.3.1] - 2017-08-31
### Changed
- Applied a quick `fix` for adding the `defer` keyword to all script tags.

## [0.1.3] - 2017-07-26
### Added
- Resolved composer support
- Added to packagist

## [0.1.1] - 2016-06-06
### Changed
- Fix for excluding specific scripts

## [0.1.0] - 2016-06-03
### Added
- Support for excluding specific scripts

## [0.0.5] - 2016-04-15
### Changed
- Ignore scripts in comments

### Removed
- Removed HTTP response observer

## [0.0.4] - 2016-03-01
### Added
- Added usage information
- Added exclusion flag for scripts
- Add black list for blocks

### Removed
- Check response body

### Changed
- Rewrite for EE FPC to stop container being saved with JS

## [0.0.3] - 2015-08-22
### Added
- Add Varien_Profiler lines
- Added composer.json by Alan Morkan
- Listen to http_response_send_before
- Added Magento EE FPC compatibility
