### design document
https://github.com/magento/architecture/blob/4f28fde7aa0e24b6e0b834cd35591637b40f60b6/design-documents/storefront/catalog/product-options-and-variants.md#storefront-api

### catalog.proto updates:

- ProductVariation contains `id` and `product_id` (in current implementation id=product id, need to be fixed)

## Configurable product variants overview

Let's assume we have configurable product [id=42] with 2 options:

Options:
>    color: [red, blue]
>
>    size: [l, xl]

[`color`, `size`] are attribute codes (options)

[`red`, `blue`, `l`, `xl`] are option values

System has 3 simple products which reflects 3 combinations of those options (blue-xl, red-xl, red-l)

1. simple "Blue XL" {id: 1, storeViewId: ["default"],               color: "blue",  size: "xl"}
2. simple "Red XL"  {id: 2, storeViewId: ["default", "storeview2"], color: "red",   size: "xl"}
3. simple "Red L"   {id: 3, storeViewId: ["default", "storeview2"], color: "red",   size: "l"}

## Commerce Data Export
in `et_schema.xml` there's the following structure for ProductVariant:
```
    <record name="ProductVariant">
        <field name="id" type="ID" />
        <field name="option_valuess" type="string" repeated="true" />
        <field name="product_id" type="string"/>
    </record>
```
where

`product_id` = 1,2,3 in our case

`id` (variantIdBasedOnPIM) is a unique field that describes product variant and has a specific format.

`option_values` represents an array of product option values and has a specific format.

### Variant `id` and `option_values` generation

**`id`** (variantId) has the following format

`:prefix:/:parentId:/:simpleProductId:` where

`:prefix:` - for the configurable product is `configurable`

`:parentId:` - is configurable product id (parent_id) that is `42`.

`:simpleProductId:` - is an id of simple product that represents variation.

for other types of product `id` of variant will be the following:

**bundle** - `bundle/:bundleProductId:/:bundleProductOptionId:`// subject to change

**grouped** - `grouped/:simpleProductId:`// subject to change

**downloadable** - `downloadable/:downloadableProductId:` // subject to change

**custom_option** - `custom-option/:customOptionId:/:customOptionValueUID:`// subject to change

in our case we have the following ids:

`configurable/42/1` (blue-xl) --> variantId1BasedOnPIM

`configurable/42/2` (red-xl) --> variantId2BasedOnPIM

`configurable/42/3` (red-l) --> variantId3BasedOnPIM


**`option_values`** has the following format

`parent_id:option_id/optionValue.uid` where

`parent_id` - is configurable product id (parent_id) that is `42`.

Note:
for configurable/bundle parent_id = configurable/bundle product id respectively
for other types of options downloadable/custom-options parent_id = simple-product-id

`:option_id:` - for configurable product `attribute_code` is used to determine option_id.
```
<record name="ProductOptionValue">
    ...
    <field name="id" type="ID" /> <!-- attribute_code of product custom attribute for configurable product
    ...
</record>
```

`:optionValue.uid:` - is uid of product option value (see `\Magento\CatalogDataExporter\Model\Provider\Product\ProductOptions\OptionValueUid`)

| optionValue.uid | explanation |
|:---|:---|
|`Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==`|`base64_encode('configurable/color/:blue-id:')`|
|`Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6`|      `base64_encode('configurable/size/:xl-id:')`|
|`Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOnJlZC1pZDo=`|  `base64_encode('configurable/color/:red-id:')`|
|`Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6` |     `base64_encode('configurable/size/:xl-id:')`|
|`Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOnJlZC1pZDo=` | `base64_encode('configurable/color/:red-id:')`|
|`Y29uZmlndXJhYmxlLzpzaXplLWlkOi86bC1pZDo=`   |   `base64_encode('configurable/size/:l-id:')`|

so exported `ProductVariants` will look like this:

| product_id   | id | feed_data    |
|----:|:---:|----|
| 1 | `configurable/42/1` | `{"id":"configurable/42/1","product_id": 1,"option_values":["42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==", "42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6"]}` |
| 2 | `configurable/42/2` | `{"id":"configurable/42/2","product_id": 2,"option_values":["42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOnJlZC1pZDo=", "42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6"]}` |
| 3 | `configurable/42/3` | `{"id":"configurable/42/3","product_id": 3,"option_values":["42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOnJlZC1pZDo=", "42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86bC1pZDo="]}` |

## Storefront Application part

Import API (`importProductVariants`) should split `option_values` and save them as separate records into SF App storage.
Parent id can be parsed from `option_values` too

| product_id   | id | feed_data    |
|----:|:---:|----|
| 1 | `configurable/42/1` | `{"id":"configurable/42/1","product_id": 1,"option_values":["42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==", "42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6"]}` |
becomes

 | id                                      |  option_value | product_id   | parent_id
 |---|----|---:|---:|
 | `configurable/42/1`  |  `42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==` | 1 | 42|
 | `configurable/42/1`  |  `42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6`        | 1 | 42|
in SF storage

Imported variants looks like the following:

 | id                                      |  option_value | product_id   | parent_id
 |---|----|---:|---:|
 | `configurable/42/1`  |  `42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==` | 1 | 42|
 | `configurable/42/1`  |  `42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6`        | 1 | 42|
 | `configurable/42/2`  |  `42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOnJlZC1pZDo=`   | 2 | 42|
 | `configurable/42/2`  |  `42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6`        | 2 | 42|
 | `configurable/42/3`  |  `42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOnJlZC1pZDo=`   | 3 | 42|
 | `configurable/42/3`  |  `42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86bC1pZDo=`        | 3 | 42|

## Variant service requests.

The `proto` scheme has the following structure for variants service
```
message OptionSelectionRequest
{
    string storeViewId = 1;
    repeated string values = 2;
}
message ProductVariantResponse {
    repeated ProductVariant matchedVariants = 3;
}
service VariantSearchService {
    // get all variants that belong to a product
    rpc getProductVariants (ProductVariantRequest) returns (ProductVariantResponse);
    // match the variants which correspond, and do not contradict, the merchant selection (%like operation)
    rpc getVariantsMatch (OptionSelectionRequest) returns (ProductVariantResponse);
    // match the variants which exactly matched with merchant selection (&& operation)
    rpc getVariantsExactlyMatch (OptionSelectionRequest) returns (ProductVariantResponse);
    // get all variants which contain at least one of merchant selection (|| operation)
    rpc getVariantsInclude (OptionSelectionRequest) returns (ProductVariantResponse);
}
```

### Get all variants for a configurable product with id `42`
`rpc getProductVariants (ProductVariantRequest) returns (ProductVariantResponse)`

1. internal Request to get variants data for by parent id:
`getVariants(parent_id, ...)`

```
$parentId = 42;
$productVariantResponse = $variantService->getVariants($parentId);
```

``
// proto
message ProductVariantResponse {
    repeated ProductVariant matchedVariants = 3;
}
``
Result has `ProductVariantResponse` like data.
```
[
    {
        id: "configurable/42/1"
        option_value: "42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==",
        product_id: 1,
        parent_id: 42
    },
    {
        id: "configurable/42/1"
        option_value: "42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6",
        product_id: 1,
        parent_id: 42
    },
    {
        id: "configurable/42/2"
        option_value: "42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOnJlZC1pZDo=",
        product_id: 2,
        parent_id: 42
    },
    ....
]
```

2. Collect these `product_id` (simple product ids) from `$productVariantResponse` and make request to products storage to find only those are available.
```
// proto
rpc getProducts (ProductsGetRequest) returns (ProductsGetResult) {}
```
@see `\Magento\CatalogStorefront\Model\CatalogService::getProducts` for existing `getProducts` service implementation

**ProductsGetRequest**:
```
{
    [1, 2, 3],// product_ids
    3,// storeViewId
    ["id", "status"] // attribute_code
}
```

**ProductsGetResult**:
```
[
    {
        id: 1, // simple product id that represents blue-xl variant
        status: true
    },{
        id: 2, // simple product id that represents red-xml variant
        status: false
    },{
        id: 3, // simple product id that represents red-l variant
        status: true
    },
]
```

3. In a step *1* all possible variants fetched for configurable product with id `42`.
Now need filter them and return simple product that are available (`status: true` from step *2*)
As final result product variants that related to products #1 and #3.
```
[
    {
        id: "configurable/42/1"
        option_value: "42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==",
        product_id: 1,
        parent_id: 42
    },
    {
        id: "configurable/42/1"
        option_value: "42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6",
        product_id: 1,
        parent_id: 42
    },
    {
        id: "configurable/42/3"
        option_value: "42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOnJlZC1pZDo=",
        product_id: 3,
        parent_id: 42
    },
    {
        id: "configurable/42/3"
        option_value: "42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86bC1pZDo=",
        product_id: 3,
        parent_id: 42
    }
]
```

For explanation of other service refer to this document https://github.com/magento/catalog-storefront/blob/develop/dev/docs/designDocuments/Product-Variants-services.md
