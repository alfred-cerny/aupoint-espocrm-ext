<?php

namespace Espo\Modules\EnhancedFields\Entities;

use Espo\Core\ORM\Entity;
use stdClass;

class AccountAddress extends Entity {
	public const ENTITY_TYPE = 'AccountAddress';
	public const RELATION_ENTITY_ACCOUNT_ADDRESS = 'EntityAccountAddress';
	public const FIELDS = [
		'type',
		'street',
		'city',
		'state',
		'postalCode',
		'country',
		'invalid',
		'description',
		'accountId',
		'accountName'
	];

	public function getCity(): ?string {
		return $this->get('city');
	}

	public function getState(): ?string {
		return $this->get('state');
	}

	public function getPostalCode(): ?string {
		return $this->get('postalCode');
	}

	public function getCountry(): ?string {
		return $this->get('country');
	}

	public function getStreet(): ?string {
		return $this->get('street');
	}

	/**
	 * Formats the address components into a display name.
	 *
	 * @return string Formatted address string for display and search
	 */
	public function getFormattedName(): string {
		$parts = [];

		if ($street = $this->getStreet()) {
			$parts[] = $street;
		}

		$cityStateParts = [];
		if ($city = $this->getCity()) {
			$cityStateParts[] = $city;
		}
		if ($state = $this->getState()) {
			$cityStateParts[] = $state;
		}
		if ($postalCode = $this->getPostalCode()) {
			$cityStateParts[] = $postalCode;
		}

		if (!empty($cityStateParts)) {
			$parts[] = implode(', ', $cityStateParts);
		}

		if ($country = $this->getCountry()) {
			$parts[] = $country;
		}

		return implode(', ', $parts);
	}

	public static function generateAddressKey(stdClass $data): string {
		$normalized = [
			'street' => self::normalizeString($data->street ?? ''),
			'city' => self::normalizeString($data->city ?? ''),
			'state' => self::normalizeString($data->state ?? ''),
			'postalCode' => self::normalizeString($data->postalCode ?? ''),
			'country' => self::normalizeString($data->country ?? '')
		];

		return hash('sha256', serialize($normalized));
	}

	protected static function normalizeString(string $str): string {
		return strtolower(trim(preg_replace('/\s+/', ' ', $str)));
	}

}