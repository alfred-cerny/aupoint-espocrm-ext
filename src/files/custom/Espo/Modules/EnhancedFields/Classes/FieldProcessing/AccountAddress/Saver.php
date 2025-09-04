<?php

namespace Espo\Modules\EnhancedFields\Classes\FieldProcessing\AccountAddress;

use Espo\Core\ApplicationState;
use Espo\Core\FieldProcessing\PhoneNumber\AccessChecker;
use Espo\Core\FieldProcessing\Saver as SaverInterface;
use Espo\Core\FieldProcessing\Saver\Params;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;
use Espo\Modules\EnhancedFields\Entities\AccountAddress;
use Espo\Modules\EnhancedFields\Repositories\AccountAddress as AccountAddressRepository;
use Espo\ORM\Mapper\BaseMapper;
use Espo\ORM\Name\Attribute;
use stdClass;

/**
 * Processes AccountAddress field data during entity save.
 * Creates or finds AccountAddress entities and manages relationships.
 *
 * @implements SaverInterface<Entity>
 */
class Saver implements SaverInterface {
	private const ATTR_ACCOUNT_ADDRESS = 'accountAddress';
	private const ATTR_ACCOUNT_ADDRESS_DATA = 'accountAddressData';
	private const ATTR_ACCOUNT_ADDRESS_IS_INVALID = 'accountAddressIsInvalid';

	public function __construct(
		private readonly \Espo\ORM\EntityManager $entityManager,
		private readonly ApplicationState        $applicationState,
		private readonly AccessChecker           $accessChecker,
		private readonly Metadata                $metadata
	) {}

	public function process(Entity $entity, Params $params): void {
		$entityType = $entity->getEntityType();

		$defs = $this->entityManager->getDefs()->getEntity($entityType);

		if (!$defs->hasField(self::ATTR_ACCOUNT_ADDRESS)) {
			return;
		}

		if ($defs->getField(self::ATTR_ACCOUNT_ADDRESS)->getType() !== 'accountAddress') {
			return;
		}

		$accountAddressData = null;

		if ($entity->has(self::ATTR_ACCOUNT_ADDRESS_DATA)) {
			$accountAddressData = $entity->get(self::ATTR_ACCOUNT_ADDRESS_DATA);
		}

		if ($accountAddressData !== null && $entity->isAttributeChanged(self::ATTR_ACCOUNT_ADDRESS_DATA)) {
			$this->storeData($entity);

			return;
		}

		if ($entity->has(self::ATTR_ACCOUNT_ADDRESS)) {
			$this->storePrimary($entity);
		}
	}


	private function storeData(Entity $entity): void {
		if (!$entity->has(self::ATTR_ACCOUNT_ADDRESS_DATA)) {
			return;
		}

		$accountAddressValue = $entity->get(self::ATTR_ACCOUNT_ADDRESS);

		if (is_string($accountAddressValue)) {
			$accountAddressValue = trim($accountAddressValue);
		}

		$accountAddressData = $entity->get(self::ATTR_ACCOUNT_ADDRESS_DATA);

		if (!is_array($accountAddressData)) {
			return;
		}

		$noPrimary = array_filter($accountAddressData, static fn($item) => !empty($item->primary)) === [];

		if ($noPrimary && $accountAddressData !== []) {
			$accountAddressData[0]->primary = true;
		}

		$keyList = [];
		$keyPreviousList = [];
		$previousAccountAddressData = [];

		if (!$entity->isNew()) {
			/** @var AccountAddressRepository $repository */
			$repository = $this->entityManager->getRepository(AccountAddress::ENTITY_TYPE);

			$previousAccountAddressData = $repository->getAccountAddressData($entity);
		}

		$hash = (object)[];
		$hashPrevious = (object)[];

		foreach ($accountAddressData as $row) {
			$key = $row->accountAddressId ?? AccountAddress::generateAddressKey($row);

			if (empty($key)) {
				continue;
			}

			/*$labels = $row->labels ??
				$this->metadata
					->get(['entityDefs', $entity->getEntityType(), 'fields', 'accountAddress', 'labels', 'options']);
			if (is_array($type)) {
				$type = $type[0];
			}*/

			$data = [
				'primary' => !empty($row->primary),
				'labels' => $row->labels ?? null,
				'street' => $row->street ?? '',
				'city' => $row->city ?? '',
				'state' => $row->state ?? '',
				'postalCode' => $row->postalCode ?? '',
				'country' => $row->country ?? '',
				'invalid' => !empty($row->invalid),
				'description' => $row->description ?? '',
			];

			if (property_exists($row, 'accountId')) {
				$data['accountId'] = empty($row->accountId) ? null : $row->accountId;
			}

			$hash->$key = $data;

			$keyList[] = $key;
		}

		if (
			$accountAddressValue &&
			$entity->has(self::ATTR_ACCOUNT_ADDRESS_IS_INVALID) && (
				$entity->isNew() ||
				(
					$entity->hasFetched(self::ATTR_ACCOUNT_ADDRESS_IS_INVALID) &&
					$entity->isAttributeChanged(self::ATTR_ACCOUNT_ADDRESS_IS_INVALID)
				)
			)
		) {
			$key = $accountAddressValue;

			if (isset($hash->$key)) {
				$hash->{$key}['invalid'] = (bool)$entity->get(self::ATTR_ACCOUNT_ADDRESS_IS_INVALID);
			}
		}

		foreach ($previousAccountAddressData as $row) {
			$key = $row->accountAddressId ?? null;

			if (empty($key)) {
				continue;
			}
			/*$labels = $row->labels ?? null;
			if (is_array($labels)) {
				$labels = $labels[0];
			}*/

			$hashPrevious->$key = [
				'primary' => (bool)$row->primary,
				'labels' => $row->labels ?? null,
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
					$hash->{$key}['labels'] !== $hashPrevious->{$key}['labels'] ||
					$hash->{$key}['street'] !== $hashPrevious->{$key}['street'] ||
					$hash->{$key}['city'] !== $hashPrevious->{$key}['city'] ||
					$hash->{$key}['state'] !== $hashPrevious->{$key}['state'] ||
					$hash->{$key}['postalCode'] !== $hashPrevious->{$key}['postalCode'] ||
					$hash->{$key}['country'] !== $hashPrevious->{$key}['country'] ||
					$hash->{$key}['invalid'] !== $hashPrevious->{$key}['invalid'] ||
					$hash->{$key}['description'] !== $hashPrevious->{$key}['description'];

				if (
					!$changed &&
					array_key_exists('accountId', $hash->{$key})
				) {
					$changed = $hash->{$key}['accountId'] !== $hashPrevious->{$key}['accountId'];
				}

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
			$accountAddress = $this->getById($name);
			if (!$accountAddress) {
				continue;
			}

			$delete = $this->entityManager->getQueryBuilder()
				->delete()
				->from(AccountAddress::RELATION_ENTITY_ACCOUNT_ADDRESS)
				->where([
					'entityId' => $entity->getId(),
					'entityType' => $entity->getEntityType(),
					'accountAddressId' => $accountAddress->getId(),
				])
				->build();

			$this->entityManager->getQueryExecutor()->execute($delete);
		}

		foreach ($toUpdateList as $name) {
			$accountAddress = $this->getById($name);

			if ($accountAddress) {
				$skipSave = $this->checkChangeIsForbidden($accountAddress, $entity);

				if (!$skipSave) {
					$accountAddress->setMultiple([
						'labels' => $hash->{$name}['labels'],
						'street' => $hash->{$name}['street'],
						'city' => $hash->{$name}['city'],
						'state' => $hash->{$name}['state'],
						'postalCode' => $hash->{$name}['postalCode'],
						'country' => $hash->{$name}['country'],
						'invalid' => $hash->{$name}['invalid'],
						'description' => $hash->{$name}['description'],
					]);

					if (array_key_exists('accountId', $hash->{$name})) {
						$accountAddress->set('accountId', $hash->{$name}['accountId']);
					}

					$this->entityManager->saveEntity($accountAddress);
				} else {
					$revertData[$name] = [
						'labels' => $accountAddress->get('labels'),
						'street' => $accountAddress->get('street'),
						'city' => $accountAddress->get('city'),
						'state' => $accountAddress->get('state'),
						'postalCode' => $accountAddress->get('postalCode'),
						'country' => $accountAddress->get('country'),
						'invalid' => $accountAddress->get('invalid'),
						'accountId' => $accountAddress->get('accountId'),
						'description' => $accountAddress->get('description'),
					];
				}
			}
		}

		foreach ($toCreateList as $name) {
			$accountAddress = $this->getById($name);

			if (!$accountAddress) {
				$accountAddress = $this->entityManager->getNewEntity(AccountAddress::ENTITY_TYPE);

				$accountAddress->set([
					'name' => $name,
					'labels' => $hash->{$name}['labels'],
					'street' => $hash->{$name}['street'],
					'city' => $hash->{$name}['city'],
					'state' => $hash->{$name}['state'],
					'postalCode' => $hash->{$name}['postalCode'],
					'country' => $hash->{$name}['country'],
					'invalid' => $hash->{$name}['invalid'],
					'accountId' => $hash->{$name}['accountId'],
					'description' => $hash->{$name}['description'],
				]);

				$this->entityManager->saveEntity($accountAddress);
			} else {
				$skipSave = $this->checkChangeIsForbidden($accountAddress, $entity);

				if (!$skipSave) {
					if (
						$accountAddress->get('labels') !== $hash->{$name}['labels'] ||
						$accountAddress->get('street') !== $hash->{$name}['street'] ||
						$accountAddress->get('city') !== $hash->{$name}['city'] ||
						$accountAddress->get('state') !== $hash->{$name}['state'] ||
						$accountAddress->get('postalCode') !== $hash->{$name}['postalCode'] ||
						$accountAddress->get('country') !== $hash->{$name}['country'] ||
						$accountAddress->get('invalid') !== $hash->{$name}['invalid'] ||
						$accountAddress->get('accountId') !== $hash->{$name}['accountId'] ||
						$accountAddress->get('description') !== $hash->{$name}['description']
					) {
						$accountAddress->set([
							'labels' => $hash->{$name}['labels'],
							'street' => $hash->{$name}['street'],
							'city' => $hash->{$name}['city'],
							'state' => $hash->{$name}['state'],
							'postalCode' => $hash->{$name}['postalCode'],
							'country' => $hash->{$name}['country'],
							'invalid' => $hash->{$name}['invalid'],
							'accountId' => $hash->{$name}['accountId'],
							'description' => $hash->{$name}['description'],
						]);

						$this->entityManager->saveEntity($accountAddress);
					}
				} else {
					$revertData[$name] = [
						'labels' => $accountAddress->get('labels'),
						'street' => $accountAddress->get('street'),
						'city' => $accountAddress->get('city'),
						'state' => $accountAddress->get('state'),
						'postalCode' => $accountAddress->get('postalCode'),
						'country' => $accountAddress->get('country'),
						'invalid' => $accountAddress->get('invalid'),
						'description' => $accountAddress->get('description'),
						'accountId' => $accountAddress->get('accountId'),
					];
				}
			}

			$entityAccountAddress = $this->entityManager->getNewEntity(AccountAddress::RELATION_ENTITY_ACCOUNT_ADDRESS);

			$entityAccountAddress->set([
				'entityId' => $entity->getId(),
				'entityType' => $entity->getEntityType(),
				'accountAddressId' => $accountAddress->getId(),
				'primary' => $name === $primary,
				Attribute::DELETED => false,
			]);

			/** @var BaseMapper $mapper */
			$mapper = $this->entityManager->getMapper();

			$mapper->insertOnDuplicateUpdate($entityAccountAddress, [
				'primary',
				Attribute::DELETED,
			]);
		}

		if ($primary) {
			$accountAddress = $this->getById($primary);

			$entity->set(self::ATTR_ACCOUNT_ADDRESS, $primary);

			if ($accountAddress) {
				$update1 = $this->entityManager
					->getQueryBuilder()
					->update()
					->in('EntityAccountAddress')
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
					->in('EntityAccountAddress')
					->set(['primary' => true])
					->where([
						'entityId' => $entity->getId(),
						'entityType' => $entity->getEntityType(),
						'accountAddressId' => $accountAddress->getId(),
						Attribute::DELETED => false,
					])
					->build();

				$this->entityManager->getQueryExecutor()->execute($update2);
			}
		}

		if (!empty($revertData)) {
			foreach ($accountAddressData as $row) {
				if (empty($revertData[$row->accountAddressId])) {
					continue;
				}

				$row->labels = $revertData[$row->accountAddressId]['labels'];
				$row->street = $revertData[$row->accountAddressId]['street'];
				$row->city = $revertData[$row->accountAddressId]['city'];
				$row->state = $revertData[$row->accountAddressId]['state'];
				$row->postalCode = $revertData[$row->accountAddressId]['postalCode'];
				$row->country = $revertData[$row->accountAddressId]['country'];
				$row->invalid = $revertData[$row->accountAddressId]['invalid'];
				$row->description = $revertData[$row->accountAddressId]['description'];
				$row->accountId = $revertData[$row->accountAddressId]['accountId'];
			}

			$entity->set(self::ATTR_ACCOUNT_ADDRESS_DATA, $accountAddressData);
		}
	}

	private function storePrimary(Entity $entity): void {
		if (!$entity->has(self::ATTR_ACCOUNT_ADDRESS)) {
			return;
		}

		$accountAddressValue = trim($entity->get(self::ATTR_ACCOUNT_ADDRESS) ?? '');

		$entityRepository = $this->entityManager->getRDBRepository($entity->getEntityType());

		if (!empty($accountAddressValue)) {
			if ($accountAddressValue !== $entity->getFetched(self::ATTR_ACCOUNT_ADDRESS)) {
				$this->storePrimaryNotEmpty($accountAddressValue, $entity);

				return;
			}

			if (
				$entity->has(self::ATTR_ACCOUNT_ADDRESS_IS_INVALID) &&
				(
					$entity->isNew() ||
					(
						$entity->hasFetched(self::ATTR_ACCOUNT_ADDRESS_IS_INVALID) &&
						$entity->isAttributeChanged(self::ATTR_ACCOUNT_ADDRESS_IS_INVALID)
					)
				)
			) {
				$this->markAddressInvalid($accountAddressValue, (bool)$entity->get(self::ATTR_ACCOUNT_ADDRESS_IS_INVALID));
			}

			return;
		}

		$accountAddressValueOld = $entity->getFetched(self::ATTR_ACCOUNT_ADDRESS);

		if (!empty($accountAddressValueOld)) {
			$accountAddressOld = $this->getById($accountAddressValueOld);

			if ($accountAddressOld) {
				$entityRepository
					->getRelation($entity, 'accountAddresses')
					->unrelate($accountAddressOld, [SaveOption::SKIP_HOOKS => true]);
			}
		}
	}

	private function getById(string $id): ?AccountAddress {
		/** @var AccountAddressRepository $repository */
		$repository = $this->entityManager->getRepository(AccountAddress::ENTITY_TYPE);

		return $repository->getById($id);
	}

	private function markAddressInvalid(string $name, bool $isInvalid = true): void {
		/** @var AccountAddressRepository $repository */
		$repository = $this->entityManager->getRepository(AccountAddress::ENTITY_TYPE);
		$repository->markAddressInvalid($name, $isInvalid);
	}

	private function checkChangeIsForbidden(AccountAddress $accountAddress, Entity $entity): bool {
		if (!$this->applicationState->hasUser()) {
			return true;
		}

		$user = $this->applicationState->getUser();

		// @todo Check if not modified by system.

		return false; // @todo !$this->accessChecker->checkEdit($user, $accountAddress, $entity);
	}

	private function storePrimaryNotEmpty(string $accountAddressValue, Entity $entity): void {
		$entityRepository = $this->entityManager->getRDBRepository($entity->getEntityType());

		$accountAddressNew = $this->entityManager
			->getRDBRepository(AccountAddress::ENTITY_TYPE)
			->where([
				'hash' => $accountAddressValue,
			])
			->findOne();

		if (!$accountAddressNew) {
			/** @var AccountAddress $accountAddressNew */
			$accountAddressNew = $this->entityManager->getNewEntity(AccountAddress::ENTITY_TYPE);

			$accountAddressNew->set('hash', $accountAddressValue);

			if ($entity->has(self::ATTR_ACCOUNT_ADDRESS_IS_INVALID)) {
				$accountAddressNew->set('invalid', (bool)$entity->get(self::ATTR_ACCOUNT_ADDRESS_IS_INVALID));
			}

			$defaultLabels = $this->metadata
				->get("entityDefs.{$entity->getEntityType()}.fields.accountAddress.defaultLabels");

			$accountAddressNew->set('labels', $defaultLabels);

			$this->entityManager->saveEntity($accountAddressNew);
		}

		$accountAddressValueOld = $entity->getFetched(self::ATTR_ACCOUNT_ADDRESS);

		if (!empty($accountAddressValueOld)) {
			$accountAddressOld = $this->getById($accountAddressValueOld);

			if ($accountAddressOld) {
				$entityRepository
					->getRelation($entity, 'accountAddresses')
					->unrelate($accountAddressOld, [SaveOption::SKIP_HOOKS => true]);
			}
		}

		$entityRepository
			->getRelation($entity, 'accountAddresses')
			->relate($accountAddressNew, null, [SaveOption::SKIP_HOOKS => true]);

		if ($entity->has(self::ATTR_ACCOUNT_ADDRESS_IS_INVALID)) {
			$this->markAddressInvalid($accountAddressValue, (bool)$entity->get(self::ATTR_ACCOUNT_ADDRESS_IS_INVALID));
		}

		$update = $this->entityManager
			->getQueryBuilder()
			->update()
			->in('EntityAccountAddress')
			->set(['primary' => true])
			->where([
				'entityId' => $entity->getId(),
				'entityType' => $entity->getEntityType(),
				'accountAddressId' => $accountAddressNew->getId(),
			])
			->build();

		$this->entityManager->getQueryExecutor()->execute($update);
	}

}