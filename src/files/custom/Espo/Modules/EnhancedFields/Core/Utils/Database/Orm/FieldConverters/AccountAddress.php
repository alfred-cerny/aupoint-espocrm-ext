<?php

namespace Espo\Modules\EnhancedFields\Core\Utils\Database\Orm\FieldConverters;

use Espo\Core\ORM\Defs\AttributeParam;
use Espo\Core\Utils\Database\Orm\FieldConverter;
use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\Core\Utils\Database\Orm\Defs\EntityDefs;
use Espo\Core\Utils\Database\Orm\Defs\RelationDefs;
use Espo\Modules\EnhancedFields\Entities\AccountAddress as AccountAddressEntity;
use Espo\ORM\Defs\FieldDefs;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;

/**
 * Converts AccountAddress field type to appropriate database schema.
 * Creates many-to-many relationship tables and defines ORM relationships.
 */
class AccountAddress implements FieldConverter {
	private const COLUMN_ENTITY_TYPE_LENGTH = 100;

	public function convert(FieldDefs $fieldDefs, string $entityType): EntityDefs {
		$name = $fieldDefs->getName();

		$foreignJoinAlias = "$name$entityType{alias}Foreign";
		$foreignJoinMiddleAlias = "$name$entityType{alias}ForeignMiddle";

		$accountAddressDefs = AttributeDefs
			::create($name)
			->withType(AttributeType::VARCHAR)
			->withParamsMerged(
				$this->getAccountAddressParams($entityType, $foreignJoinAlias, $foreignJoinMiddleAlias)
			);

		$dataDefs = AttributeDefs
			::create($name . 'Data')
			->withType(AttributeType::JSON_ARRAY)
			->withNotStorable()
			->withParamsMerged([
				AttributeParam::NOT_EXPORTABLE => true,
				'isAccountAddressData' => true,
				'field' => $name,
			]);

		$relationDefs = RelationDefs
			::create('accountAddresses')
			->withType(RelationType::MANY_MANY)
			->withForeignEntityType(AccountAddressEntity::ENTITY_TYPE)
			->withRelationshipName('entityAccountAddress')
			->withMidKeys('entityId', 'accountAddressId')
			->withConditions(['entityType' => $entityType])
			->withAdditionalColumn(
				AttributeDefs
					::create('entityType')
					->withType(AttributeType::VARCHAR)
					->withLength(self::COLUMN_ENTITY_TYPE_LENGTH)
			)
			->withAdditionalColumn(
				AttributeDefs
					::create('primary')
					->withType(AttributeType::BOOL)
					->withDefault(false)
			);

		return EntityDefs::create()
			->withAttribute($accountAddressDefs)
			->withAttribute($dataDefs)
			->withRelation($relationDefs);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function getAccountAddressParams(
		string $entityType,
		string $foreignJoinAlias,
		string $foreignJoinMiddleAlias,
	): array {

		return [
			'select' => [
				"select" => "accountAddresses.name",
				'leftJoins' => [['accountAddresses', 'accountAddresses', ['primary' => true]]],
			],
			'selectForeign' => [
				"select" => "$foreignJoinAlias.name",
				'leftJoins' => [
					[
						'EntityAccountAddress',
						$foreignJoinMiddleAlias,
						[
							"$foreignJoinMiddleAlias.entityId:" => "{alias}.id",
							"$foreignJoinMiddleAlias.primary" => true,
							"$foreignJoinMiddleAlias.deleted" => false,
						]
					],
					[
						AccountAddressEntity::ENTITY_TYPE,
						$foreignJoinAlias,
						[
							"$foreignJoinAlias.id:" => "$foreignJoinMiddleAlias.accountAddressId",
							"$foreignJoinAlias.deleted" => false,
						]
					]
				],
			],
			'fieldType' => 'AccountAddress',
			'where' => [
				'LIKE' => [
					'whereClause' => [
						'id=s' => [
							'from' => 'EntityAccountAddress',
							'select' => ['entityId'],
							'joins' => [
								[
									'accountAddress',
									'accountAddress',
									[
										'accountAddress.id:' => 'accountAddressId',
										'accountAddress.deleted' => false,
									],
								],
							],
							'whereClause' => [
								Attribute::DELETED => false,
								'entityType' => $entityType,
								'accountAddress.name*' => '{value}',
							],
						],
					],
				],
				'NOT LIKE' => [
					'whereClause' => [
						'id!=s' => [
							'from' => 'EntityAccountAddress',
							'select' => ['entityId'],
							'joins' => [
								[
									'accountAddress',
									'accountAddress',
									[
										'accountAddress.id:' => 'accountAddressId',
										'accountAddress.deleted' => false,
									],
								],
							],
							'whereClause' => [
								Attribute::DELETED => false,
								'entityType' => $entityType,
								'accountAddress.name*' => '{value}',
							],
						],
					],
				],
				'=' => [
					'leftJoins' => [['accountAddresses', 'accountAddressesMultiple']],
					'whereClause' => [
						'accountAddressesMultiple.name=' => '{value}',
					]
				],
				'<>' => [
					'whereClause' => [
						'id!=s' => [
							'from' => 'EntityAccountAddress',
							'select' => ['entityId'],
							'joins' => [
								[
									'accountAddress',
									'accountAddress',
									[
										'accountAddress.id:' => 'accountAddressId',
										'accountAddress.deleted' => false,
									],
								],
							],
							'whereClause' => [
								Attribute::DELETED => false,
								'entityType' => $entityType,
								'accountAddress.name' => '{value}',
							],
						],
					],
				],
				'IN' => [
					'whereClause' => [
						'id=s' => [
							'from' => 'EntityAccountAddress',
							'select' => ['entityId'],
							'joins' => [
								[
									'accountAddress',
									'accountAddress',
									[
										'accountAddress.id:' => 'accountAddressId',
										'accountAddress.deleted' => false,
									],
								],
							],
							'whereClause' => [
								Attribute::DELETED => false,
								'entityType' => $entityType,
								'accountAddress.name' => '{value}',
							],
						],
					],
				],
				'NOT IN' => [
					'whereClause' => [
						'id=s' => [
							'from' => 'EntityAccountAddress',
							'select' => ['entityId'],
							'joins' => [
								[
									'accountAddress',
									'accountAddress',
									[
										'accountAddress.id:' => 'accountAddressId',
										'accountAddress.deleted' => false,
									],
								],
							],
							'whereClause' => [
								Attribute::DELETED => false,
								'entityType' => $entityType,
								'accountAddress.name!=' => '{value}',
							],
						],
					],
				],
				'IS NULL' => [
					'leftJoins' => [['accountAddresses', 'accountAddressesMultiple']],
					'whereClause' => [
						'accountAddressesMultiple.name=' => null,
					]
				],
				'IS NOT NULL' => [
					'whereClause' => [
						'id=s' => [
							'from' => 'EntityAccountAddress',
							'select' => ['entityId'],
							'whereClause' => [
								Attribute::DELETED => false,
								'entityType' => $entityType,
							],
						],
					],
				],
			],
			'order' => [
				'order' => [
					['accountAddresses.name', '{direction}'],
				],
				'leftJoins' => [['accountAddresses', 'accountAddresses', ['primary' => true]]],
				'additionalSelect' => ['accountAddresses.name'],
			],
		];
	}

}