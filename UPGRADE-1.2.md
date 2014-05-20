UPGRADE FROM 1.1 to 1.2
=======================

General
-------

FlexibleEntityBundle
--------------------

As announced during last release in UPGRADE-1.1.md, the bundle has been removed.

CatalogBundle
-------------

In ./src/Pim/Bundle/CatalogBundle/Resources/config/managers.yml:

With 1.1 :
```
    flexible_class:               %pim_catalog.entity.product.class%
    flexible_value_class:         %pim_catalog.entity.product_value.class%
```

With 1.2 :
```
    product_class:                %pim_catalog.entity.product.class%
    product_value_class:          %pim_catalog.entity.product_value.class%
```

./src/Pim/Bundle/CatalogBundle/Manager/ProductManager.php has been updated to use these new configuration parameters


MongoDB implementation
----------------------

We removed null values from normalizedData field to avoid storing useless values

DataGridBundle
--------------

Change of ConfiguratorInterface and configurators are now services to make them easier to customize

OroSegmentationTreeBundle
--------------

The bundle has been removed from Oro Platform, entities extending AbstractSegment should implement the desired
methods themselves and repositories extending SegmentRepository should extend Gedmo\Tree\Entity\Repository\NestedTreeRepository
