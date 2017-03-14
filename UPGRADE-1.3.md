# UPGRADE FROM 1.2 to 1.3

Some namespaces changed for Akeneo PIM 1.6 compatibility and you can reflect them in your custom by running the following commands:

```bash
    find ./src -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnhancedConnectorBundle\\Processor\\AttributeToFlatArrayProcessor/Pim\\Bundle\\EnhancedConnectorBundle\\Processor\\Normalization\\AttributeProcessor/g'
    find ./src -type f -print0 | xargs -0 sed -i 's/Pim\\Bundle\\EnhancedConnectorBundle\\Processor\\FamilyToFlatArrayProcessor/Pim\\Bundle\\EnhancedConnectorBundle\\Processor\\Normalization\\FamilyProcessor/g'
```
