<?php

namespace Espo\Modules\EnhancedFields\Classes\FieldProcessing\Account;

use Espo\Core\FieldProcessing\Loader as LoaderInterface;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs as OrmDefs;
use Espo\ORM\Entity;

class BidDataLoader implements LoaderInterface {
	public function __construct(
		protected EntityManager $entityManager,
		protected Metadata      $metadata,
		protected OrmDefs       $ormDefs
	) {}

	public function process(Entity $entity, Params $params): void {
		$bidsIds = $entity->get('opportunityBidsIds');

		$bids = $this->entityManager
			->getRDBRepository('OpportunityBid')
			->where('id', $bidsIds)
			->find();

		$bidsData = array_column($bids->getValueMapList(), null, 'id');
		$entity->set('opportunityBidsData', (object)$bidsData);
	}

}