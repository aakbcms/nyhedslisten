<?php

namespace App\Service\Heyloyalty;

use App\Exception\HeyloyaltyException;
use App\Exception\HeyloyaltyOptionNotFoundException;
use Phpclient\HLClient;
use Phpclient\HLLists;
use Phpclient\V2\HLLists as HLListsV2;

/**
 * Class AuthenticationService.
 */
class HeyloyaltyService
{
    public function __construct(
        private readonly HLClient $client,
        private readonly string $listId,
        private readonly string $fieldId,
    ) {}

    /**
     * Remove option from list field.
     *
     * @throws HeyloyaltyException
     */
    public function removeOption(): never
    {
        throw new HeyloyaltyException('Not supported yet');
    }

    /**
     * Update option/value to the list.
     *
     * @param string $oldOption
     *   Option to update
     * @param string $newOption
     *   New option
     *
     * @throws HeyloyaltyOptionNotFoundException|HeyloyaltyException
     */
    public function updateOption(string $oldOption, string $newOption): void
    {
        $list = $this->getList($this->listId);

        $field = $this->getListField($this->listId, $this->fieldId);
        $id = array_search($oldOption, $list['fields'][$field['name']]['options']);

        if (false !== $id) {
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

            $this->updateListField($this->listId, $params);
        } else {
            throw new HeyloyaltyOptionNotFoundException('Option not found');
        }
    }

    /**
     * Add option/value to the list.
     *
     * @param string $option
     *   Option to add
     *
     * @throws HeyloyaltyException
     */
    public function addOption(string $option): void
    {
        $list = $this->getList($this->listId);

        $field = $this->getListField($this->listId, $this->fieldId);
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

        $this->updateListField($this->listId, $params);
    }

    /**
     * Updated list.
     *
     * @param int $listId
     *   List ID
     * @param $params
     *   Stuff to patch
     *
     * @throws HeyloyaltyException
     */
    private function updateListField(int $listId, $params): void
    {
        $listsService = new HLListsV2($this->client);
        $res = $listsService->patch($listId, $params);

        // We decode res to get exception on errors in responses.
        $this->decodeResponse($res['response'], true);
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
     * @throws HeyloyaltyException
     */
    private function getListField(int $listId, int $fieldId): mixed
    {
        $list = $this->getList($listId);
        $field = array_filter($list['fields'], fn ($val, $id) => $val['id'] == $fieldId, ARRAY_FILTER_USE_BOTH);

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
     * @throws HeyloyaltyException
     *   If error is return from Heyloyalty
     */
    private function getList(int $listId): mixed
    {
        $listsService = new HLLists($this->client);
        $response = $listsService->getList($listId);
        if (array_key_exists('response', $response)) {
            $response = $this->decodeResponse($response['response'], true);
        } else {
            throw new HeyloyaltyException('Unknown response from HlLists->getList call');
        }

        return $response ?? null;
    }

    /**
     * Decode json string from Heyloyalty.
     *
     * @param $string
     *   JSON encoded string
     *
     * @return mixed
     *   Decoded result
     *
     * @throws HeyloyaltyException
     *   If error is return from Heyloyalty
     */
    private function decodeResponse($string): mixed
    {
        try {
            $json = json_decode((string) $string, true, 512, JSON_THROW_ON_ERROR);

            if (array_key_exists('error', $json)) {
                $error = $json['error'];

                if (is_array($error)) {
                    $error = $error['original'];
                }

                throw new HeyloyaltyException($error);
            }

            if (array_key_exists('code', $json) && $json['code'] >= 400) {
                $message = $json['message'] ?? 'Unknown error from Heyloyalty api';
                throw new HeyloyaltyException($message, $json['code']);
            }

            return $json;
        } catch (\JsonException $e) {
            throw new HeyloyaltyException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
