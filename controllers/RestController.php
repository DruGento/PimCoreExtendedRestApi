<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) 2018 Drugento Inc (http://www.drugento.com)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 *
 * @author     Drugento Team, team@drugento.com
 */

use Pimcore\Logger;
use Pimcore\Model\Asset;
use Pimcore\Model\Object;
use Pimcore\Model\Element;
use Pimcore\Model\Webservice;
use Pimcore\Tool;

class PimCoreExtendedRestApi_RestController extends \Pimcore\Controller\Action\Webservice
{
    const ELEMENT_DOES_NOT_EXIST = -1;

    /**
     * the webservice
     * @var
     */
    private $service;

    /**
     * The output encoder (e.g. json)
     * @var
     */
    private $encoder;


    public function init()
    {
        if ($this->getParam("condense")) {
            Object\ClassDefinition\Data::setDropNullValues(true);
            Webservice\Data\Object::setDropNullValues(true);
        }

        $profile = $this->getParam("profiling");
        if ($profile) {
            $startTs = microtime(true);
        }
        parent::init();
        $this->disableViewAutoRender();
        $this->service = new Webservice\Service();
        // initialize json encoder by default, maybe support xml in the near future
        $this->encoder = new Webservice\JsonEncoder();

        if ($profile) {
            $this->timeConsumedInit = round(microtime(true) - $startTs, 3)*1000;
        }
    }

    /**
     * @param Element\AbstractElement $element
     * @param $category
     *
     * @throws Zend_Controller_Response_Exception
     */
    private function checkPermission($element, $category)
    {
        if ($category == "get") {
            if (!$element->isAllowed("view")) {
                $this->getResponse()->setHttpResponseCode(403);
                $this->encoder->encode(["success" => false, "msg" => "not allowed, permission view is needed"]);
            }
        } elseif ($category == "delete") {
            if (!$element->isAllowed("delete")) {
                $this->getResponse()->setHttpResponseCode(403);
                $this->encoder->encode(["success" => false, "msg" => "not allowed, permission delete is needed"]);
            }
        } elseif ($category == "update") {
            if (!$element->isAllowed("publish")) {
                $this->getResponse()->setHttpResponseCode(403);
                $this->encoder->encode(["success" => false, "msg" => "not allowed, permission save is needed"]);
            }
        } elseif ($category == "create") {
            if (!$element->isAllowed("create")) {
                $this->getResponse()->setHttpResponseCode(403);
                $this->encoder->encode(["success" => false, "msg" => "not allowed, permission create is needed"]);
            }
        }
    }

    /** end point for objects related data.
     * - create objects
     *      POST http://[YOUR-DOMAIN]/plugin/PimCoreExtendedRestApi/rest/objects?apikey=[API-KEY]
     *      body: json-encoded objects array in the same format as returned by get object by id
     *              but with missing id field or id set to 0
     *      returns json encoded objects ids
     * - update objects
     *      POST http://[YOUR-DOMAIN]/plugin/PimCoreExtendedRestApi/rest/objects?apikey=[API-KEY]
     *      body: same as for create objects but with objects ids
     *      returns json encoded success value
     * @throws \Exception
     */
    public function objectsAction()
    {
        try {
            if ($this->isPost()) {
                $objects = file_get_contents("php://input");
                if (!$objects) {
                    $result = ["success" => false, "msg" => "Json encoded object array not found"];
                    $this->encoder->encode($result);

                    return;
                }

                $objects = \Zend_Json::decode($objects);
                $ids = [];
                foreach ($objects as $data) {
                    try {
                        $type = $data["type"];

                        if ($data["id"]) {

                            // Update existing object

                            $success = false;
                            $obj = Object::getById($data["id"]);
                            if ($obj) {
                                $this->checkPermission($obj, "update");
                            }

                            if ($type == "folder") {
                                $wsData  = self::fillWebserviceData(
                                    "\\Pimcore\\Model\\Webservice\\Data\\Object\\Folder\\In",
                                    $data
                                );
                                $success = $this->service->updateObjectFolder($wsData);
                            } else {
                                $wsData  = self::fillWebserviceData(
                                    "\\Pimcore\\Model\\Webservice\\Data\\Object\\Concrete\\In",
                                    $data
                                );
                                $success = $this->service->updateObjectConcrete($wsData);
                            }
                            array_push($ids, ['key' => $data['key'], 'id' => $data['id'], 'success' => $success]);

                        } else {

                            //Create new object

                            if ($type == "folder") {
                                $class  = "\\Pimcore\\Model\\Webservice\\Data\\Object\\Folder\\In";
                                $method = "createObjectFolder";
                            } else {
                                $class  = "\\Pimcore\\Model\\Webservice\\Data\\Object\\Concrete\\In";
                                $method = "createObjectConcrete";
                            }
                            $wsData = self::fillWebserviceData($class, $data);

                            $obj = new Object();
                            $obj->setId($wsData->parentId);
                            $this->checkPermission($obj, "create");

                            array_push(
                                $ids,
                                ['key' => $data['key'], 'id' => $this->service->$method($wsData), 'success' => true]
                            );
                        }
                    } catch (\Exception $e) {
                        Logger::error($e);
                        array_push(
                            $ids,
                            [
                                'key'     => $data['key'],
                                'id'      => $data['id'] ? $data['id'] : null,
                                'msg'     => (string)$e,
                                'success' => false,
                            ]
                        );
                    }
                }
            } else {
                $msg = "Method not allowed";
            }

            if (count($ids)) {
                $result = ["success" => true, "data" => $ids];
            } else {
                $result = ["success" => false, "msg" => $msg ? $msg : 'Objects not found'];
            }
            $this->encoder->encode($result);

            return;
        } catch (\Exception $e) {
            Logger::error($e);
            $this->encoder->encode(["success" => false, "msg" => (string)$e]);
        }

        throw new \Exception("not implemented");
    }

    /** Returns a list of object related data matching the given criteria.
     *  Example:
     *  GET http://[YOUR-DOMAIN]/plugin/PimCoreExtendedRestApi/rest/object-list?apikey=[API-KEY]&order=DESC&offset=3&orderKey=id&limit=2&condition=type%3D%27folder%27&elements=name,price
     *
     * Parameters:
     *      - condition
     *      - sort order (if supplied then also the key must be provided)
     *      - sort order key
     *      - offset
     *      - limit
     *      - group by key
     *      - objectClass the name of the object class (without "Object_"). If the class does
     *          not exist the filter criteria will be ignored!
     *      - elements
     */
    public function objectListAction()
    {
        $this->checkUserPermission("objects");

        $condition = urldecode($this->getParam("condition"));
        $order = $this->getParam("order");
        $orderKey = $this->getParam("orderKey");
        $offset = $this->getParam("offset");
        $limit = $this->getParam("limit");
        $groupBy = $this->getParam("groupBy");
        $objectClass = $this->getParam("objectClass");
        $elements = $this->getParam("elements");
        $elementsNames = explode(',', $elements);
        $result = $this->service->getObjectList($condition, $order, $orderKey, $offset, $limit, $groupBy, $objectClass);

        $objects = [];
        foreach ($result as $value) {
            $object = Object::getById($value->id);
            if ($object) {
                if ($object instanceof Object\Folder) {
                    $object = $this->service->getObjectFolderById($value->id);
                } else {
                    $object = $this->service->getObjectConcreteById($value->id);

                    if ($object && $elements) {
                        $allowedElements = [];
                        foreach ($object->elements as $element) {
                            if (in_array($element->name, $elementsNames)) {
                                $allowedElements[] = $element;
                            }
                        }
                        $object->elements = $allowedElements;
                    }
                }
                $item = ["id" => $value->id, "success" => true, "data" => $object];
            } else {
                $item = [
                    "id"      => $value->id,
                    "success" => false,
                    "msg"     => "Object does not exist",
                    "code"    => self::ELEMENT_DOES_NOT_EXIST,
                ];
            }
            $objects[] = $item;
        }

        $this->encoder->encode(["success" => true, "data" => $objects]);
    }

    /** end point for create/update asset related data.
     * - create asset
     *      PUT or POST http://[YOUR-DOMAIN]/plugin/PimCoreExtendedRestApi/rest/asset?apikey=[API-KEY]
     *      body: json-encoded asset data in the same format as returned by get asset by id
     *              but with missing id field or id set to 0
     *      returns json encoded asset id
     * - update asset
     *      PUT or POST http://[YOUR-DOMAIN]/plugin/PimCoreExtendedRestApi/rest/asset?apikey=[API-KEY]
     *      body: same as for create asset but with asset id and saveOldMetadata flag if you want to keep old metadata
     *      returns json encoded success value
     * @throws \Exception
     */
    public function assetAction()
    {
        try {
            if ($this->isPost() || $this->isPut()) {
                $data = file_get_contents("php://input");
                $data = \Zend_Json::decode($data);

                // Download image by url, encode it and save to data param
                if (isset($data["url"])) {
                    list($filepath, $status) = $this->downloadImageWithWget($data["url"]);
                    if ($status != 0) {
                        $this->encoder->encode(["success" => false, "msg" => "Unable to download image by url ".$data["url"]]);

                        return;
                    }
                    $image = base64_encode(file_get_contents($filepath));
                    $data["data"] = $image;
                }

                $type = $data["type"];
                $id = null;

                if ($data["id"]) {
                    $id = $data["id"];
                    $asset = Asset::getById($id);

                    if ($asset) {
                        $this->checkPermission($asset, "update");
                    }

                    // Keep old metadata on update
                    $isSaveMetadata = isset($data["saveOldMetadata"]) ? $data["saveOldMetadata"] : false;
                    if ($isSaveMetadata) {
                        $newMetadata = isset($data["metadata"]) ? $data["metadata"] : [];
                        $oldMetadata = $asset->getMetadata();

                        // Remove duplicates and merge old and new data
                        if (count($newMetadata)) {
                            $metadataWithKeys = [];
                            foreach ($oldMetadata as $old) {
                                $metadataWithKeys[$old['name']] = $old;
                            }
                            foreach ($newMetadata as $new) {
                                $metadataWithKeys[$new['name']] = $new;
                            }
                            $oldMetadata = array_values($metadataWithKeys);
                        }

                        $data['metadata'] = $oldMetadata;
                    }

                    if ($type == "folder") {
                        $wsData = self::fillWebserviceData("\\Pimcore\\Model\\Webservice\\Data\\Asset\\Folder\\In", $data);
                        $success = $this->service->updateAssetFolder($wsData);
                    } else {
                        $wsData = self::fillWebserviceData("\\Pimcore\\Model\\Webservice\\Data\\Asset\\File\\In", $data);
                        $success = $this->service->updateAssetFile($wsData);
                    }
                } else {
                    if ($type == "folder") {
                        $class = "\\Pimcore\\Model\\Webservice\\Data\\Asset\\Folder\\In";
                        $method = "createAssetFolder";
                    } else {
                        $class = "\\Pimcore\\Model\\Webservice\\Data\\Asset\\File\\In";
                        $method = "createAssetFile";
                    }

                    $wsData = self::fillWebserviceData($class, $data);

                    $asset = new Asset();
                    $asset->setId($wsData->parentId);
                    $this->checkPermission($asset, "create");

                    $id = $this->service->$method($wsData);
                    $success = true;
                }

                $this->encoder->encode(["success" => $success, "data" => ["id" => $id]]);

                return;
            }
        } catch (\Exception $e) {
            Logger::error($e);
            $this->encoder->encode(["success" => false, "msg" => (string) $e]);
        }
        $this->encoder->encode(["success" => false]);
    }

    // Fast download image by url with Wget
    private function downloadImageWithWget($url)
    {
        $url = trim($url);
        if (empty($url)) {
            return ['', -1];
        }

        $filename = explode('/', $url);
        $filename = $filename[count($filename) - 1];

        $dir = PIMCORE_TEMPORARY_DIRECTORY;
        $img = $dir.DIRECTORY_SEPARATOR.$filename;

        if ( ! file_exists($img)) {
            system("wget --tries=5 $url -P $dir", $status);
        }

        return [$img, $status];
    }

    /**
     * @param $permission
     *
     * @throws Zend_Controller_Response_Exception
     */
    private function checkUserPermission($permission)
    {
        if ($user = Tool\Admin::getCurrentUser()) {
            if ($user->isAllowed($permission)) {
                return;
            }
        }
        $this->getResponse()->setHttpResponseCode(403);
        $this->encoder->encode(["success" => false, "msg" => "not allowed"]);
    }

    /**
     * @param $wsData
     * @param $data
     * @return Webservice\Data\Asset
     */
    private static function map($wsData, $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $tmp = [];

                foreach ($value as $subkey => $subvalue) {
                    if (is_array($subvalue)) {
                        $object = new stdClass();
                        $object = self::map($object, $subvalue);
                        ;
                        $tmp[$subkey] = $object;
                    } else {
                        $tmp[$subkey] = $subvalue;
                    }
                }
                $value = $tmp;
            }
            $wsData->$key = $value;
        }

        if ($wsData instanceof Pimcore\Model\Webservice\Data\Object) {
            /** @var Pimcore\Model\Webservice\Data\Object key */
            $wsData->key = Element\Service::getValidKey($wsData->key, "object");
        } elseif ($wsData instanceof Pimcore\Model\Webservice\Data\Document) {
            /** @var Pimcore\Model\Webservice\Data\Document key */
            $wsData->key = Element\Service::getValidKey($wsData->key, "document");
        } elseif ($wsData instanceof Pimcore\Model\Webservice\Data\Asset) {
            /** @var Pimcore\Model\Webservice\Data\Asset $wsData */
            $wsData->filename = Element\Service::getValidKey($wsData->filename, "asset");
        }

        return $wsData;
    }

    /**
     * @param $class
     * @param $data
     * @return Webservice\Data\Asset
     */
    public static function fillWebserviceData($class, $data)
    {
        $wsData = new $class();

        return self::map($wsData, $data);
    }

    /** Returns true if this is a DELETE request. Can be overridden by providing a
     * a method=delete parameter.
     * @return bool
     */
    public function isDelete()
    {
        $request = $this->getRequest();
        $overrideMethod = $request->getParam("method");
        if (strtoupper($overrideMethod) == "DELETE") {
            return true;
        }

        return $request->isDelete();
    }

    /** Returns true if this is a GET request. Can be overridden by providing a
     * a method=get parameter.
     * @return bool
     */
    public function isGet()
    {
        $request = $this->getRequest();
        $overrideMethod = $request->getParam("method");
        if (strtoupper($overrideMethod) == "GET") {
            return true;
        }

        return $request->isGet();
    }

    /** Returns true if this is a POST request. Can be overridden by providing a
     * a method=post parameter.
     * @return bool
     */
    public function isPost()
    {
        $request = $this->getRequest();
        $overrideMethod = $request->getParam("method");
        if (strtoupper($overrideMethod) == "POST") {
            return true;
        }

        return $request->isPost();
    }

    /** Returns true if this is a PUT request. Can be overridden by providing a
     * a method=put parameter.
     * @return bool
     */
    public function isPut()
    {
        $request = $this->getRequest();
        $overrideMethod = $request->getParam("method");
        if (strtoupper($overrideMethod) == "PUT") {
            return true;
        }

        return $request->isPut();
    }

    /**
     * @return mixed
     */
    protected function getQueryParams()
    {
        return $this->getRequest()->getQuery();
    }

}
