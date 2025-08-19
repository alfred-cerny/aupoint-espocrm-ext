<?php

namespace Espo\Modules\EnhancedFields\Api\Lead;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Error\Body as ErrorBody;
use Espo\Core\Utils\Log;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Lead;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

readonly class Create implements Action {
	public function __construct(
		protected EntityManager $entityManager,
		protected Log           $log
	) {}

	public function process(Request $request): Response {
		$data = $request->getParsedBody() ?? throw Error::createWithBody(
			'No request body provided.',
			ErrorBody::create()
				->withMessageTranslation(
					'noBody',
					Lead::ENTITY_TYPE,
				)
				->encode()
		);
		$attributes = $data->attributes ?? throw Error::createWithBody(
			'No attributes provided.',
			ErrorBody::create()
				->withMessageTranslation(
					'noAttributes',
					Lead::ENTITY_TYPE,
				)
				->encode()
		);
		$tm = $this->entityManager->getTransactionManager();
		$tm->start();
		/** @var Lead $lead */
		$lead = $this->entityManager->createEntity(Lead::ENTITY_TYPE, $attributes);

		if (
			(isset($attributes->referredByContact)) &&
			($referredByContactData = $attributes->referredByContact)
		) {
			$contact = $this->createOrFindEntity(
				'Contact',
				$referredByContactData,
				[
					'emailAddress'
				]
			);
			$contact->isNew() && $this->entityManager->saveEntity($contact);
			$this->entityManager
				->getRDBRepository(Lead::ENTITY_TYPE)
				->getRelation($lead, 'referredByContact')
				->relate($contact);

			if (
				(isset($referredByContactData->account)) &&
				($accountData = $referredByContactData->account) &&
				empty($contact->get('accountsIds'))
			) {
				$account = $this->createOrFindEntity(
					'Account',
					$accountData,
					[
						'name'
					]
				);
				$this->entityManager->saveEntity($contact);
				$account->isNew() && $this->entityManager->saveEntity($account);

				$this->entityManager
					->getRDBRepository(Contact::ENTITY_TYPE)
					->getRelation($contact, 'accounts')
					->relate($account);
			}
		}
		if (
			(isset($attributes->referredByUser)) &&
			($referredByUserData = $attributes->referredByUser)
		) {
			$user = $this->findEntity(
				'User',
				$referredByUserData,
				[
					'emailAddress'
				]
			);
			$lead->set('referredByUserId', $user?->getId());
		}
		$this->entityManager->saveEntity($lead);
		$tm->commit();
		$this->log->debug("Created lead via 'Lead/create' endpoint, id: {$lead->getId()}.");
		return ResponseComposer::json([
			'success' => true,
			'data' => $lead->getValueMap()
		]);
	}

	protected function findEntity(string $entityType, array|\stdClass $entityData, array $filterFieldNames): null|Entity {
		$entityData = (array)$entityData;
		return $this->entityManager
			->getRDBRepository($entityType)
			->where('deleted', false)
			->where(
				array_intersect_key($entityData, array_flip($filterFieldNames))
			)
			->findOne();
	}

	protected function createOrFindEntity(string $entityType, array|\stdClass $entityData, array $filterFieldNames): Entity {
		return $this->findEntity($entityType, $entityData, $filterFieldNames)
			?? $this->entityManager->createEntity($entityType, $entityData);
	}

}