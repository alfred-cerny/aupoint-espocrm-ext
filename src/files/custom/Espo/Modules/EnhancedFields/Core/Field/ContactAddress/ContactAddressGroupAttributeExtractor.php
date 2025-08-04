<?php

namespace Espo\Modules\EnhancedFields\Core\Field\ContactAddress;

use Espo\ORM\Value\AttributeExtractor;
use stdClass;

/**
 * Extracts attributes from ContactAddressGroup for storage.
 * Converts ContactAddressGroup value objects to data suitable for entity storage.
 */
class ContactAddressGroupAttributeExtractor implements AttributeExtractor {
	/**
	 * @param ContactAddressGroup $value
	 */
	public function extract(object $value, string $field): stdClass {
		if (!$value instanceof ContactAddressGroup) {
			throw new \RuntimeException("Invalid value object type.");
		}

		$dataList = [];
		$primaryId = null;

		foreach ($value->getList() as $address) {
			$o = new stdClass();

			$o->contactAddressId = $address->getAddressId();
			$o->contactAddressName = $address->getName();
			$o->street = $address->getStreet();
			$o->city = $address->getCity();
			$o->state = $address->getState();
			$o->country = $address->getCountry();
			$o->postalCode = $address->getPostalCode();
			$o->primary = $address->isPrimary();

			if ($address->isPrimary()) {
				$primaryId = $address->getAddressId();
			}

			$dataList[] = $o;
		}

		$attributes = [
			$field . 'Data' => $dataList,
			$field . 'Ids' => $value->getIdList(),
			$field => $primaryId ? $value->getPrimary()?->getName() : null,
		];

		return (object)$attributes;
	}

	public function extractFromNull(string $field): stdClass {
		return (object)[
			$field . 'Data' => [],
			$field . 'Ids' => [],
			$field => null,
		];
	}

}