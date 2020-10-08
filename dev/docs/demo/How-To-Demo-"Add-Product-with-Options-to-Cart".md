## Step #1
Create a product with options.
***
### Step #2
Create cartId.

_Example Mutation:_
```
 mutation {
   createEmptyCart
 }
```
_Example Response:_
```
 {
   "data": {
     "createEmptyCart": "dM1ZlCDjKPfNH85CIWU5RJyY8dYuMiwT"
   }
 }
```
***
## Step #3
Add Product to cart.

_Example Mutation: (edit and left the only fields you need)_
```
mutation{
  addProductsToCart(
    cartId: "type your cartId here", 
    cartItems: 
  [
           {
                sku: "type created product SKU here"
                quantity: 1
                    selected_options: [
                    	"Dropdown Option UID",                # for customizable option
                        "Radio Buttons Option UID",           # for customizable option
                        "Checkbox Option UID",                # for customizable option
                        "Multiple Select Option 1 UID",       # for customizable option
                        "Multiple Select Option 2 UID",       # for customizable option
                        "Configurable Product Option 1 UID",  
                        "Configurable Product Option 2 UID",  
                        "Bundle Product Option 1 UID",        
                        "Bundle Product Option 2 UID",
                        "Downloadable Product Option 1 UID",        
                        "Downloadable Product Option 2 UID"          
                   ],
                    entered_options: [{
                        uid: "Text Field UID"         # for customizable option
                        value: "Text Field Text"
                    },
                      {
                        uid: "Text Area UID"          # for customizable option
                        value: "Text Area Text"
                    },
                   {
                        uid: "Date Option UID"        # for customizable option
                        value: "2020-09-02 17:16:18"
                    },
                   {
                        uid: "Date&Time Option UID"   # for customizable option
                        value: "2020-09-02 17:16:18"
                    },
                   {
                        uid: "Time Option UID"        # for customizable option
                        value: "2020-09-02 17:16:18"
                    },
                    ]
            }
  ]
  ) {
    cart
    {
      id
      items
      {
        id
        quantity
        product
        {
          id
          name
        }
      }
    }
    user_errors
    {
      message
      code
    }
  }
}
```
***
## Step #4
Expected Result - product is added to Cart with selected options.

_Exemple Response:_
***
## Consider The Following

### Product Types and Options Mapping
Customizable Options is available for all product types, see additional info in Customizable Options Section
***
### Customizable Options:

**Customizable Options Types**

Selectable Types: _(need just UID)_
* Drop-down
* Radio Buttons
* Checkbox
* Multiple Select

Enterable Types: _(need UID and value)_
* Text Field
* Text Area
* File
* Date
* Date & Time
* Time

**Required Customizable Options**

The product shouldn't be added to the cart until all required customizable options are selected or entered.

_Error Message Example:_ 
```
      "user_errors": [
        {
          "message": "The product's required option(s) weren't entered. Make sure the options are entered and try again.",
          "code": "UNDEFINED"
        }
      ]
```
**Multiple Select Customizable Option Type**

Several UID could be specified at one time
***