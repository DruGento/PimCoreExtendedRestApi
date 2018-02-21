## Example: search and download objects
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