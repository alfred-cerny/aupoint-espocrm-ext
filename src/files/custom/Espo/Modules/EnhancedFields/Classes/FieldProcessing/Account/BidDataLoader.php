<?php

namespace Espo\Modules\EnhancedFields\Classes\FieldProcessing\Account;

use Espo\Core\FieldProcessing\Loader as LoaderInterface;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs as OrmDefs;
use Espo\ORM\Entity;

class BidDataLoader implements LoaderInterface {
	protected string $bidsFieldName = "opportunityBids";

	public function __construct(
		protected EntityManager $entityManager,
		protected Metadata      $metadata,
		protected OrmDefs       $ormDefs
	) {}

	public function process(Entity $entity, Params $params): void {
		if (
			!$entity->hasRelation($this->bidsFieldName)
		) {
			$this->bidsFieldName = 'bids';
			if (!$entity->hasRelation($this->bidsFieldName)) {
				return;
			}
		}
		$bidsIds = $entity->get("{$this->bidsFieldName}Ids");

		$bids = $this->entityManager
			->getRDBRepository('OpportunityBid')
			->where('id', $bidsIds)
			->find();

		$entity->set("{$this->bidsFieldName}Data", (array)$bids->getValueMapList());
	}

}