![GitHub release](https://img.shields.io/github/release/idmarinas/lotgd-modules.svg)
![GitHub Release Date](https://img.shields.io/github/release-date/idmarinas/lotgd-modules.svg)
[![Website](https://img.shields.io/website-up-down-green-red/https/lotgd.infommo.es.svg?label=lotgd-demo)](https://lotgd.infommo.es)
[![Build in PHP](https://img.shields.io/badge/PHP-^7.1-8892BF.svg?logo=php)](http://php.net/)

![GitHub top language](https://img.shields.io/github/languages/top/idmarinas/lotgd-modules.svg)
![GitHub language count](https://img.shields.io/github/languages/count/idmarinas/lotgd-modules.svg)
[![Maintainability](https://api.codeclimate.com/v1/badges/4553239eac9e717f1cce/maintainability)](https://codeclimate.com/github/idmarinas/lotgd-modules/maintainability)
[![DepShield Badge](https://depshield.sonatype.org/badges/idmarinas/lotgd-modules/depshield.svg)](https://depshield.github.io)
[![Total alerts](https://img.shields.io/lgtm/alerts/g/idmarinas/lotgd-modules.svg?logo=lgtm&logoWidth=18)](https://lgtm.com/projects/g/idmarinas/lotgd-modules/alerts/)
[![Language grade: JavaScript](https://img.shields.io/lgtm/grade/javascript/g/idmarinas/lotgd-modules.svg?logo=lgtm&logoWidth=18)](https://lgtm.com/projects/g/idmarinas/lotgd-modules/context:javascript)

[![built with gulp](https://img.shields.io/badge/gulp-builds_this_project-eb4a4b.svg?logo=gulp)](http://gulpjs.com/)
[![Dependabot Status](https://api.dependabot.com/badges/status?host=github&repo=idmarinas/lotgd-modules)](https://dependabot.com)

[![PayPal - The safer, easier way to pay online!](https://img.shields.io/badge/donate-help_my_project-ffaa29.svg?logo=paypal)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CAYNPHQ8VN92C&source=url)
[![Liberapay - Donate](https://img.shields.io/liberapay/receives/IDMarinas.svg?logo=liberapay)](https://liberapay.com/IDMarinas/donate)
[![Twitter](https://img.shields.io/twitter/url/http/shields.io.svg?style=social)](https://twitter.com/idmarinas)

# About #

This modules are created for community of LOTGD and published in ([DragonPrime](http://dragonprime.net)).

# Disclaimer #

These modules are downloaded from ([DragonPrime](http://dragonprime.net)) and adapted to work in LOTGD IDMarinas Edition and are published in this repository so that other members can use them with LOTGD IDMarinas Edition.
All this work was done without intention to offend or break the license on which the modules and the core of LOTGD are based.

> **_NOTE:_**  This modules not work in other version

# Structure of modules for version 4.5.0
```
..\src
├── moduleName
│   ├── templates_modules
│   │   └── moduleName_templateName.html.twig
│   ├── translations
│   │   └── en
│   │       └── moduleName_domain.yaml
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
../
├── templates_modules
│   └── moduleName_templateName.html.twig
├── translations
│   └── en
│       └── moduleName_domain.yaml
├── modules
│   └── moduleName.php
└── src
    └── local
        └── Entity
            └── ModuleModuleName.php
```

# Obsolete modules

> These modules are obsolete and it is not recommended to use them in a version greater than or equal to 4.0.0.
>
> On many occasions, the functionality has been included in the core.

-   `serversuspend.php` Server Maintenance Suspension
    >   The core now includes this feature.
-   `translationwizard.php` Translation Wizard
    >   The core now use a diferent translation system.
-   `unclean.php` Unclean Commentary Tracker
    >   New comment system allow hide/unhide comments. In future version allow to delete hide comments.
-   `allmodulegroup.php` All Module Group - Modulemanager
    >   The core now includes this feature.
-   `sulogin.php` Superuser Login
    >   The core now includes this feature.
-   `last_installed.php` Additional Module Operations
    >   These function may be included in a future version of the core.
-   `charrestore.php` Character Restorer
    >   Core include a new system of Backups for characters, and allow restore it.
-   `meaninglesscode.php` Hide Meaningless Appo Codes
    >   Core include a Sanitize for remove non color codes.
-   `deputymoderator.php` Deputy Moderator
    >   These function may be included in a future version of the core.
-   `pvpnocheat.php` PvP NoCheat
    > The core now includes this feature.
-   `warnlvl.php` Warning Level and Bans
    > These function will be included in a future version of the core.

# Modules that have been migrated in a LoTGD Bundle
-   `funddrive/` Is now a LoTGD Bundle [Bundle Fund Drive](https://github.com/lotgd-core/bundle-fund-drive)
-   `funddriverewards/` Is now a LoTGD Bundle [Bundle Fund Drive Rewards](https://github.com/lotgd-core/bundle-fund-drive-rewards)
-   `gardenparty/` Is now a LoTGD Bundle [Bundle Garden Party](https://github.com/lotgd-core/bundle-garden-party)
-   `clannews/` Is now a LoTGD Bundle [Clan News](https://github.com/lotgd-core/clan-news-bundle)
-   `titlechange/` Is now a LoTGD Bundle [Lodge Title Change](https://github.com/lotgd-core/lodge-title-change-bundle)
-   `fairy/` Is now a LoTGD Bundle [Fairy](https://github.com/lotgd-core/fairy-bundle)
-   `findgold/` Is now a LoTGD Bundle [Find Gold](https://github.com/lotgd-core/find-gold-bundle)
