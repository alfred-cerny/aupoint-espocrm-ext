<?php

namespace Espo\Modules\EnhancedFields\Classes\FieldProcessing\Account;

use Espo\Core\FieldProcessing\Saver as SaverInterface;
use Espo\Core\FieldProcessing\Saver\Params;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs as OrmDefs;
use Espo\ORM\Entity;

class BidDataSaver implements SaverInterface {
	protected string $bidsFieldName = "opportunityBids";

	public function __construct(
		protected EntityManager $entityManager,
		protected Metadata      $metadata,
		protected OrmDefs       $ormDefs
	) {}

	public function process(Entity $entity, Params $params): void {
		if (
			!$entity->has($this->bidsFieldName)
		) {
			$this->bidsFieldName = 'bids';
			if (!$entity->has($this->bidsFieldName)) {
				return;
			}
		}
		$tm = $this->entityManager->getTransactionManager();
		$tm->start();
		$bidsRelation = $this->entityManager
			->getRDBRepository($entity->getEntityType())
			->getRelation($entity, $this->bidsFieldName);

		$bidsData = $entity->get("{$this->bidsFieldName}Data");
		if (!is_array($bidsData)) {
			$bidsData = [];
		}

		$bidsIdsToUnrelate = array_diff(
			$entity->getFetched("{$this->bidsFieldName}Ids"),
			array_column($bidsData, 'id')
		);
		if (!empty($bidsIdsToUnrelate)) {
			foreach ($bidsIdsToUnrelate as $bidId) {
				$bidsRelation->unrelateById($bidId);
			}
		}

		if (empty($bidsData)) {
			$tm->commit();
			return;
		}

		$bidsIds = array_column($bidsData, 'id');
		$resultingBidsData = [];
		$bidsToUpdateData = [];
		$bidsToCreateData = [];
		foreach ($bidsData as $bidDataObj) {
			$bidDataObj = (array)$bidDataObj;
			if ($id = $bidDataObj['id']) {
				$bidsToUpdateData[$id] = $bidDataObj;
			} else {
				$bidsToCreateData[] = $bidDataObj;
			}
		}
		$bids = $this->entityManager
			->getRDBRepository('OpportunityBid')
			->where('id', $bidsIds)
			->find();

		foreach ($bids as $bid) {
			$bidData = $bidsToUpdateData[$bid->getId()] ?? null;
			if (!$bidData) {
				continue;
			}
			$updateData = $bidData;
			unset($updateData['id']);
			$bid->setMultiple($updateData);
			$this->entityManager->saveEntity($bid);
			if (!$bidsRelation->isRelated($bid)) {
				$bidsRelation->relate($bid);
			}
			$resultingBidsData[] = (array)$bid->getValueMap();
		}
		foreach ($bidsToCreateData as $newBidData) {
			$newBid = $this->entityManager
				->createEntity('OpportunityBid', $newBidData);
			$bidsRelation->relate($newBid);
			$resultingBidsData[] = (array)$newBid->getValueMap();
		}

		$entity->set("{$this->bidsFieldName}Ids", array_column($resultingBidsData, 'id'));
		$entity->set("{$this->bidsFieldName}Data", $resultingBidsData);

		$tm->commit();
	}

}