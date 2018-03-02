## Asset example
To work with Assets, you can use the PimCore default [functionality](https://pimcore.com/docs/4.6.x/Development_Documentation/Web_Services/index.html#page_Create-New-Asset).

But if you do not want to transfer the encoded image content in the POST-request, you can use the link (`url` field) to the image in the request.
You send POST request to **create/update** asset on following url:

http://pimcore.loc/plugin/PimCoreExtendedRestApi/rest/asset?apikey=APIKEY

Sending data in JSON format (the `id` field can be zero or absent at all on asset *create* and must be present and not be null on asset *update*):
```
{
  "url": "http://example.com/images/test.jpg",
  "parentId": 1,
  "path": "/Photos/",
  "type": "image",
  "filename": "test.jpg",
  "mimetype": "image/jpg",
  "metadata": [
    {
      "name": "title",
      "type": "input",
      "data": "New title",
      "language": "en"
    },
    {
      "name": "alt",
      "type": "input",
      "data": "New alt",
      "language": "en"
    },
    {
      "name": "description",
      "type": "textarea",
      "data": "the new description",
      "language": "en"
    }
  ]
}

```

And get the next response:

```
{
    "success": true,
    "data": {
        "id": 100
    }
}
```

If the wrong URL was specified you will get the following response:
```
{
    "success": false,
    "msg": "Unable to download image by url http://example.com/images/test.jpg"
}
```

#### Update metadata
Please use `saveOldMetadata` flag in POST request body if you want to keep old metadata and to add new data to them:

````
{
  "id": 100,
  "url": "http://example.com/images/test.jpg",
  "parentId": 1,
  "path": "/Photos/",
  "type": "image",
  "filename": "test.jpg",
  "mimetype": "image/jpg",
  "saveOldMetadata": true,
  "metadata": [
    {
      "name": "title",
      "type": "input",
      "data": "New title after update",
      "language": "en"
    },
    {
      "name": "copyright",
      "type": "input",
      "data": "2018",
      "language": "en"
    }
  ]
}
````

You can check result with standard Pimcore request:

http://pimcore.loc/webservice/rest/asset/id/100?apikey=APIKEY

And you will get the next response:
````
{
    "success": true,
    "data": {
        "id": 100,
        "parentId": 1,
        "type": "image",
        "filename": "test.jpg",
        ...
        "metadata": [
            {
                "name": "title",
                "language": "en",
                "type": "input",
                "data": "New title after update"
            },
            {
                "name": "alt",
                "language": "en",
                "type": "input",
                "data": "New alt"
            },
            {
                "name": "description",
                "language": "en",
                "type": "textarea",
                "data": "the new description"
            },
            {
                "name": "copyright",
                "language": "en",
                "type": "input",
                "data": "© John Doe"
            }
        ],
        ...
    }
}
````

If you do not specify flag `saveOldMetadata` or it is will be `false`, then PimCore will delete old metadata and load new ones from the request body if they are specified.