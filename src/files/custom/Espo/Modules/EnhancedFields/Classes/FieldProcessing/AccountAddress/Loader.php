<?php

namespace Espo\Modules\EnhancedFields\Classes\FieldProcessing\AccountAddress;

use Espo\Core\FieldProcessing\Loader as LoaderInterface;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Metadata;
use Espo\Modules\EnhancedFields\Entities\AccountAddress;
use Espo\Modules\EnhancedFields\Repositories\AccountAddress as AccountAddressRepository;
use Espo\ORM\Defs as OrmDefs;
use Espo\ORM\Entity;

/**
 * Loads AccountAddress field data when entity is fetched.
 * Populates the accountAddressData attribute with full address information.
 */
class Loader implements LoaderInterface {
	public function __construct(
		protected EntityManager $entityManager,
		protected Metadata      $metadata,
		protected OrmDefs       $ormDefs
	) {}

	public function process(Entity $entity, Params $params): void {
		$fieldList = $this->getAccountAddressFieldList($entity->getEntityType());

		foreach ($fieldList as $fieldName) {
			$this->loadAccountAddressField($entity, $fieldName, $params);
		}
	}

	/**
	 * Get list of account address fields for an entity type.
	 *
	 * @param string $entityType
	 * @return string[]
	 */
	private function getAccountAddressFieldList(string $entityType): array {
		$fieldDefs = $this->metadata->get(['entityDefs', $entityType, 'fields'], []);
		$fieldList = [];

		foreach ($fieldDefs as $field => $defs) {
			if (($defs['type'] ?? null) === 'accountAddress') {
				$fieldList[] = $field;
			}
		}

		return $fieldList;
	}

	/**
	 * Load account address field data.
	 *
	 * @param Entity $entity
	 * @param string $field
	 * @param Params $params
	 */
	private function loadAccountAddressField(Entity $entity, string $fieldName, Params $params): void {
		$entityDefs = $this->ormDefs->getEntity($entity->getEntityType());

		if (!$entityDefs->hasField('accountAddress')) {
			return;
		}

		if ($entityDefs->getField('accountAddress')->getType() !== 'accountAddress') {
			return;
		}

		/** @var AccountAddressRepository $repository */
		$repository = $this->entityManager->getRepository(AccountAddress::ENTITY_TYPE);

		$accountAddressData = $repository->getAccountAddressData($entity);

		$entity->set('accountAddressData', $accountAddressData);
		$entity->setFetched('accountAddressData', $accountAddressData);
	}

}