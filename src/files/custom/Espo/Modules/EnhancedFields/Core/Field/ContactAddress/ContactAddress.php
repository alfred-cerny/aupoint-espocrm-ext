<?php

namespace Espo\Modules\EnhancedFields\Core\Field\ContactAddress;

/**
 * Represents a single contact address with all its properties.
 * Immutable value object for contact address data.
 */
class ContactAddress {
	private string $addressId;
	private string $name;
	private ?string $street;
	private ?string $city;
	private ?string $state;
	private ?string $country;
	private ?string $postalCode;
	private bool $primary;

	public function __construct(
		string  $addressId,
		string  $name,
		?string $street = null,
		?string $city = null,
		?string $state = null,
		?string $country = null,
		?string $postalCode = null,
		bool    $primary = false
	) {
		$this->addressId = $addressId;
		$this->name = $name;
		$this->street = $street;
		$this->city = $city;
		$this->state = $state;
		$this->country = $country;
		$this->postalCode = $postalCode;
		$this->primary = $primary;
	}

	public function getAddressId(): string {
		return $this->addressId;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getStreet(): ?string {
		return $this->street;
	}

	public function getCity(): ?string {
		return $this->city;
	}

	public function getState(): ?string {
		return $this->state;
	}

	public function getCountry(): ?string {
		return $this->country;
	}

	public function getPostalCode(): ?string {
		return $this->postalCode;
	}

	public function isPrimary(): bool {
		return $this->primary;
	}

}