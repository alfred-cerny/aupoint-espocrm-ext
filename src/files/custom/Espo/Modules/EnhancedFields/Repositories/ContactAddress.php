<?php

namespace Espo\Modules\EnhancedFields\Repositories;

use Espo\Core\Name\Field;
use Espo\Core\Templates\Repositories\Base as BaseRepository;
use Espo\Modules\EnhancedFields\Entities\ContactAddress as ContactAddressEntity;
use Espo\ORM\Entity;
use stdClass;

/**
 * Repository for ContactAddress entity.
 * Handles address creation, lookup, and management.
 *
 * @extends BaseRepository<ContactAddressEntity>
 */
class ContactAddress extends BaseRepository {

	/**
	 * @return array<int, stdClass>
	 */
	public function getContactAddressData(Entity $entity): array {
		if (!$entity->hasId()) {
			return [];
		}

		$dataList = [];

		$addressList = $this
			->select([Field::ID, Field::NAME, ...ContactAddressEntity::FIELDS, ['en.primary', 'primary']])
			->join(
				ContactAddressEntity::RELATION_ENTITY_CONTACT_ADDRESS,
				'en',
				[
					'en.contactAddressId:' => 'id',
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
				//'contactAddress' => $address->get(Field::NAME),
				'contactAddressId' => $address->get(Field::ID),
				'contactAddressName' => $address->get(Field::NAME),
				'primary' => $address->get('primary'),
			];

			foreach (ContactAddressEntity::FIELDS as $fieldName) {
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
	 * @return ContactAddressEntity
	 */
	public function getByData(array $data): ContactAddressEntity {
		$normalizedData = $this->normalizeAddressData($data);
		$name = $this->formatAddressName($normalizedData);

		$address = $this->where(['name' => $name])->findOne();

		if ($address) {
			return $address;
		}
		/** @var $address ContactAddressEntity */
		$address = $this->entityManager->createEntity(ContactAddressEntity::ENTITY_TYPE, [
			'name' => $name,
			'street' => $normalizedData['street'] ?? null,
			'city' => $normalizedData['city'] ?? null,
			'state' => $normalizedData['state'] ?? null,
			'country' => $normalizedData['country'] ?? null,
			'postalCode' => $normalizedData['postalCode'] ?? null,
		]);

		return $address;
	}

	public function getByName(string $name): ?ContactAddressEntity {
		/** @var ?ContactAddressEntity */
		return $this->where(['name' => $name])->findOne();
	}

	public function getById(string $id): ?ContactAddressEntity {
		/** @var ?ContactAddressEntity */
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
		$hash = ContactAddressEntity::generateAddressKey($entity->getValueMap());
		$entity->set('hash', $hash);
		$GLOBALS['log']->debug('Saving address hash: ' . $hash);
	}

	public function markAddressInvalid(string $name, bool $isInvalid = true): void {
		$contactAddress = $this->getByName($name);

		if (!$contactAddress) {
			return;
		}

		$contactAddress->set('invalid', $isInvalid);

		$this->save($contactAddress);
	}

}
