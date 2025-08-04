<?php

namespace Espo\Modules\EnhancedFields\Classes\FieldProcessing\ContactAddress;

use Espo\Core\ApplicationState;
use Espo\Core\FieldProcessing\PhoneNumber\AccessChecker;
use Espo\Core\FieldProcessing\Saver as SaverInterface;
use Espo\Core\FieldProcessing\Saver\Params;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;
use Espo\Modules\EnhancedFields\Entities\ContactAddress;
use Espo\Modules\EnhancedFields\Repositories\ContactAddress as ContactAddressRepository;
use Espo\ORM\Mapper\BaseMapper;
use Espo\ORM\Name\Attribute;
use stdClass;

/**
 * Processes ContactAddress field data during entity save.
 * Creates or finds ContactAddress entities and manages relationships.
 *
 * @implements SaverInterface<Entity>
 */
class Saver implements SaverInterface {
	private const ATTR_CONTACT_ADDRESS = 'contactAddress';
	private const ATTR_CONTACT_ADDRESS_DATA = 'contactAddressData';
	private const ATTR_CONTACT_ADDRESS_IS_INVALID = 'contactAddressIsInvalid';

	public function __construct(
		private readonly \Espo\ORM\EntityManager $entityManager,
		private readonly ApplicationState        $applicationState,
		private readonly AccessChecker           $accessChecker,
		private readonly Metadata                $metadata
	) {}

	public function process(Entity $entity, Params $params): void {
		$entityType = $entity->getEntityType();

		$defs = $this->entityManager->getDefs()->getEntity($entityType);

		if (!$defs->hasField(self::ATTR_CONTACT_ADDRESS)) {
			return;
		}

		if ($defs->getField(self::ATTR_CONTACT_ADDRESS)->getType() !== 'contactAddress') {
			return;
		}

		$contactAddressData = null;

		if ($entity->has(self::ATTR_CONTACT_ADDRESS_DATA)) {
			$contactAddressData = $entity->get(self::ATTR_CONTACT_ADDRESS_DATA);
		}

		if ($contactAddressData !== null && $entity->isAttributeChanged(self::ATTR_CONTACT_ADDRESS_DATA)) {
			$this->storeData($entity);

			return;
		}

		if ($entity->has(self::ATTR_CONTACT_ADDRESS)) {
			$this->storePrimary($entity);
		}
	}


	private function storeData(Entity $entity): void {
		if (!$entity->has(self::ATTR_CONTACT_ADDRESS_DATA)) {
			return;
		}

		$contactAddressValue = $entity->get(self::ATTR_CONTACT_ADDRESS);

		if (is_string($contactAddressValue)) {
			$contactAddressValue = trim($contactAddressValue);
		}

		$contactAddressData = $entity->get(self::ATTR_CONTACT_ADDRESS_DATA);

		if (!is_array($contactAddressData)) {
			return;
		}

		$noPrimary = array_filter($contactAddressData, static fn($item) => !empty($item->primary)) === [];

		if ($noPrimary && $contactAddressData !== []) {
			$contactAddressData[0]->primary = true;
		}

		$keyList = [];
		$keyPreviousList = [];
		$previousContactAddressData = [];

		if (!$entity->isNew()) {
			/** @var ContactAddressRepository $repository */
			$repository = $this->entityManager->getRepository(ContactAddress::ENTITY_TYPE);

			$previousContactAddressData = $repository->getContactAddressData($entity);
		}

		$hash = (object)[];
		$hashPrevious = (object)[];

		foreach ($contactAddressData as $row) {
			$key = $row->contactAddressId ?? ContactAddress::generateAddressKey($row);

			if (empty($key)) {
				continue;
			}

			$type = $row->type ??
				$this->metadata
					->get(['entityDefs', $entity->getEntityType(), 'fields', 'contactAddress', 'defaultType']);

			$hash->$key = [
				'primary' => !empty($row->primary),
				'type' => $type,
				'street' => $row->street ?? '',
				'city' => $row->city ?? '',
				'state' => $row->state ?? '',
				'postalCode' => $row->postalCode ?? '',
				'country' => $row->country ?? '',
				'invalid' => !empty($row->invalid),
				'accountId' => $row->accountId ?? null,
				'description' => $row->description ?? '',
			];

			$keyList[] = $key;
		}

		if (
			$contactAddressValue &&
			$entity->has(self::ATTR_CONTACT_ADDRESS_IS_INVALID) && (
				$entity->isNew() ||
				(
					$entity->hasFetched(self::ATTR_CONTACT_ADDRESS_IS_INVALID) &&
					$entity->isAttributeChanged(self::ATTR_CONTACT_ADDRESS_IS_INVALID)
				)
			)
		) {
			$key = $contactAddressValue;

			if (isset($hash->$key)) {
				$hash->{$key}['invalid'] = (bool)$entity->get(self::ATTR_CONTACT_ADDRESS_IS_INVALID);
			}
		}

		foreach ($previousContactAddressData as $row) {
			$key = $row->contactAddressId ?? null;

			if (empty($key)) {
				continue;
			}

			$hashPrevious->$key = [
				'primary' => (bool)$row->primary,
				'type' => $row->type,
				'street' => $row->street ?? '',
				'city' => $row->city ?? '',
				'state' => $row->state ?? '',
				'postalCode' => $row->postalCode ?? '',
				'country' => $row->country ?? '',
				'invalid' => (bool)$row->invalid,
				'accountId' => $row->accountId ?? null,
				'description' => $row->description ?? '',
			];

			$keyPreviousList[] = $key;
		}

		$primary = null;

		$toCreateList = [];
		$toUpdateList = [];
		$toRemoveList = [];

		$revertData = [];

		foreach ($keyList as $key) {
			$new = true;
			$changed = false;

			if ($hash->{$key}['primary']) {
				$primary = $key;
			}

			if (property_exists($hashPrevious, $key)) {
				$new = false;

				$changed =
					$hash->{$key}['type'] !== $hashPrevious->{$key}['type'] ||
					$hash->{$key}['street'] !== $hashPrevious->{$key}['street'] ||
					$hash->{$key}['city'] !== $hashPrevious->{$key}['city'] ||
					$hash->{$key}['state'] !== $hashPrevious->{$key}['state'] ||
					$hash->{$key}['postalCode'] !== $hashPrevious->{$key}['postalCode'] ||
					$hash->{$key}['country'] !== $hashPrevious->{$key}['country'] ||
					$hash->{$key}['invalid'] !== $hashPrevious->{$key}['invalid'] ||
					$hash->{$key}['accountId'] !== $hashPrevious->{$key}['accountId'] ||
					$hash->{$key}['description'] !== $hashPrevious->{$key}['description'];

				if (
					$hash->{$key}['primary'] &&
					$hash->{$key}['primary'] === $hashPrevious->{$key}['primary']
				) {
					$primary = null;
				}
			}

			if ($new) {
				$toCreateList[] = $key;
			}
			if ($changed) {
				$toUpdateList[] = $key;
			}
		}

		foreach ($keyPreviousList as $key) {
			if (!property_exists($hash, $key)) {
				$toRemoveList[] = $key;
			}
		}

		foreach ($toRemoveList as $name) {
			$contactAddress = $this->getById($name);
			if (!$contactAddress) {
				continue;
			}

			$delete = $this->entityManager->getQueryBuilder()
				->delete()
				->from(ContactAddress::RELATION_ENTITY_CONTACT_ADDRESS)
				->where([
					'entityId' => $entity->getId(),
					'entityType' => $entity->getEntityType(),
					'contactAddressId' => $contactAddress->getId(),
				])
				->build();

			$this->entityManager->getQueryExecutor()->execute($delete);
		}

		foreach ($toUpdateList as $name) {
			$contactAddress = $this->getById($name);

			if ($contactAddress) {
				$skipSave = $this->checkChangeIsForbidden($contactAddress, $entity);

				if (!$skipSave) {
					$contactAddress->set([
						'type' => $hash->{$name}['type'],
						'street' => $hash->{$name}['street'],
						'city' => $hash->{$name}['city'],
						'state' => $hash->{$name}['state'],
						'postalCode' => $hash->{$name}['postalCode'],
						'country' => $hash->{$name}['country'],
						'invalid' => $hash->{$name}['invalid'],
						'accountId' => $hash->{$name}['accountId'],
						'description' => $hash->{$name}['description'],
					]);

					$this->entityManager->saveEntity($contactAddress);
				} else {
					$revertData[$name] = [
						'type' => $contactAddress->get('type'),
						'street' => $contactAddress->get('street'),
						'city' => $contactAddress->get('city'),
						'state' => $contactAddress->get('state'),
						'postalCode' => $contactAddress->get('postalCode'),
						'country' => $contactAddress->get('country'),
						'invalid' => $contactAddress->get('invalid'),
						'accountId' => $contactAddress->get('accountId'),
						'description' => $contactAddress->get('description'),
					];
				}
			}
		}

		foreach ($toCreateList as $name) {
			$contactAddress = $this->getById($name);

			if (!$contactAddress) {
				$contactAddress = $this->entityManager->getNewEntity(ContactAddress::ENTITY_TYPE);

				$contactAddress->set([
					'name' => $name,
					'type' => $hash->{$name}['type'],
					'street' => $hash->{$name}['street'],
					'city' => $hash->{$name}['city'],
					'state' => $hash->{$name}['state'],
					'postalCode' => $hash->{$name}['postalCode'],
					'country' => $hash->{$name}['country'],
					'invalid' => $hash->{$name}['invalid'],
					'accountId' => $hash->{$name}['accountId'],
					'description' => $hash->{$name}['description'],
				]);

				$this->entityManager->saveEntity($contactAddress);
			} else {
				$skipSave = $this->checkChangeIsForbidden($contactAddress, $entity);

				if (!$skipSave) {
					if (
						$contactAddress->get('type') !== $hash->{$name}['type'] ||
						$contactAddress->get('street') !== $hash->{$name}['street'] ||
						$contactAddress->get('city') !== $hash->{$name}['city'] ||
						$contactAddress->get('state') !== $hash->{$name}['state'] ||
						$contactAddress->get('postalCode') !== $hash->{$name}['postalCode'] ||
						$contactAddress->get('country') !== $hash->{$name}['country'] ||
						$contactAddress->get('invalid') !== $hash->{$name}['invalid'] ||
						$contactAddress->get('accountId') !== $hash->{$name}['accountId'] ||
						$contactAddress->get('description') !== $hash->{$name}['description']
					) {
						$contactAddress->set([
							'type' => $hash->{$name}['type'],
							'street' => $hash->{$name}['street'],
							'city' => $hash->{$name}['city'],
							'state' => $hash->{$name}['state'],
							'postalCode' => $hash->{$name}['postalCode'],
							'country' => $hash->{$name}['country'],
							'invalid' => $hash->{$name}['invalid'],
							'accountId' => $hash->{$name}['accountId'],
							'description' => $hash->{$name}['description'],
						]);

						$this->entityManager->saveEntity($contactAddress);
					}
				} else {
					$revertData[$name] = [
						'type' => $contactAddress->get('type'),
						'street' => $contactAddress->get('street'),
						'city' => $contactAddress->get('city'),
						'state' => $contactAddress->get('state'),
						'postalCode' => $contactAddress->get('postalCode'),
						'country' => $contactAddress->get('country'),
						'invalid' => $contactAddress->get('invalid'),
						'description' => $contactAddress->get('description'),
						'accountId' => $contactAddress->get('accountId'),
					];
				}
			}

			$entityContactAddress = $this->entityManager->getNewEntity(ContactAddress::RELATION_ENTITY_CONTACT_ADDRESS);

			$entityContactAddress->set([
				'entityId' => $entity->getId(),
				'entityType' => $entity->getEntityType(),
				'contactAddressId' => $contactAddress->getId(),
				'primary' => $name === $primary,
				Attribute::DELETED => false,
			]);

			/** @var BaseMapper $mapper */
			$mapper = $this->entityManager->getMapper();

			$mapper->insertOnDuplicateUpdate($entityContactAddress, [
				'primary',
				Attribute::DELETED,
			]);
		}

		if ($primary) {
			$contactAddress = $this->getById($primary);

			$entity->set(self::ATTR_CONTACT_ADDRESS, $primary);

			if ($contactAddress) {
				$update1 = $this->entityManager
					->getQueryBuilder()
					->update()
					->in('EntityContactAddress')
					->set(['primary' => false])
					->where([
						'entityId' => $entity->getId(),
						'entityType' => $entity->getEntityType(),
						'primary' => true,
						Attribute::DELETED => false,
					])
					->build();

				$this->entityManager->getQueryExecutor()->execute($update1);

				$update2 = $this->entityManager
					->getQueryBuilder()
					->update()
					->in('EntityContactAddress')
					->set(['primary' => true])
					->where([
						'entityId' => $entity->getId(),
						'entityType' => $entity->getEntityType(),
						'contactAddressId' => $contactAddress->getId(),
						Attribute::DELETED => false,
					])
					->build();

				$this->entityManager->getQueryExecutor()->execute($update2);
			}
		}

		if (!empty($revertData)) {
			foreach ($contactAddressData as $row) {
				if (empty($revertData[$row->contactAddress])) {
					continue;
				}

				$row->type = $revertData[$row->contactAddress]['type'];
				$row->street = $revertData[$row->contactAddress]['street'];
				$row->city = $revertData[$row->contactAddress]['city'];
				$row->state = $revertData[$row->contactAddress]['state'];
				$row->postalCode = $revertData[$row->contactAddress]['postalCode'];
				$row->country = $revertData[$row->contactAddress]['country'];
				$row->invalid = $revertData[$row->contactAddress]['invalid'];
				$row->description = $revertData[$row->contactAddress]['description'];
				$row->accountId = $revertData[$row->contactAddress]['accountId'];
			}

			$entity->set(self::ATTR_CONTACT_ADDRESS_DATA, $contactAddressData);
		}
	}

	private function storePrimary(Entity $entity): void {
		if (!$entity->has(self::ATTR_CONTACT_ADDRESS)) {
			return;
		}

		$contactAddressValue = trim($entity->get(self::ATTR_CONTACT_ADDRESS) ?? '');

		$entityRepository = $this->entityManager->getRDBRepository($entity->getEntityType());

		if (!empty($contactAddressValue)) {
			if ($contactAddressValue !== $entity->getFetched(self::ATTR_CONTACT_ADDRESS)) {
				$this->storePrimaryNotEmpty($contactAddressValue, $entity);

				return;
			}

			if (
				$entity->has(self::ATTR_CONTACT_ADDRESS_IS_INVALID) &&
				(
					$entity->isNew() ||
					(
						$entity->hasFetched(self::ATTR_CONTACT_ADDRESS_IS_INVALID) &&
						$entity->isAttributeChanged(self::ATTR_CONTACT_ADDRESS_IS_INVALID)
					)
				)
			) {
				$this->markAddressInvalid($contactAddressValue, (bool)$entity->get(self::ATTR_CONTACT_ADDRESS_IS_INVALID));
			}

			return;
		}

		$contactAddressValueOld = $entity->getFetched(self::ATTR_CONTACT_ADDRESS);

		if (!empty($contactAddressValueOld)) {
			$contactAddressOld = $this->getById($contactAddressValueOld);

			if ($contactAddressOld) {
				$entityRepository
					->getRelation($entity, 'contactAddresses')
					->unrelate($contactAddressOld, [SaveOption::SKIP_HOOKS => true]);
			}
		}
	}

	private function getById(string $id): ?ContactAddress {
		/** @var ContactAddressRepository $repository */
		$repository = $this->entityManager->getRepository(ContactAddress::ENTITY_TYPE);

		return $repository->getById($id);
	}

	private function markAddressInvalid(string $name, bool $isInvalid = true): void {
		/** @var ContactAddressRepository $repository */
		$repository = $this->entityManager->getRepository(ContactAddress::ENTITY_TYPE);
		$repository->markAddressInvalid($name, $isInvalid);
	}

	private function checkChangeIsForbidden(ContactAddress $contactAddress, Entity $entity): bool {
		if (!$this->applicationState->hasUser()) {
			return true;
		}

		$user = $this->applicationState->getUser();

		// @todo Check if not modified by system.

		return false; // @todo !$this->accessChecker->checkEdit($user, $contactAddress, $entity);
	}

	private function storePrimaryNotEmpty(string $contactAddressValue, Entity $entity): void {
		$entityRepository = $this->entityManager->getRDBRepository($entity->getEntityType());

		$contactAddressNew = $this->entityManager
			->getRDBRepository(ContactAddress::ENTITY_TYPE)
			->where([
				'hash' => $contactAddressValue,
			])
			->findOne();

		if (!$contactAddressNew) {
			/** @var ContactAddress $contactAddressNew */
			$contactAddressNew = $this->entityManager->getNewEntity(ContactAddress::ENTITY_TYPE);

			$contactAddressNew->set('hash', $contactAddressValue);

			if ($entity->has(self::ATTR_CONTACT_ADDRESS_IS_INVALID)) {
				$contactAddressNew->set('invalid', (bool)$entity->get(self::ATTR_CONTACT_ADDRESS_IS_INVALID));
			}

			$defaultType = $this->metadata
				->get("entityDefs.{$entity->getEntityType()}.fields.contactAddress.defaultType");

			$contactAddressNew->set('type', $defaultType);

			$this->entityManager->saveEntity($contactAddressNew);
		}

		$contactAddressValueOld = $entity->getFetched(self::ATTR_CONTACT_ADDRESS);

		if (!empty($contactAddressValueOld)) {
			$contactAddressOld = $this->getById($contactAddressValueOld);

			if ($contactAddressOld) {
				$entityRepository
					->getRelation($entity, 'contactAddresses')
					->unrelate($contactAddressOld, [SaveOption::SKIP_HOOKS => true]);
			}
		}

		$entityRepository
			->getRelation($entity, 'contactAddresses')
			->relate($contactAddressNew, null, [SaveOption::SKIP_HOOKS => true]);

		if ($entity->has(self::ATTR_CONTACT_ADDRESS_IS_INVALID)) {
			$this->markAddressInvalid($contactAddressValue, (bool)$entity->get(self::ATTR_CONTACT_ADDRESS_IS_INVALID));
		}

		$update = $this->entityManager
			->getQueryBuilder()
			->update()
			->in('EntityContactAddress')
			->set(['primary' => true])
			->where([
				'entityId' => $entity->getId(),
				'entityType' => $entity->getEntityType(),
				'contactAddressId' => $contactAddressNew->getId(),
			])
			->build();

		$this->entityManager->getQueryExecutor()->execute($update);
	}

}