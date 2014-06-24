BatchManager
============
[![Build Status](https://travis-ci.org/neoglez/BatchManager.svg?branch=master)](https://travis-ci.org/neoglez/BatchManager) [![Code Coverage](https://scrutinizer-ci.com/g/neoglez/BatchManager/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/neoglez/BatchManager/?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/neoglez/BatchManager/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/neoglez/BatchManager/?branch=master)

Introduction
------------

The BatchManager is an attempt to port the [Drupal Batch API](https://www.drupal.org/node/180528) to the Zend Framework 2 event-driven, service-oriented arquitecture. The batch manager can be used to simulate or even implement asynchronous processing. It also tries to solve (in a rather naive way) the problem of scalability in a PHP environment where you usually have to increase the maximum execution time to accomplish a task depending on the size of some input. The Drupal Batch API is a very simple but powerful idea for a lot of practical use cases. If you are not familiar with it [go and give it a try] (https://www.drupal.org/node/180528).  So why then write this module? Well, Drupal Batch API depends on Drupal, which is a framework but also a CMS, so in my opinion it isn’t flexible enough; I also think that Drupal’s hook-philosophy reassemble that of an event driven, but with some limitations so this module aims to decouple the functionality while using the comprehensive ZF2 EventManager component.

Installation
------------

### Main setup

#### With composer

1. Load neoglez/batch-manager in your composer.json file.

    ```json
    "require": {
        "neoglez/batch-manager": "dev-master"
    }
    ```

2. Update composer

```bash
$ php composer.phar update
```

#### By cloning project

If you do not want to use composer, clone this project (either as a git submodule or not) into ./vendor/ directory.

### Post installation

1. Enable BatchManager in your `application.config.php`file.

    ```php
    <?php
    return array(
        'modules' => array(
            // ...
            'BatchManager',
        ),
        // ...
    );
    ```

2. To be able to use the assets under /batch-manager/public you can either install the module [AssetManager](https://github.com/RWOverdijk/AssetManager) or copy the files to your public directory.
