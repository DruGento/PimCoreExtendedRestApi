## Example: search and download objects

#### Search and download objects with all elements
You send search request with standard [condition](https://pimcore.com/docs/4.6.x/Development_Documentation/Web_Services/index.html#page_Search-Objects):

http://pimcore.loc/plugin/PimCoreExtendedRestApi/rest/object-list?apikey=APIKEY&objectClass=Product1&limit=2

And get detailed answer:

```
{
    "success": true,
    "data": [
        {
            "id": 1,
            "success": true,
            "data": {
                "path": "/",
                "creationDate": 1502179437,
                "modificationDate": 1502179437,
                "userModification": 1,
                "id": 1,
                "parentId": null,
                "key": null,
                "published": null,
                "type": "folder",
                "userOwner": 1,
                "properties": null
            }
        },
        {
            "id": 10,
            "success": true,
            "data": {
                "path": "/Stores/Brands/",
                "creationDate": 1502180350,
                "modificationDate": 1502180350,
                "userModification": null,
                "childs": null,
                "elements": [
                    {
                        "type": "input",
                        "value": "Amana",
                        "name": "name",
                        "language": null
                    }
                ],
                "className": "Brand",
                "id": 10,
                "parentId": 6,
                "key": "Amana",
                "published": true,
                "type": "object",
                "userOwner": null,
                "properties": null
            }
        }       
```

If the results not found you will get the next answer:

```
{
    "success": true,
    "data": []
}
```


#### Search and download objects with filtered elements

You send search request with param `elements`:

http://pimcore.loc/plugin/PimCoreExtendedRestApi/rest/object-list?apikey=APIKEY&objectClass=Product&limit=2&condition=model+like+%27%25DWT25%25%27&elements=name,model

And get answer only with requested elements:
````
{
    "success": true,
    "data": [
        {
            "id": 2108,
            "success": true,
            "data": {
                "path": "/Stores/Products/Non active products/Uncategorized/",
                "creationDate": 1502196287,
                "modificationDate": 1502201007,
                "userModification": 2,
                "childs": null,
                "elements": [
                    {
                        "type": "input",
                        "value": "DWT25502B",
                        "name": "model",
                        "language": null
                    },
                    {
                        "type": "input",
                        "value": "Tall Tub dishwasher 5 cycle front control black 49 dBA",
                        "name": "name",
                        "language": null
                    }
                ],
                "className": "Product",
                "id": 2108,
                "parentId": 2107,
                "key": "DWT25502B",
                "published": true,
                "type": "object",
                "userOwner": null,
                "properties": null
            }
        },
        {
            "id": 2109,
            "success": true,
            "data": {
                "path": "/Stores/Products/Non active products/Uncategorized/DWT25502B/",
                "creationDate": 1502196288,
                "modificationDate": 1502197161,
                "userModification": null,
                "childs": null,
                "elements": [
                    {
                        "type": "input",
                        "value": "DWT25502W",
                        "name": "model",
                        "language": null
                    },
                    {
                        "type": "input",
                        "value": "White",
                        "name": "name",
                        "language": null
                    }
                ],
                "className": "Product",
                "id": 2109,
                "parentId": 2108,
                "key": "DWT25502W",
                "published": true,
                "type": "variant",
                "userOwner": null,
                "properties": null
            }
        }
    ]
}
````