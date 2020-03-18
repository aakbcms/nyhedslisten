<?php

namespace App\Service\Heyloyalty;

use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Phpclient\HLClient;
use Phpclient\HLLists;

/**
 * Class AuthenticationService.
 */
class HeyloyaltyService
{
    private $params;
    private $cache;
    private $statsLogger;

    private $client;

    public function __construct(ParameterBagInterface $params, AdapterInterface $cache, LoggerInterface $statsLogger)
    {
        $this->params = $params;
        $this->cache = $cache;
        $this->statsLogger = $statsLogger;
    }

    public function removeOption() {

    }

    public function addOption(string $option) {
        $listId = $this->params->get('heyloyalty.list.id');
        $list = $this->getList($listId);

        $field = $this->getListField($listId, $this->params->get('heyloyalty.field.id'));
        $list['fields'][$field['name']]['options'][] = $option;

        $params = [
            'id' => $list['id'],
            'name' => $list['name'],
            'country_id' => $list['country_id'],
            'duplicates' => $list['duplicates'],
            'fields' => $list['fields'],
        ];

        $this->updateListField($this->params->get('heyloyalty.list.id'), $params);
    }

    private function updateListField(int $listId, $params) {
        $client = $this->getClient();
        $listsService = new HLLists($client);
        $response = $listsService->update($listId, $params);
        print_r($response);
    }

    /**
     * @param int $listId
     * @param int $fieldId
     * @return mixed|null
     * @throws \Exception
     */
    private function getListField(int $listId, int $fieldId) {
        $list = $this->getList($listId);
        $field = array_filter($list['fields'], function ($val, $id) use ($fieldId) {
            return $val['id'] == $fieldId;
        }, ARRAY_FILTER_USE_BOTH);

        return is_array($field) ? reset($field) : NULL;
    }

    /**
     * Get list.
     *
     * @param $listId
     *   ID of the list to get.
     *
     * @return mixed|null
     *   The list object.
     *
     * @throws \Exception
     *   If error is return from Heyloyalty.
     */
    private function getList(int $listId) {
        $client = $this->getClient();
        $listsService = new HLLists($client);
        $response = $listsService->getList($listId);
        if (array_key_exists('response', $response)) {
            $list = $this->jsonDecode($response['response'], TRUE);
        }

        return $list ?? NULL;
    }

    /**
     * Decode json string from Heyloyalty.
     *
     * @param $string
     *   JSON encoded string
     * @param bool $assoc
     *   IF TRUE, returned objects will be converted into associative arrays.
     *   Default FALSE.
     *
     * @return mixed
     *   Decoded result.
     *
     * @throws \Exception
     *   If error is return from Heyloyalty.
     */
    private function jsonDecode($string, $assoc = FALSE) {
        $json = json_decode($string, $assoc);
        if (array_key_exists('error', $json)) {
            if ($assoc) {
                $error = $json['error'];
            }
            else {
                $error = $json->error;
            }
            throw new \Exception($error);
        }
        return $json;
    }

    /**
     * Get client to communicate with Heyloyalty.
     *
     * @return \Phpclient\HLClient|null
     */
    private function getClient() {
        if (is_null($this->client)) {
            $this->client = new HLClient($this->params->get('heyloyalty.apikey'), $this->params->get('heyloyalty.secret'));
        }

        return $this->client;
    }
}