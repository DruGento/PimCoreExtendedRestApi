# PimCoreExtendedRestApi

## Introduction ##
Adds additional REST API calls to default PIMCore API

## Requirements
* Pimcore 4.6
* wget

## Installation ##

Install Pimcore plugin:
  * [download plugin as zip achive](https://github.com/DruGento/PimCoreExtendedRestApi/archive/master.zip)
  * navigate to `Tools -> Extensions` in admin panel
  * press `Upload Plugin (ZIP)`
  * click `Enable` & `Install`
  * reload admin interface

If everything went well you will see plugin in `Manage extensions`:

![Pimcore manage extensions](docs/images/Pimcore%20mange%20extensions.png)

## Features ##

*   multiple objects upload (creation) by POST method - [upload example](docs/example/upload.md) - instead of standard single [object creation](https://pimcore.com/docs/4.6.x/Development_Documentation/Web_Services/index.html#page_Create-a-new-Object)
*   multiple objects search with detailed results in JSON format - [search example](docs/example/search.md) - instead of standard [search](https://pimcore.com/docs/4.6.x/Development_Documentation/Web_Services/index.html#page_Search-Objects) with short results
*   create/update asset by url with metadata - [example](docs/example/asset.md)
