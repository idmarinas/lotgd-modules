![GitHub issues](https://img.shields.io/github/issues/idmarinas/lotgd-modules.svg)
[![DepShield Badge](https://depshield.sonatype.org/badges/idmarinas/lotgd-modules/depshield.svg)](https://depshield.github.io)
[![Website](https://img.shields.io/website-up-down-green-red/https/lotgd.infommo.es.svg?label=lotgd-demo)](https://lotgd.infommo.es)
[![built with gulp](https://img.shields.io/badge/gulp-builds_this_project-eb4a4b.svg?logo=gulp)](http://gulpjs.com/)

[![PayPal - The safer, easier way to pay online!](https://img.shields.io/badge/donate-help_my_project-ffaa29.svg?logo=paypal)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CAYNPHQ8VN92C&source=url)
[![Liberapay - Donate](https://img.shields.io/liberapay/receives/IDMarinas.svg?logo=liberapay)](https://liberapay.com/IDMarinas/donate)
[![Twitter](https://img.shields.io/twitter/url/http/shields.io.svg?style=social)](https://twitter.com/idmarinas)

# About #

This modules are created for community of LOTGD and published in ([DragonPrime](http://dragonprime.net)).

# Disclaimer #

These modules are downloaded from ([DragonPrime](http://dragonprime.net)) and adapted to work in LOTGD IDMarinas Edition and are published in this repository so that other members can use them with LOTGD IDMarinas Edition.
All this work was done without intention to offend or break the license on which the modules and the core of LOTGD are based.

# Structure of modules for version 4.0.0
```
..\src
├── moduleName
│   ├── data
│   │   ├── template
│   │   │   └── module
│   │   │       └── moduleName
│   │   │           └── file.twig
│   │   └── translation
│   │       └── en
│   │           └── module
│   │               └── moduleName.yaml
│   ├── modules
│   │   └── moduleName.php
│   └── src
│       └── local
│           └── Entity
│               └── ModuleModuleName.php
```
> Simplifying, each module is included in its own folder, which copies the LoTGD structure.

> This structure is only for this repository, when the command `gulp` is executed the structure of the modules becomes:
```
data
├── template
│   └── module
│       └── moduleName
│           └── file.twig
└── translation
    └── en
        └── module
            └── moduleName.yaml
modules
└── moduleName.php
src
└── local
    └── Entity
        └── ModuleModuleName.php
```

## This modules not work in other version ##
