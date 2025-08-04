<?php

namespace Espo\Modules\EnhancedFields\Core\Utils\Database\Orm\FieldConverters;

use Espo\Core\ORM\Defs\AttributeParam;
use Espo\Core\Utils\Database\Orm\FieldConverter;
use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\Core\Utils\Database\Orm\Defs\EntityDefs;
use Espo\Core\Utils\Database\Orm\Defs\RelationDefs;
use Espo\Modules\EnhancedFields\Entities\ContactAddress as ContactAddressEntity;
use Espo\ORM\Defs\FieldDefs;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;

/**
 * Converts ContactAddress field type to appropriate database schema.
 * Creates many-to-many relationship tables and defines ORM relationships.
 */
class ContactAddress implements FieldConverter {
	private const COLUMN_ENTITY_TYPE_LENGTH = 100;

	public function convert(FieldDefs $fieldDefs, string $entityType): EntityDefs {
		$name = $fieldDefs->getName();

		$foreignJoinAlias = "$name$entityType{alias}Foreign";
		$foreignJoinMiddleAlias = "$name$entityType{alias}ForeignMiddle";

		$contactAddressDefs = AttributeDefs
			::create($name)
			->withType(AttributeType::VARCHAR)
			->withParamsMerged(
				$this->getContactAddressParams($entityType, $foreignJoinAlias, $foreignJoinMiddleAlias)
			);

		$dataDefs = AttributeDefs
			::create($name . 'Data')
			->withType(AttributeType::JSON_ARRAY)
			->withNotStorable()
			->withParamsMerged([
				AttributeParam::NOT_EXPORTABLE => true,
				'isContactAddressData' => true,
				'field' => $name,
			]);

		$relationDefs = RelationDefs
			::create('contactAddresses')
			->withType(RelationType::MANY_MANY)
			->withForeignEntityType(ContactAddressEntity::ENTITY_TYPE)
			->withRelationshipName('entityContactAddress')
			->withMidKeys('entityId', 'contactAddressId')
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
			->withAttribute($contactAddressDefs)
			->withAttribute($dataDefs)
			->withRelation($relationDefs);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function getContactAddressParams(
		string $entityType,
		string $foreignJoinAlias,
		string $foreignJoinMiddleAlias,
	): array {

		return [
			'select' => [
				"select" => "contactAddresses.name",
				'leftJoins' => [['contactAddresses', 'contactAddresses', ['primary' => true]]],
			],
			'selectForeign' => [
				"select" => "$foreignJoinAlias.name",
				'leftJoins' => [
					[
						'EntityContactAddress',
						$foreignJoinMiddleAlias,
						[
							"$foreignJoinMiddleAlias.entityId:" => "{alias}.id",
							"$foreignJoinMiddleAlias.primary" => true,
							"$foreignJoinMiddleAlias.deleted" => false,
						]
					],
					[
						ContactAddressEntity::ENTITY_TYPE,
						$foreignJoinAlias,
						[
							"$foreignJoinAlias.id:" => "$foreignJoinMiddleAlias.contactAddressId",
							"$foreignJoinAlias.deleted" => false,
						]
					]
				],
			],
			'fieldType' => 'ContactAddress',
			'where' => [
				'LIKE' => [
					'whereClause' => [
						'id=s' => [
							'from' => 'EntityContactAddress',
							'select' => ['entityId'],
							'joins' => [
								[
									'contactAddress',
									'contactAddress',
									[
										'contactAddress.id:' => 'contactAddressId',
										'contactAddress.deleted' => false,
									],
								],
							],
							'whereClause' => [
								Attribute::DELETED => false,
								'entityType' => $entityType,
								'contactAddress.name*' => '{value}',
							],
						],
					],
				],
				'NOT LIKE' => [
					'whereClause' => [
						'id!=s' => [
							'from' => 'EntityContactAddress',
							'select' => ['entityId'],
							'joins' => [
								[
									'contactAddress',
									'contactAddress',
									[
										'contactAddress.id:' => 'contactAddressId',
										'contactAddress.deleted' => false,
									],
								],
							],
							'whereClause' => [
								Attribute::DELETED => false,
								'entityType' => $entityType,
								'contactAddress.name*' => '{value}',
							],
						],
					],
				],
				'=' => [
					'leftJoins' => [['contactAddresses', 'contactAddressesMultiple']],
					'whereClause' => [
						'contactAddressesMultiple.name=' => '{value}',
					]
				],
				'<>' => [
					'whereClause' => [
						'id!=s' => [
							'from' => 'EntityContactAddress',
							'select' => ['entityId'],
							'joins' => [
								[
									'contactAddress',
									'contactAddress',
									[
										'contactAddress.id:' => 'contactAddressId',
										'contactAddress.deleted' => false,
									],
								],
							],
							'whereClause' => [
								Attribute::DELETED => false,
								'entityType' => $entityType,
								'contactAddress.name' => '{value}',
							],
						],
					],
				],
				'IN' => [
					'whereClause' => [
						'id=s' => [
							'from' => 'EntityContactAddress',
							'select' => ['entityId'],
							'joins' => [
								[
									'contactAddress',
									'contactAddress',
									[
										'contactAddress.id:' => 'contactAddressId',
										'contactAddress.deleted' => false,
									],
								],
							],
							'whereClause' => [
								Attribute::DELETED => false,
								'entityType' => $entityType,
								'contactAddress.name' => '{value}',
							],
						],
					],
				],
				'NOT IN' => [
					'whereClause' => [
						'id=s' => [
							'from' => 'EntityContactAddress',
							'select' => ['entityId'],
							'joins' => [
								[
									'contactAddress',
									'contactAddress',
									[
										'contactAddress.id:' => 'contactAddressId',
										'contactAddress.deleted' => false,
									],
								],
							],
							'whereClause' => [
								Attribute::DELETED => false,
								'entityType' => $entityType,
								'contactAddress.name!=' => '{value}',
							],
						],
					],
				],
				'IS NULL' => [
					'leftJoins' => [['contactAddresses', 'contactAddressesMultiple']],
					'whereClause' => [
						'contactAddressesMultiple.name=' => null,
					]
				],
				'IS NOT NULL' => [
					'whereClause' => [
						'id=s' => [
							'from' => 'EntityContactAddress',
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
					['contactAddresses.name', '{direction}'],
				],
				'leftJoins' => [['contactAddresses', 'contactAddresses', ['primary' => true]]],
				'additionalSelect' => ['contactAddresses.name'],
			],
		];
	}

}