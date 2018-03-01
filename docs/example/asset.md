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
