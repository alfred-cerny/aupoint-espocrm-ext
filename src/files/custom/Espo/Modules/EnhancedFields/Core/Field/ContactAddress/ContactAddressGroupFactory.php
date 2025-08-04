<?php

namespace Espo\Modules\EnhancedFields\Core\Field\ContactAddress;

use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Value\ValueFactory;
use Espo\Core\Utils\Metadata;

/**
 * Factory for creating ContactAddressGroup value objects from entity data.
 * Handles loading related ContactAddress entities and converting to value objects.
 */
readonly class ContactAddressGroupFactory implements ValueFactory {

	public function __construct(
		protected EntityManager $entityManager,
		protected Metadata      $metadata) {}

	public function isCreatableFromEntity(Entity $entity, string $field): bool {
		$type = $this->metadata->get(['entityDefs', $entity->getEntityType(), 'fields', $field, 'type']);

		return $type === 'contactAddress';
	}

	public function createFromEntity(Entity $entity, string $field): ContactAddressGroup {
		if (!$this->isCreatableFromEntity($entity, $field)) {
			throw new \RuntimeException("Can't create from entity.");
		}

		$contactAddressData = $entity->get($field . 'Data');

		if (empty($contactAddressData)) {
			return new ContactAddressGroup([]);
		}

		$addressList = [];

		foreach ($contactAddressData as $item) {
			$addressId = $item->contactAddressId ?? null;

			if (!$addressId) {
				continue;
			}

			$name = $item->contactAddressName ?? '';
			$street = $item->street ?? null;
			$city = $item->city ?? null;
			$state = $item->state ?? null;
			$country = $item->country ?? null;
			$postalCode = $item->postalCode ?? null;
			$primary = $item->primary ?? false;

			$addressList[] = new ContactAddress(
				$addressId,
				$name,
				$street,
				$city,
				$state,
				$country,
				$postalCode,
				$primary
			);
		}

		return new ContactAddressGroup($addressList);
	}

}