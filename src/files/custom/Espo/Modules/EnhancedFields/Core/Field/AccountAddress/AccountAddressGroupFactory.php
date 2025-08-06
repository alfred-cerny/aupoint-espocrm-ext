<?php

namespace Espo\Modules\EnhancedFields\Core\Field\AccountAddress;

use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Value\ValueFactory;
use Espo\Core\Utils\Metadata;

/**
 * Factory for creating AccountAddressGroup value objects from entity data.
 * Handles loading related AccountAddress entities and converting to value objects.
 */
readonly class AccountAddressGroupFactory implements ValueFactory {

	public function __construct(
		protected EntityManager $entityManager,
		protected Metadata      $metadata) {}

	public function isCreatableFromEntity(Entity $entity, string $field): bool {
		$type = $this->metadata->get(['entityDefs', $entity->getEntityType(), 'fields', $field, 'type']);

		return $type === 'accountAddress';
	}

	public function createFromEntity(Entity $entity, string $field): AccountAddressGroup {
		if (!$this->isCreatableFromEntity($entity, $field)) {
			throw new \RuntimeException("Can't create from entity.");
		}

		$accountAddressData = $entity->get($field . 'Data');

		if (empty($accountAddressData)) {
			return new AccountAddressGroup([]);
		}

		$addressList = [];

		foreach ($accountAddressData as $item) {
			$addressId = $item->accountAddressId ?? null;

			if (!$addressId) {
				continue;
			}

			$name = $item->accountAddressName ?? '';
			$street = $item->street ?? null;
			$city = $item->city ?? null;
			$state = $item->state ?? null;
			$country = $item->country ?? null;
			$postalCode = $item->postalCode ?? null;
			$primary = $item->primary ?? false;

			$addressList[] = new AccountAddress(
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

		return new AccountAddressGroup($addressList);
	}

}