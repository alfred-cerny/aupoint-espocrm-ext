<?php

namespace Espo\Modules\EnhancedFields\Repositories;

use Espo\Core\Name\Field;
use Espo\Core\Templates\Repositories\Base as BaseRepository;
use Espo\Modules\EnhancedFields\Entities\AccountAddress as AccountAddressEntity;
use Espo\ORM\Entity;
use stdClass;

/**
 * Repository for AccountAddress entity.
 * Handles address creation, lookup, and management.
 *
 * @extends BaseRepository<AccountAddressEntity>
 */
class AccountAddress extends BaseRepository {

	/**
	 * @return array<int, stdClass>
	 */
	public function getAccountAddressData(Entity $entity): array {
		if (!$entity->hasId()) {
			return [];
		}

		$dataList = [];

		$addressList = $this
			->select([Field::ID, Field::NAME, ...AccountAddressEntity::FIELDS, ['en.primary', 'primary']])
			->join(
				AccountAddressEntity::RELATION_ENTITY_ACCOUNT_ADDRESS,
				'en',
				[
					'en.accountAddressId:' => 'id',
				]
			)
			->where([
				'en.entityId' => $entity->getId(),
				'en.entityType' => $entity->getEntityType(),
				'en.deleted' => false,
			])
			->order('en.primary', true)
			->find();

		foreach ($addressList as $address) {
			$item = (object)[
				//'accountAddress' => $address->get(Field::NAME),
				'accountAddressId' => $address->get(Field::ID),
				'accountAddressName' => $address->get(Field::NAME),
				'primary' => $address->get('primary'),
			];

			foreach (AccountAddressEntity::FIELDS as $fieldName) {
				$fieldValue = $address->get($fieldName);
				$item->$fieldName = empty($fieldValue) ? null : $fieldValue;
			}

			$dataList[] = $item;
		}

		return $dataList;
	}


	/**
	 * Create or find an existing contact address based on normalized data.
	 *
	 * @param array $data Address data containing street, city, state, country, postalCode
	 * @return AccountAddressEntity
	 */
	public function getByData(array $data): AccountAddressEntity {
		$normalizedData = $this->normalizeAddressData($data);
		$name = $this->formatAddressName($normalizedData);

		$address = $this->where(['name' => $name])->findOne();

		if ($address) {
			return $address;
		}
		/** @var $address AccountAddressEntity */
		$address = $this->entityManager->createEntity(AccountAddressEntity::ENTITY_TYPE, [
			'name' => $name,
			'street' => $normalizedData['street'] ?? null,
			'city' => $normalizedData['city'] ?? null,
			'state' => $normalizedData['state'] ?? null,
			'country' => $normalizedData['country'] ?? null,
			'postalCode' => $normalizedData['postalCode'] ?? null,
		]);

		return $address;
	}

	public function getByName(string $name): ?AccountAddressEntity {
		/** @var ?AccountAddressEntity */
		return $this->where(['name' => $name])->findOne();
	}

	public function getById(string $id): ?AccountAddressEntity {
		/** @var ?AccountAddressEntity */
		return $this->where(['id' => $id])->findOne();
	}

	/**
	 * Normalize address data by trimming whitespace and handling empty values.
	 *
	 * @param array $data Raw address data
	 * @return array Normalized address data
	 */
	private function normalizeAddressData(array $data): array {
		$normalized = [];

		$fields = ['street', 'city', 'state', 'country', 'postalCode'];

		foreach ($fields as $field) {
			if (!empty($data[$field])) {
				$normalized[$field] = trim($data[$field]);
			}
		}

		return $normalized;
	}

	/**
	 * Format address components into a display name.
	 *
	 * @param array $data Normalized address data
	 * @return string Formatted address string
	 */
	private function formatAddressName(array $data): string {
		$parts = [];

		if (!empty($data['street'])) {
			$parts[] = $data['street'];
		}

		$cityStateParts = [];
		if (!empty($data['city'])) {
			$cityStateParts[] = $data['city'];
		}
		if (!empty($data['state'])) {
			$cityStateParts[] = $data['state'];
		}
		if (!empty($data['postalCode'])) {
			$cityStateParts[] = $data['postalCode'];
		}

		if (!empty($cityStateParts)) {
			$parts[] = implode(', ', $cityStateParts);
		}

		if (!empty($data['country'])) {
			$parts[] = $data['country'];
		}

		return implode(', ', $parts);
	}

	protected function beforeSave(\Espo\ORM\Entity $entity, array $options = []): void {
		parent::beforeSave($entity, $options);

		if ($entity->isNew() || $entity->isAttributeChanged('street') ||
			$entity->isAttributeChanged('city') || $entity->isAttributeChanged('state') ||
			$entity->isAttributeChanged('country') || $entity->isAttributeChanged('postalCode')) {

			$data = [
				'street' => $entity->get('street'),
				'city' => $entity->get('city'),
				'state' => $entity->get('state'),
				'country' => $entity->get('country'),
				'postalCode' => $entity->get('postalCode'),
			];

			$name = $this->formatAddressName($this->normalizeAddressData($data));
			$entity->set('name', $name);
		}
		$hash = AccountAddressEntity::generateAddressKey($entity->getValueMap());
		$entity->set('hash', $hash);
		$GLOBALS['log']->debug('Saving address hash: ' . $hash);
	}

	public function markAddressInvalid(string $name, bool $isInvalid = true): void {
		$accountAddress = $this->getByName($name);

		if (!$accountAddress) {
			return;
		}

		$accountAddress->set('invalid', $isInvalid);

		$this->save($accountAddress);
	}

}
