<?php

namespace Espo\Modules\EnhancedFields\Core\Field\ContactAddress;

use Countable;
use Iterator;
use RuntimeException;

/**
 * Represents a collection of contact addresses for an entity.
 * Manages multiple addresses with one primary address.
 */
class ContactAddressGroup implements Countable, Iterator {
	/** @var ContactAddress[] */
	private array $addressList = [];

	private int $position = 0;

	/**
	 * @param ContactAddress[] $addressList
	 */
	public function __construct(array $addressList = []) {
		$primaryCount = 0;

		foreach ($addressList as $address) {
			if (!$address instanceof ContactAddress) {
				throw new RuntimeException("Invalid address object provided.");
			}

			if ($address->isPrimary()) {
				$primaryCount++;
			}

			$this->addressList[] = $address;
		}

		if ($primaryCount > 1) {
			throw new RuntimeException("Only one address can be primary.");
		}

		if ($primaryCount === 0 && count($this->addressList) > 0) {
			throw new RuntimeException("At least one address must be primary.");
		}
	}

	/**
	 * Get the primary contact address.
	 *
	 * @return ContactAddress|null
	 */
	public function getPrimary(): ?ContactAddress {
		foreach ($this->addressList as $address) {
			if ($address->isPrimary()) {
				return $address;
			}
		}

		return null;
	}

	/**
	 * Get list of secondary addresses.
	 *
	 * @return ContactAddress[]
	 */
	public function getSecondaryList(): array {
		$list = [];

		foreach ($this->addressList as $address) {
			if (!$address->isPrimary()) {
				$list[] = $address;
			}
		}

		return $list;
	}

	/**
	 * Get all addresses.
	 *
	 * @return ContactAddress[]
	 */
	public function getList(): array {
		return $this->addressList;
	}

	/**
	 * Get list of address IDs.
	 *
	 * @return string[]
	 */
	public function getIdList(): array {
		$idList = [];

		foreach ($this->addressList as $address) {
			$idList[] = $address->getAddressId();
		}

		return $idList;
	}

	/**
	 * Get list of address names.
	 *
	 * @return string[]
	 */
	public function getAddressList(): array {
		$list = [];

		foreach ($this->addressList as $address) {
			$list[] = $address->getName();
		}

		return $list;
	}

	/**
	 * Get address by ID.
	 *
	 * @param string $addressId
	 * @return ContactAddress|null
	 */
	public function getByAddressId(string $addressId): ?ContactAddress {
		foreach ($this->addressList as $address) {
			if ($address->getAddressId() === $addressId) {
				return $address;
			}
		}

		return null;
	}

	public function count(): int {
		return count($this->addressList);
	}

	public function rewind(): void {
		$this->position = 0;
	}

	public function current(): ContactAddress {
		return $this->addressList[$this->position];
	}

	public function key(): int {
		return $this->position;
	}

	public function next(): void {
		$this->position++;
	}

	public function valid(): bool {
		return isset($this->addressList[$this->position]);
	}

}