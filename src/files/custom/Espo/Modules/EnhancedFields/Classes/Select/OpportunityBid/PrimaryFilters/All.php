<?php

namespace Espo\Modules\EnhancedFields\Classes\Select\OpportunityBid\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class All implements Filter {
	public function apply(SelectBuilder $queryBuilder): void {
		$queryBuilder->distinct();
	}

}
