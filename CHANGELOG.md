# 1.4.1 (2017-05-31)
## Bug fixes
- GITHUB-90: family export doesnt export label

# 1.2.3 (2017-04-11)
## Bug fixes
- ECB-13: fix ProductReader returning false 

# 1.4.0 (2017-03-14)
## Improvements
- Compatibility with PIM 1.7 community edition 1.7
- Compatibility with PIM 1.7 enterprise edition 1.7
- Remove all FormConfigurationProviders to use the new PEF for Jobs

# 1.3.0 (2016-08-05)
## Improvements
- Compatibility with PIM community edition 1.6
- Compatibility with PIM enterprise edition 1.6
- Remove the customized product export as the PIM 1.6 supports same filtering features (completeness, last export date)
- Move `Pim\Bundle\EnhancedConnectorBundle\Processor\AttributeToFlatArrayProcessor` to `Pim\Bundle\EnhancedConnectorBundle\Processor\Normalization\AttributeProcessor`
- Move `Pim\Bundle\EnhancedConnectorBundle\Processor\FamilyToFlatArrayProcessor` to `Pim\Bundle\EnhancedConnectorBundle\Processor\Normalization\FamilyProcessor`

# 1.2.2 - (2016-08-04)
## Improvements
- Use startTime instead of endTime for last execution date

# 1.2.1 - (2016-06-09)
## Improvements
- Use of object detacher in ProductReader is deprecated and will be removed in 1.3

# 1.2.0 - (2016-03-08)
## New feature
- Compatibility with Akeneo PIM 1.5

## Improvements
- Detach products after having read them instead of doing it in the processor

## BC Break
- Remove enhanced family export as it has been integrated in Akeneo PIM 1.5
- Replace service `pim_enhanced_connector.reader.orm.family` by `pim_base_connector.reader.orm.family`
- Replace service `pim_enhanced_connector.processor.product_to_flat_array` by `pim_base_connector.processor.product_to_flat_array`
- Added `akeneo_storage_utils.doctrine.object_detacher` in `pim_enhanced_connector.reader.product`

# 1.1 - (2016-01-28)
## Bug fixes
- ECB-7: diverted from 1.3 branch where a BC break was fixed

# 1.0.5 (2016-01-11)
## Bug fixes
- ECB-6: Detach products once processed to avoid memory leak

# 1.0.4 (2016-01-07)
## New feature
- ECB-5: Add a new option to export categorized and/or uncategorized products

# 1.0.3 (2015-10-28)
## Bug fixes
- ECB-3: Attribute labels are tranlated and exported to PimGento only for active locales
- ECB-4: Add missing translations on enhanced product export display

# 1.0.2 (2015-10-12)
## Bug fixes
- Correct a bug that prevents family export to PimGento if no label is filled
- Correct a bug that prevents attribute export to PimGento if the attribute is in no family

# 1.0.1 (2015-08-27)
## Bug fixes
- Using datagrids update filter results in an error caused by the "Greater or equal" operator used by the enhanced product reader

# 1.0 (2015-06-10)
- Initial release
