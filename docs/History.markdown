
# HEAD
## 2.0.0 / 2021-11-23

Main changes on this release below, see also the [complete changelog](https://github.com/bedita/manager/compare/v1.2.0...v2.0.0).

### User-visible changes

 * Export data in multiple formats, with config `Exports.formats` (csv, ods, xlsx)
 * Bulk actions improved: change "field", copy/move content to destination folder, associate multiple categories, etc.
 * Fast object creation form customized by type
 * Filters enhanced in index and view pages
 * Module(s) accesses by users role(s)
 * `Categories` as trees in modules views
 * Lock/unlock of contents
 * Concurrent editors evidence
 * `Admin` and `Model` modules for admins

### Developer-visible changes

 * Composer 2
 * Nodejs 14
 * Webpack and all other javascript dependencies upgrade
 * Javascript linter [also GH workflow with lint validation]
 * Plugin `dereuromark/cakephp-ide-helper` added to dev requirements
 * `phpoffice/phpspreadsheet` to export data in multiple formats

### Integration changes

 * Use bedita/bedita:4.5.0 docker image in CI
 * Test coverage improved
 * Security vulnerabilities fixes

## 1.2.0 / 2020-06-12

Main changes on this release below, see also the [complete changelog](https://github.com/bedita/manager/compare/v1.1.0...v1.2.0).

### User-visible changes

* `Folders` tab tree in single object view
* `Folders` index tree view
* Handle multiple date ranges in object view (see `Events`)
* Display date ranges in index
* Avoid empty `Media` tab in object with no streams view
* Export CSV with active filter
* Add `roles` filter on Users

### Developer-visible changes

* Route to proxy requests to BEdita 4 API from JS
* Remove empty attributes in new objects creation, use defaults
* Improve default config, add `Pagination` default in bootstrap
* Fix a problem on failed save form (remove `id`)

### Integration changes

* Use `bedita/bedita:4.1.0` docker image in CI
* fix vulnerabilities in `acrorn` and other JS libraries
* Test coverage improved


## 1.1.0 / 2020-01-30

Main changes on this release below, see also the [complete changelog](https://github.com/bedita/manager/compare/v1.0.0...v1.1.0).

### User-visible changes

* Display simple map for objects with POINT coordinates
* Project configuration loaded object creation, `language` options available on object creation
* Related objects and folder children order changeable via `drang’n’drop`
* `Model` module has been refactored
* Upon save form data are recovered on error
* (Fix) Avoid 2nd field removal in exported CSV header

### Developer-visible changes

* Multi project support added
* Related objects search API call fixed
* (Fix) `CsrfExceptions` in bootstrap typo
* `Project.name`  set in configuration overrides the one in `/home` response
* (Fix) Properly save fields with null value
* Safe login redirects from any URL

### Integration changes

* `PHP 7.4` step added to Travis CI
* `ExportController` tests have been updated
* Code sniffer rules updated to PSR-12
* Scrutinizer use new PHP analysis

## 1.0.0 / 2019-11-18

First public release of **BEdita Manager** - official backend admin WebApp for BEdita4 API.

