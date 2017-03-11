Arachne/Doctrine
====

[![Build Status](https://img.shields.io/travis/Arachne/Doctrine/master.svg?style=flat-square)](https://travis-ci.org/Arachne/Doctrine/branches)
[![Coverage Status](https://img.shields.io/coveralls/Arachne/Doctrine/master.svg?style=flat-square)](https://coveralls.io/github/Arachne/Doctrine?branch=master)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/0be9dca9-8412-481b-b86d-90bb6f07a7b0.svg?style=flat-square)](https://insight.sensiolabs.com/projects/0be9dca9-8412-481b-b86d-90bb6f07a7b0)
[![VersionEye](https://img.shields.io/versioneye/d/php/arachne:doctrine.svg?style=flat-square)](https://www.versioneye.com/php/arachne:doctrine)
[![Latest stable](https://img.shields.io/packagist/v/arachne/doctrine.svg?style=flat-square)](https://packagist.org/packages/arachne/doctrine)
[![Downloads this Month](https://img.shields.io/packagist/dm/arachne/doctrine.svg?style=flat-square)](https://packagist.org/packages/arachne/doctrine)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://github.com/Arachne/Doctrine/blob/master/license.md)

Additional integrations of Doctrine to Kdyby and Arachne packages.

Documentation
----

This package works as a bridge to connect [Kdyby/Doctrine](https://github.com/Kdyby/Doctrine) with other libraries. There are no hard dependencies. Instead Arachne/Doctrine will add each feature if the necessary extensions are available.

- [Entities as presenter parameters](docs/entity-loader.md) (with [Arachne/EntityLoader](https://github.com/Arachne/EntityLoader))
- [Symfony/Validator UniqueEntity constraint, validation on flush](docs/validator.md) (with [Kdyby/Validator](https://github.com/Kdyby/Validator))
- [Symfony/Form EntityType and DoctrineOrmTypeGuesser](docs/forms.md) (with [Arachne/Forms](https://github.com/Arachne/Forms))
