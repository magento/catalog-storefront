See https://github.com/magento/catalog-storefront/blob/develop/dev/docs/designDocuments/Configurable-products-option-variants.md as base document

Let's assume we have configurable product [id=42] with 2 options:

Options:
>    color: [red, blue]
>
>    size: [l, xl]
>
>
SF App storage:

 | id              |  option_value | product_id   | parent_id
 |---|----|---:|---:|
 | `configurable/42/1`  |  `42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==` | 1 | 42|
 | `configurable/42/1`  |  `42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6`        | 1 | 42|
 | `configurable/42/2`  |  `42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOnJlZC1pZDo=`   | 2 | 42|
 | `configurable/42/2`  |  `42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6`        | 2 | 42|
 | `configurable/42/3`  |  `42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOnJlZC1pZDo=`   | 3 | 42|
 | `configurable/42/3`  |  `42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86bC1pZDo=`        | 3 | 42|
```
message ProductVariant {
  string id = 1;// convention :prefix:/:parentId:/:entityId:
  repeated string option_values = 2;// parent_id:option_id/optionValue.uid
  string product_id = 3;
}
message OptionSelectionRequest
{
  string store = 1;
  // array of option_values with the following format parent_id:option_id/optionValue.uid
  repeated string values = 2;
}
message ProductVariantResponse {
  repeated ProductVariant matched_variants = 3;
}
```
------------------------------------------------------
rpc getVariantsExactlyMatch (OptionSelectionRequest) returns (ProductVariantResponse) {}

REQUEST #1.1:
```
{
    store: 1,
    values: [
        '42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==',
        '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
    ]
}
```
Response #1.1 (fully match only 1 record):
```
[
    {
        id: 'configurable/42/1',
        option_values: [
            '42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==',
            '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
        ]
        product_id: 1
    }
]
```


REQUEST #1.2:
```
{
    store: 1,
    values: [
        '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
    ]
}
```
Response #1.2:
```
[
]
```
`getVariantsExactlyMatch` should return only fully matched records.
`42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6` option value is present in `configurable/42/1` and `configurable/42/2` variants,
but matched variants partially, because each variant has 2 option values, requested values should match both records as into Request 1.1

------------------------------------------------------
rpc getVariantsInclude (OptionSelectionRequest) returns (ProductVariantResponse) {}

REQUEST #2.1:
```
{
    store: 1,
    values: [
        '42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==',
        '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
    ]
}
```
Response #2.1 (2 variants matches with 3 option_values):
```
[
    {
        id: 'configurable/42/1',
        option_values: [
            '42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==',
            '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
        ]
        product_id: 1
    },
        id: 'configurable/42/2',
        option_values: [
            '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
        ]
        product_id: 2
    }
]
```

REQUEST #2.2:
```
{
    store: 1,
    values: [
        '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
    ]
}
```
Response #2.2 (2 variants matches with 3 option_values):
```
[
    {
        id: 'configurable/42/1',
        option_values: [
            '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
        ]
        product_id: 1
    },
        id: 'configurable/42/2',
        option_values: [
            '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
        ]
        product_id: 2
    }
]
```

------------------------------------------------------
rpc getVariantsMatch (OptionSelectionRequest) returns (ProductVariantResponse) {}

REQUEST #3.1:
```
{
    store: 1,
    values: [
        '42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==',
        '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
    ]
}
```

Result of search into storage will look like this:
 
 | id              |  option_value | product_id   | parent_id | weight
 |---|----|---:|---:|---:|        
 | `configurable/42/1`  |  `42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==` | 1 | 42| 2
 | `configurable/42/1`  |  `42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6`        | 1 | 42| 2
 | `configurable/42/2`  |  `42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6`        | 2 | 42| 1

`configurable/42/1` has weight result 2 (`group by id`), so this variant with 2 option values is a result.

Response #3.1:
```
[
    {
        id: 'configurable/42/1',
        option_values: [
            '42:color/Y29uZmlndXJhYmxlLzpjb2xvci1pZDovOmJsdWUtaWQ6==',
            '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
        ]
        product_id: 1
    }
]
```

REQUEST #3.2:
```
{
    store: 1,
    values: [
        '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
    ]
}
```

Result of search into storage will look like this:
 
 | id              |  option_value | product_id   | parent_id | weight
 |---|----|---:|---:|---:|        
 | `configurable/42/1`  |  `42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6`        | 1 | 42| 1
 | `configurable/42/2`  |  `42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6`        | 2 | 42| 1

`configurable/42/1` and `configurable/42/2` both have weight result 1 (`group by id`), so these 2 variants with 1 option value is a result of request.

Response #3.2 (2 variants matches with 2 option_values):
```
[
    {
        id: 'configurable/42/1',
        option_values: [
            '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
        ]
        product_id: 1
    },
        id: 'configurable/42/2',
        option_values: [
            '42:size/Y29uZmlndXJhYmxlLzpzaXplLWlkOi86eGwtaWQ6',
        ]
        product_id: 2
    }
]
```