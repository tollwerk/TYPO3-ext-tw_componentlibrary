# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [0.3.3] - Unreleased

### Changed

- Make component base classes abstract
- Add validation error support to Fluid components 
- Ensure one-off extbase controller class declaration

## [0.3.2] - 2018-01-11

### Changed

- TYPO3 v9 compatibility
- Added support for directory specific configuration 

### Fixed

- Dependency graph determination bug for single components

## [0.3.1] - 2017-11-25

### Changed

- Documentation & skeleton file updates
- Fixed graph dependencies to component variants

## [0.3.0] - 2017-11-15

### Added

- Correct file system privileges for kickstarted components
- Added "content" component type ([#5](https://github.com/tollwerk/TYPO3-ext-tw_componentlibrary/issues/5))
- Improved error reporting in case of component rendering issues ([#6](https://github.com/tollwerk/TYPO3-ext-tw_componentlibrary/issues/6))
- Introduced component dependencies ([#4](https://github.com/tollwerk/TYPO3-ext-tw_componentlibrary/issues/4))
- Introduced GraphViz based component dependency graphs ([#4](https://github.com/tollwerk/TYPO3-ext-tw_componentlibrary/issues/4))

## [0.2.6] - 2017-10-15

### Added

- Command line component kickstarter

## [0.2.5] - 2017-10-03

### Added

- Support for relative translation keys in Fluid components

## [0.2.4] - 2017-09-23

### Added

- Backend integration to update and re-initialize an external component library
- Support for JSON parameter files for FLUID template components
- Support for rich documentation & auto-generated documentation listing

## [0.2.3] - 2017-07-28

### Fixed

- Removed flux extension dependency ([#2](https://github.com/tollwerk/TYPO3-ext-tw_componentlibrary/issues/2))
- Excluded the component GET parameter from cHash calculation ([#1](https://github.com/tollwerk/TYPO3-ext-tw_componentlibrary/issues/1))
- Fixed export value of top level component paths ([tollwerk/fractal-typo3 #1](https://github.com/tollwerk/fractal-typo3/issues/1))

## [0.2.2] - 2017-07-10

### Added

- Added changelog & documentation
