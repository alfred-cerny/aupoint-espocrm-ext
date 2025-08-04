<?php

namespace Espo\Modules\EnhancedFields\Classes\FieldProcessing\ContactAddress;

use Espo\Core\FieldProcessing\Loader as LoaderInterface;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Metadata;
use Espo\Modules\EnhancedFields\Entities\ContactAddress;
use Espo\Modules\EnhancedFields\Repositories\ContactAddress as ContactAddressRepository;
use Espo\ORM\Defs as OrmDefs;
use Espo\ORM\Entity;

/**
 * Loads ContactAddress field data when entity is fetched.
 * Populates the contactAddressData attribute with full address information.
 */
class Loader implements LoaderInterface {
	public function __construct(
		protected EntityManager $entityManager,
		protected Metadata      $metadata,
		protected OrmDefs       $ormDefs
	) {}

	public function process(Entity $entity, Params $params): void {
		$fieldList = $this->getContactAddressFieldList($entity->getEntityType());

		foreach ($fieldList as $fieldName) {
			$this->loadContactAddressField($entity, $fieldName, $params);
		}
	}

	/**
	 * Get list of contact address fields for an entity type.
	 *
	 * @param string $entityType
	 * @return string[]
	 */
	private function getContactAddressFieldList(string $entityType): array {
		$fieldDefs = $this->metadata->get(['entityDefs', $entityType, 'fields'], []);
		$fieldList = [];

		foreach ($fieldDefs as $field => $defs) {
			if (($defs['type'] ?? null) === 'contactAddress') {
				$fieldList[] = $field;
			}
		}

		return $fieldList;
	}

	/**
	 * Load contact address field data.
	 *
	 * @param Entity $entity
	 * @param string $field
	 * @param Params $params
	 */
	private function loadContactAddressField(Entity $entity, string $fieldName, Params $params): void {
		$entityDefs = $this->ormDefs->getEntity($entity->getEntityType());

		if (!$entityDefs->hasField('contactAddress')) {
			return;
		}

		if ($entityDefs->getField('contactAddress')->getType() !== 'contactAddress') {
			return;
		}

		/** @var ContactAddressRepository $repository */
		$repository = $this->entityManager->getRepository(ContactAddress::ENTITY_TYPE);

		$contactAddressData = $repository->getContactAddressData($entity);
		$GLOBALS['log']->debug("Loaded contactAddressData: " . json_encode($contactAddressData));
		$entity->set('contactAddressData', $contactAddressData);
		$entity->setFetched('contactAddressData', $contactAddressData);
	}

}