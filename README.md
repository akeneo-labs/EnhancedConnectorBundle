# EnhancedConnectorBundle

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/akeneo-labs/EnhancedConnectorBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/akeneo-labs/EnhancedConnectorBundle/?branch=master)
[![Build Status](https://travis-ci.org/akeneo-labs/EnhancedConnectorBundle.svg?branch=master)](https://travis-ci.org/akeneo-labs/EnhancedConnectorBundle)

This bundle adds some new exports to Akeneo:

 - Family export in CSV format for PimGento (only codes and labels).

 - Attribute export in CSV format for PimGento with corresponding family code.

This bundle can be use as a replacement for the [DnD-MagentoConnectorBundle](https://github.com/Agence-DnD/DnD-MagentoConnectorBundle), to work with [PimGento](https://github.com/Agence-DnD/PIMGento).
However, it does not provide a SSH export as the DnD Magento connector bundle does. If you need to automatically send your exports to PimGento, you should set up a CRON task and include the SSH export in it.


## Requirements

| EnhancedConnectorBundle | Akeneo PIM Community Edition |
|:-----------------------:|:----------------------------:|
| v1.4.*                  | v1.7.*                       |
| v1.3.*                  | v1.6.*                       |
| v1.2.*                  | v1.5.*                       |
| v1.1.*                  | v1.4.*                       |
| v1.0.*                  | v1.3.*                       |


## Installation

Install the bundle with composer:

```bash
    php composer.phar require akeneo-labs/pim-enhanced-connector:~1.3
```

Enable the bundle in the `app/AppKernel.php` file:

```php
    public function registerProjectBundles()
    {
        return [
            new Pim\Bundle\EnhancedConnectorBundle\PimEnhancedConnectorBundle(),
            
            // ...
            
        ];
    }
```

Now let's clean your cache and dump your assets:

```bash
    php app/console cache:clear --env=prod
    php app/console pim:installer:assets --env=prod
```


## Documentation

### Configuration

This section explains how to export your data from Akeneo PIM. If you want to know how to use them once exported, take a look at the [PimGento documentation](https://github.com/Agence-DnD/PIMGento#configuration-and-usage).

Go to ```Spread > Export``` and create the export you need (note that you can export your data in whatever order you want, only PimGento requires that you import data in a precise order, the same that is used below):

1. Category export: use the standard Akeneo CSV export for category exports.

2. Family export: use the "Export families to CSV for PimGento" job from the Enhanced connector bundle.

3. Attribute export: use the "Export attributes to CSV for PimGento" job from the Enhanced connector bundle.

4. Attribute option export: use the standard Akeneo CSV export for attribute options.

5. Product export: use the standard "Export products" that you can configure through the Export Builder.

All these exports are configured like standards CSV Akeneo exports: you need to define a delimitor (the character separating the elements on a same line), an enclosure (for instance, if a label contain spaces, it needs to be enclose to avoid import errors), if you want headers in your file, and the file path to save your export.

However, the family and the product exports adds a few new configuration fields, as explained below.

### Family export

You need to choose which in which language you want to export the family label, as PimGento needs only one label (ideally, the language should correspond to the Magento locale for the administration interface).

