<?php

namespace App\Service\Heyloyalty;

use App\Exception\HeyloyaltyException;
use App\Exception\HeyloyaltyNotSupportedException;
use App\Exception\HeyloyaltyOptionNotFoundException;
use Phpclient\HLClient;
use Phpclient\HLLists;
use Phpclient\V2\HLLists as HLListsV2;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Class AuthenticationService.
 */
class HeyloyaltyService
{
    private AsciiSlugger $slugger;
    private mixed $heyloyaltyList = null;

    public function __construct(
        private readonly HLClient $client,
        private readonly string $listId,
        private readonly string $fieldId,
    ) {
        $this->slugger = new AsciiSlugger();
    }

    /**
     * Remove option from list field.
     *
     * @throws HeyloyaltyException
     */
    public function removeOption(): never
    {
        throw new HeyloyaltyNotSupportedException('Not supported yet');
    }

    /**
     * Check if the list has the given option.
     *
     * @param string $option
     *
     * @return bool|null
     *
     * @throws HeyloyaltyException
     */
    public function hasOption(string $option): ?bool
    {
        $list = $this->getList($this->listId);
        $field = $this->getListField($this->listId, $this->fieldId);

        return in_array($option, $list['fields'][$field['name']]['options']);
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

            $res = $this->updateListField($this->listId, $params);
            $this->validateResponse($res, $newOption);

            // Invalidate cached list after update
            $this->heyloyaltyList = null;
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
        // Heyloyalty's api will sometimes fail when ADDING labels
        // containing spaces. However, we can UPDATE a label without
        // spaces to a label with spaces... 🤯

        // Slug (remove spaces) from option to add.
        $sluggedOption = $this->slugger->slug($option)->toString();

        $list = $this->getList($this->listId);

        $field = $this->getListField($this->listId, $this->fieldId);
        $list['fields'][$field['name']]['options'] = [
            [
                'id' => null,
                'label' => $sluggedOption,
            ],
        ];

        $params = [
            'id' => $list['id'],
            'name' => $list['name'],
            'country_id' => $list['country_id'],
            'duplicates' => $list['duplicates'],
            'fields' => [$field['name'] => $list['fields'][$field['name']]],
        ];

        // Add slugged option (without spaces)
        $res = $this->updateListField($this->listId, $params);
        $this->validateResponse($res, $sluggedOption);

        // Invalidate cached list after update
        $this->heyloyaltyList = null;

        // Update option to non-slugged version
        if ($option !== $sluggedOption) {
            $this->updateOption($sluggedOption, $option);
        }
    }

    /**
     * Updated list.
     *
     * @param int $listId
     *   List ID
     * @param $params
     *   Stuff to patch
     *
     * @return mixed
     *
     * @throws HeyloyaltyException
     */
    private function updateListField(int $listId, $params): mixed
    {
        $listsService = new HLListsV2($this->client);
        $res = $listsService->patch($listId, $params);

        return $this->decodeResponse($res['response'], true);
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
        if ($this->heyloyaltyList) {
            return $this->heyloyaltyList;
        }

        $listsService = new HLLists($this->client);
        $response = $listsService->getList($listId);
        if (array_key_exists('response', $response)) {
            $response = $this->decodeResponse($response['response'], true);
        } else {
            throw new HeyloyaltyException('Unknown response from HlLists->getList call');
        }

        $this->heyloyaltyList = $response ?? null;

        return $this->heyloyaltyList;
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

    /**
     * @param array $list
     * @param string $option
     *
     * @return void
     *
     * @throws HeyloyaltyException
     */
    private function validateResponse(array $list, string $option): void
    {
        $field = $this->getListField($this->listId, $this->fieldId);

        $responseField = array_filter($list['fields'], function ($f) use ($field) {
            return $f['name'] === $field['name'];
        });
        $responseField = array_pop($responseField);

        $result = array_filter($responseField['options'], function ($o) use ($option) {
            return $o['value'] === $option;
        });

        if (empty($result)) {
            throw new HeyloyaltyException(sprintf('Error: Category "%s" not added in Heyloyalty', $option));
        }
    }
}
