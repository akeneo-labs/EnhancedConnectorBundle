# Akeneo Labs Enhanced connector bundle

This bundle adds some new exports to Akeneo:

 - Family export in CSV format for PimGento (only codes and labels).

 - Attribute export in CSV format for PimGento with corresponding family code.

 - Enhanced product export: choose if you want to export all products, products modified since a specific date or modified since the last export.

This bundle can be use as a replacement for the [DnD-MagentoConnectorBundle](https://github.com/Agence-DnD/DnD-MagentoConnectorBundle), to work with [PimGento](https://github.com/Agence-DnD/PIMGento).
However, it does not provide a SSH export as the DnD Magento connector bundle does. If you need to automatically send your exports to PimGento, you should set up a CRON task and include the SSH export in it.


## Requirements

| EnhancedConnectorBundle | Akeneo PIM Community Edition |
|:-----------------------:|:----------------------------:|
| v1.0.\*                 | v1.3.\*                      |
| v1.1.\*                 | v1.4.\*                      |
| v1.2.\*                 | v1.5.\*                      |


## Installation

Install the bundle with composer:

    $ php composer.phar require akeneo-labs/pim-enhanced-connector:~1.0

Enable the bundle in the `app/AppKernel.php` file:

.. code-block:: php

    public function registerBundles()
    {
        $bundles = [
            new Pim\Bundle\EnhancedConnectorBundle\PimEnhancedConnectorBundle()
        ]

        // ...

        return $bundles;
    }

Then clean the cache and reinstall the assets:

    php app/console cache:clear --env=prod

    php app/console pim:install:assets --env=prod


## Documentation

### Configuration

This section explains how to export your data from Akeneo PIM. If you want to know how to use them once exported, take a look at the [PimGento documentation](https://github.com/Agence-DnD/PIMGento#configuration-and-usage).

Go to ```Spread > Export``` and create the export you need (note that you can export your data in whatever order you want, only PimGento requires that you import data in a precise order, the same that is used below):

* Category export: use the standard Akeneo CSV export for category exports.

* Family export: use the "Export families to CSV for PimGento" job from the Enhanced connector bundle.

* Attribute export: use the "Export attributes to CSV for PimGento" job from the Enhanced connector bundle.

* Attribute option export: use the standard Akeneo CSV export for attribute options.

* Product export: use the "Export products using enhanced product reader" job from the Enhanced connector bundle.

All these exports are configured like standards CSV Akeneo exports: you need to define a delimitor (the character separating the elements on a same line), an enclosure (for instance, if a label contain spaces, it needs to be enclose to avoid import errors), if you want headers in your file, and the file path to save your export.

However, the family and the product exports adds a few new configuration fields, as explained below.

### Family export

You need to choose which in which language you want to export the family label, as PimGento needs only one label (ideally, the language should correspond to the Magento locale for the administration interface).

### Product export

Like with the standard product export, you need to define a channel to export from, as your products could be different from a channel to another.

However, the standard product export allows only to export complete, enable and categorized products. The enhanced export allows you to chose if you want to export only enable, only disable or both, only complete, only incomplete or both, and finaly only categorized, only uncategorized or both.

You can also choose to export the products updated since the last time you run the job, since a precise date that you give in the configuration, or regardless of their last update (i.e. all the products).
