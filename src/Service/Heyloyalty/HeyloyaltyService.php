<?php

namespace App\Service\Heyloyalty;

use Phpclient\HLClient;
use Phpclient\HLLists;
use Phpclient\V2\HLLists as HLListsV2;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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

    /**
     * Remove option from list field.
     */
    public function removeOption()
    {
        throw new \Exception('Not supported yet');
    }

    /**
     * Update option/value to the list.
     *
     * @param string $oldOption
     *   Option to update
     * @param string $newOption
     *   New option
     *
     * @throws \Exception
     */
    public function updateOption(string $oldOption, string $newOption)
    {
        $listId = $this->params->get('heyloyalty.list.id');
        $list = $this->getList($listId);

        $field = $this->getListField($listId, $this->params->get('heyloyalty.field.id'));
        $id = array_search($oldOption, $list['fields'][$field['name']]['options']);

        if ($id == !false) {
            $list['fields'][$field['name']]['options'] = [
                [
                    'id' => $id,
                    'label' => $newOption,
                ],
            ];

            $params = [
                'id' => $list['id'],
                'name' => $list['name'],
                'country_id' => $list['country_id'],
                'duplicates' => $list['duplicates'],
                'fields' => [$field['name'] => $list['fields'][$field['name']]],
            ];

            $this->updateListField($this->params->get('heyloyalty.list.id'), $params);
        } else {
            throw new \Exception('Option not found');
        }
    }

    /**
     * Add option/value to the list.
     *
     * @param string $option
     *   Option to add
     *
     * @throws \Exception
     */
    public function addOption(string $option)
    {
        $listId = $this->params->get('heyloyalty.list.id');
        $list = $this->getList($listId);

        $field = $this->getListField($listId, $this->params->get('heyloyalty.field.id'));
        $list['fields'][$field['name']]['options'] = [
            [
                'label' => $option,
            ],
        ];

        $params = [
            'id' => $list['id'],
            'name' => $list['name'],
            'country_id' => $list['country_id'],
            'duplicates' => $list['duplicates'],
            'fields' => [$field['name'] => $list['fields'][$field['name']]],
        ];

        $this->updateListField($this->params->get('heyloyalty.list.id'), $params);
    }

    /**
     * Updated list.
     *
     * @param int $listId
     *   List ID
     * @param $params
     *  Stuff to patch
     *
     * @throws \Exception
     */
    private function updateListField(int $listId, $params)
    {
        $client = $this->getClient();
        $listsService = new HLListsV2($client);
        $res = $listsService->patch($listId, $params);

        // We decode res to get exception on errors in responses.
        $this->jsonDecode($res['response'], true);
    }

    /**
     * Get list field.
     *
     * @param int $listId
     *   List ID
     * @param int $fieldId
     *   Field ID
     *
     * @return mixed|null
     *
     * @throws \Exception
     */
    private function getListField(int $listId, int $fieldId)
    {
        $list = $this->getList($listId);
        $field = array_filter($list['fields'], function ($val, $id) use ($fieldId) {
            return $val['id'] == $fieldId;
        }, ARRAY_FILTER_USE_BOTH);

        return is_array($field) ? reset($field) : null;
    }

    /**
     * Get list.
     *
     * @param $listId
     *   ID of the list to get
     *
     * @return mixed|null
     *   The list object
     *
     * @throws \Exception
     *   If error is return from Heyloyalty
     */
    private function getList(int $listId)
    {
        $client = $this->getClient();
        $listsService = new HLLists($client);
        $response = $listsService->getList($listId);
        if (array_key_exists('response', $response)) {
            $list = $this->jsonDecode($response['response'], true);
        }

        return $list ?? null;
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
     *   Decoded result
     *
     * @throws \Exception
     *   If error is return from Heyloyalty
     */
    private function jsonDecode($string, $assoc = false)
    {
        $json = json_decode($string, $assoc);
        if (array_key_exists('error', $json)) {
            if ($assoc) {
                $error = $json['error'];
            } else {
                $error = $json->error;
            }

            if (is_array($error)) {
                $error = $error['original'];
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
    private function getClient()
    {
        if (is_null($this->client)) {
            $this->client = new HLClient($this->params->get('heyloyalty.apikey'), $this->params->get('heyloyalty.secret'));
        }

        return $this->client;
    }
}
