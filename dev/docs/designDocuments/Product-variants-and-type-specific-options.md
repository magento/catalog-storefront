## Product specific configurations
We have the following product types with specific options and in some cases variants.

Grouped has variants data
* position
* default qty
* child ids

![Grouped product options](https://github.com/magento/catalog-storefront/blob/wiki-images/images/grouped-product-options.png?raw=true)

Configurable has variants data
* super attribute (position, store_id, use_default, label)
* child ids

![Configurable product options](https://github.com/magento/catalog-storefront/blob/wiki-images/images/configurable-product-options.png?raw=true)


Bundle has variants data
* option (type, required, label, store_id)
* option value (is default, default qty, user defined, product)

![Bundle product options](https://github.com/magento/catalog-storefront/blob/wiki-images/images/bundle-product-options.png?raw=true)

Gift card, variants are not applicable but has specific options.

![Gift card product options](https://github.com/magento/catalog-storefront/blob/wiki-images/images/gift-card-product-options.png?raw=true)

Downloadable, variants are not applicable but has specific options.

![Downloadable product options](https://github.com/magento/catalog-storefront/blob/wiki-images/images/downloadable-product-options.png?raw=true)

## Common interface for variants
Variant
```
/**
  * @return string
  */
public getParentId();
 
/**
  * @return string
  */
public getProductId();
 
/**
  * @return int
  */
public getDefaultQty();
 
/**
  * @return boolean
  */
public getCanChangeQty();
 
/**
  * @return int[]
  */
public getOptionsValueIds();
 
/**
  * @return float
  */
public getPrice();
```

Note: is default add to options on the product


Data for products that have variants may look like this.
```
// option_value_ids is the reference to id of OptionValue
    // grouped variant
    {
        "parent_id": 1,
        "product_id": 2,
        "default_qty": 10,
        "can_change_qty": true,
        "position": 1
    },
    // configurable variant
    {
        "parent_id": 1,
        "product_id": 2,
        "position": 1,
        "option_value_ids": [1, 2]
    },
    // bundle variant
    {
        "parent_id": 1,
        "product_id": 2,
        "default_qty": 1,
        "can_change_qty": false,
        "position": 1,
        "option_value_ids": [1, 2],
        "price": "10"
    }
]
```

As some products have thousands variations, and products API serve hundreds of products we want to create a new endpoint for variants.

GET /V1/catalog-export/product/variants?ids[0]=100&ids[0]=101&cursor=1


Where
* ids - id of parent product
* cursor id of last returned product, so that we can paginate result

After first iteration we are going to add and publish new event when parent product changes. As product relations rarely change and usually change as deltas, we could optimize communication by introducing events and probably other APIs to track changes on the relation level.

## Interfaces to represent options

Option
```
/**
  * @return string
  */
public getType();

/**
  * @return string
  */
public getId();

/**
  * @return string
  */
public getLabel();

/**
  * @return string
  */
public getSortOrder();

/**
  * @return string
  */
public getIsRequired();

/**
  * @return OptionValue
  */
public getValues();
```

OptionValue
```
/**
  * @return string
  */
public getId();

/**
  * @return string
  */
public getLabel();

/**
  * @return string
  */
public getSortOrder();

/**
  * @return string
  */
public getPrice();

/**
  * @return string
  */
public getSample();
```

Data for different product options may look like this. Interfaces are being added in this PR https://github.com/magento/catalog-storefront/pull/88.

```
[
    {
        // Grouped doesn't have product options, only variants
    },
    {
        "id": "2",
        "name": "configurable product",
        "custom_options": [
            {
                "type": "super",
                "id": "1", // catalog_product_super_attribute.product_super_attribute_id
                "label": "Color", // catalog_product_super_attribute_label.value
                "sort_order": "1", // catalog_product_super_attribute.position
                "values": [
                    {
                        "id": "1", // catalog_product_entity_int.value - id of Variant
                        "label": "Red"
                    },
                    {
                        "id": "2", // catalog_product_entity_int.value - id of Variant
                        "label": "Blue"
                    }
                ]
            }
        ]
    },
    {
        "id": "3",
        "name": "bundle product",
        "custom_options": [
            {
                "type": "super",
                "id": "1",
                "label": "Bundle option",
                "sort_order": "1",
                "values": [
                    {
                        "id": "1", // id of Variant
                        "label": "configurable-blue",
                        "sort_order": "1",
                        "is_default": false
                    },
                    {
                        "id": "2", // id of Variant
                        "label": "configurable-yellow",
                        "sort_order": "2",
                        "is_default": true
                    }
                ]
            }
        ]
    },
    {
        "id": "3",
        "name": "gift card product no open amount",
        "custom_options": [
            {
                "type": "giftcard_amount",
                "id": "134", // eav_attribute.attribute_id
                "values": [
                    {
                        "id": "1", // magento_giftcard_amount.value_id
                        "value": "10"
                    },
                    {
                        "id": "2", // magento_giftcard_amount.value_id
                        "value": "10"
                    }
                ]
            }
        ]
    },
    {
        "id": "5",
        "name": "Downloadable product",
        "custom_options": [
            {
                "type": "links",
                "is_required": "true",
                "label": "Links",
                "values": [
                    {
                        "id": "1", // downloadable_link.link_id
                        "label": "Option 1", // downloadable_link_title.title
                        "sample": "http://domain.com/path/to/sample-for-option-1", // downloadable_link.link_file
                        "price": "10" // downloadable_link_price.price
                    },
                    {
                        "id": "2", // downloadable_link.link_id
                        "label": "Option 2", // downloadable_link_title.title
                        "sample": "http://domain.com/path/to/sample-for-option-2", // downloadable_link.link_file
                        "price": "15" // downloadable_link_price.price
                    }
                ]
            }
        ]
    }
]
```