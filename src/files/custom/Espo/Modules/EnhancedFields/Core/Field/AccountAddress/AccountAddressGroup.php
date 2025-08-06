<?php

namespace Espo\Modules\EnhancedFields\Core\Field\AccountAddress;

use Countable;
use Iterator;
use RuntimeException;

/**
 * Represents a collection of account addresses for an entity.
 * Manages multiple addresses with one primary address.
 */
class AccountAddressGroup implements Countable, Iterator {
	/** @var AccountAddress[] */
	private array $addressList = [];

	private int $position = 0;

	/**
	 * @param AccountAddress[] $addressList
	 */
	public function __construct(array $addressList = []) {
		$primaryCount = 0;

		foreach ($addressList as $address) {
			if (!$address instanceof AccountAddress) {
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
	 * Get the primary account address.
	 *
	 * @return AccountAddress|null
	 */
	public function getPrimary(): ?AccountAddress {
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
	 * @return AccountAddress[]
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
	 * @return AccountAddress[]
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
	 * @return AccountAddress|null
	 */
	public function getByAddressId(string $addressId): ?AccountAddress {
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

	public function current(): AccountAddress {
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