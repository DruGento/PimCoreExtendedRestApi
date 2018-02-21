## Example: upload objects
You send POST request to url:

http://pimcore.loc/plugin/PimCoreExtendedRestApi/rest/objects?apikey=APIKEY

#### Upload new objects

Sending new data in JSON format (the `id` field can be zero or absent at all):
```
[
  {
    "key": "KEY-001",
    "id": 2191,
    "path": "",
    "userModification": "",
    "childs": null,
    "className": "Product",
    "parentId": 2107,
    "published": true,
    "type": "object",
    "userOwner": null,
    "elements": [
      {
        "type": "input",
        "value": "sku-1",
        "name": "sku",
        "language": null
      },
      {
        "type": "input",
        "value": "Product name 1",
        "name": "name",
        "language": null
      }
    ],
    "properties": null
  },
  {
    "key": "KEY-002",
    "id": 2192,
    "path": "",
    "userModification": "",
    "childs": null,
    "className": "Product",
    "parentId": 2107,
    "published": true,
    "type": "object",
    "userOwner": null,
    "elements": [
      {
        "type": "input",
        "value": "sku-2",
        "name": "sku",
        "language": null
      },
      {
        "type": "input",
        "value": "Product name 2",
        "name": "name",
        "language": null
      }
    ],
    "properties": null
  },
]
```

And get the next answer:

```
{
    "success": true,
    "data": [
        {
            "key": "KEY-001",
            "id": 2191,
            "success": true
        },
        {
            "key": "KEY-002",
            "id": 2192,
            "success": true
        }
    ]
}
```

#### Upload old objects (update)

Sending data in JSON format (the `id` field must be present and not be null):
```
[
  {
    "key": "KEY-001",
    "id": 2185,
    "path": "",
    "userModification": "",
    "childs": null,
    "className": "Product",
    "parentId": 2107,
    "published": true,
    "type": "object",
    "userOwner": null,
    "elements": [
      {
        "type": "input",
        "value": "sku-1",
        "name": "sku",
        "language": null
      },
      {
        "type": "input",
        "value": "Product name 1 (update)",
        "name": "name",
        "language": null
      }
    ],
    "properties": null
  },
  {
    "key": "KEY-002",
    "id": 0,
    "path": "",
    "userModification": "",
    "childs": null,
    "className": "Product",
    "parentId": 2107,
    "published": true,
    "type": "object",
    "userOwner": null,
    "elements": [
      {
        "type": "input",
        "value": "sku-2",
        "name": "sku",
        "language": null
      },
      {
        "type": "input",
        "value": "Product name 2",
        "name": "name",
        "language": null
      }
    ],
    "properties": null
  }
]
```

And get the next answer:

```
{
    "success": true,
    "data": [
        {
            "key": "KEY-001",
            "id": 2185,
            "success": true
        },
        {
            "key": "KEY-002",
            "id": null,
            "msg": "Exception: Duplicate full path [ /Stores/Products/Non active products/Uncategorized/KEY-002 ] - cannot save object in /var/www/misc/drimmers.pimcore.loc/pimcore/models/Object/AbstractObject.php:692\nStack trace:\n#0 /var/www/misc/drimmers.pimcore.loc/pimcore/models/Object/AbstractObject.php(556): Pimcore\\Model\\Object\\AbstractObject->correctPath()\n#1 /var/www/misc/drimmers.pimcore.loc/pimcore/models/Webservice/Service.php(951): Pimcore\\Model\\Object\\AbstractObject->save()\n#2 /var/www/misc/drimmers.pimcore.loc/pimcore/models/Webservice/Service.php(651): Pimcore\\Model\\Webservice\\Service->create(Object(Pimcore\\Model\\Webservice\\Data\\Object\\Concrete\\In), Object(Pimcore\\Model\\Object\\Product))\n#3 /var/www/misc/drimmers.pimcore.loc/pimcore/modules/webservice/controllers/RestController.php(182): Pimcore\\Model\\Webservice\\Service->createObjectConcrete(Object(Pimcore\\Model\\Webservice\\Data\\Object\\Concrete\\In))\n#4 /var/www/misc/drimmers.pimcore.loc/vendor/zendframework/zendframework1/library/Zend/Controller/Action.php(516): Webservice_RestController->objectsAction()\n#5 /var/www/misc/drimmers.pimcore.loc/vendor/zendframework/zendframework1/library/Zend/Controller/Dispatcher/Standard.php(308): Zend_Controller_Action->dispatch('objectsAction')\n#6 /var/www/misc/drimmers.pimcore.loc/vendor/zendframework/zendframework1/library/Zend/Controller/Front.php(954): Zend_Controller_Dispatcher_Standard->dispatch(Object(Zend_Controller_Request_Http), Object(Zend_Controller_Response_Http))\n#7 /var/www/misc/drimmers.pimcore.loc/pimcore/lib/Pimcore.php(152): Zend_Controller_Front->dispatch(Object(Zend_Controller_Request_Http), Object(Zend_Controller_Response_Http))\n#8 /var/www/misc/drimmers.pimcore.loc/pimcore/lib/Pimcore.php(125): Pimcore::runDispatcher(Object(Zend_Controller_Front), false, NULL, NULL)\n#9 /var/www/misc/drimmers.pimcore.loc/index.php(18): Pimcore::run()\n#10 {main}",
            "success": false
        }
    ]
}
```
