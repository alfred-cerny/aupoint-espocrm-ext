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

		$bidsMapping = [];
		foreach ($bids as $bid) {
			$type = $bid->get('type');
			if (empty($type)) {
				continue;
			}
			$bidsMapping[$type] ??= [];
			$bidsMapping[$type][] = $bid->getValueMap();
		}
		foreach ($bidsMapping as $type => $bidsData) {
			$entity->set("{$this->bidsFieldName}{$type}Data", $bidsData);
		}

		$entity->set("{$this->bidsFieldName}Data", (array)$bids->getValueMapList());
	}

}