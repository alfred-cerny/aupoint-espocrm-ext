<?php

namespace Espo\Modules\EnhancedFields\Hooks\Lead;

use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\Mail\EmailSender;
use Espo\Core\Mail\Exceptions\SendingError;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Metadata;
use Espo\Entities\ArrayValue;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\Modules\EnhancedFields\Entities\LeadCampaign;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\Part\Join;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Services\Stream as StreamService;
use Espo\Tools\EmailTemplate\Service as EmailTemplateService;
use Espo\Tools\EmailTemplate\Data as EmailTemplateData;

readonly class SendEmailAfterCreation implements AfterSave {
	public function __construct(
		protected EntityManager        $entityManager,
		protected Metadata             $metadata,
		protected EmailSender          $emailSender,
		protected EmailTemplateService $emailTemplateService,
		protected Config               $config,
		protected Log                  $log,
		protected StreamService        $streamService,
		protected User                 $user,
	) {}

	/**
	 * @throws ForbiddenSilent
	 * @throws NotFound
	 * @throws SendingError
	 */
	public function afterSave(Entity $entity, SaveOptions $options): void {
		if (!$entity->isNew()) {
			return;
		}

		$entityId = $entity->get('id');
		$emailAddress = $entity->get('emailAddress');
		if (empty($emailAddress)) {
			$this->log->debug("Lead(id:$entityId) does not have emailAddress set, not sending informational email.");
			return;
		}
		$outboundEmailAddress = $this->config->get('outboundEmailFromAddress');
		if (empty($outboundEmailAddress)) {
			$this->log->alert('Outbound email address is not configured, not sending information email to Lead after creation.');
			return;
		}
		$leadCampaign = $this->getLeadCampaign($entity);
		if (is_null($leadCampaign)) {
			return;
		}
		$templateId = $leadCampaign->getEmailTemplateId($entity->get('language'));
		if (empty($templateId)) {
			$preferredLanguage = $entity->get('language');
			$this->log->alert("Email template is not set, expected one for language '$preferredLanguage', found none.");
			return;
		}

		$data = EmailTemplateData::create()
			->withEmailAddress($emailAddress)
			->withParent($entity);
		$result = $this->emailTemplateService->process($templateId, $data);
		/** @var Email $email */
		$email = $this->entityManager->createEntity(Email::ENTITY_TYPE, [
			'status' => Email::STATUS_DRAFT,
			'from' => $outboundEmailAddress,
			'to' => $emailAddress,
			'subject' => $result->getSubject(),
			$result->isHtml() ? 'body' : 'bodyPlain' => $result->getBody(),
			'isHtml' => $result->isHtml(),
			'attachmentsIds' => $result->getAttachmentIdList(),
			'assignedUserId' => $this->user->isSystem() ? null : $this->user->getId(),
		]);
		$this->emailSender->send($email);
		$this->entityManager->saveEntity($email);
		$emailId = $email->getId();
		$this->log->info("Lead(id:$entityId) has been sent an informational email(id:$emailId).");
		$this->streamService->noteEmailSent($entity, $email);
		$this->streamService->noteEmailSent($leadCampaign, $email);
	}

	protected function getLeadCampaign(Entity $entity): ?LeadCampaign {
		$campaign = $entity->get('utmCampaign');
		if (empty($campaign)) {
			return null;
		}
		/** @var LeadCampaign|null $leadCampaign */
		$leadCampaign = $this->entityManager
			->getRDBRepository(LeadCampaign::ENTITY_TYPE)
			->where(Cond::and(
				Cond::equal(Expr::column('status'), Expr::value('Active')),
				Cond::equal(Expr::column('type'), Expr::value(LeadCampaign::TYPE_EMAIL_CAMPAIGN)),
			))
			->join(
				Join::createWithTableTarget(ArrayValue::ENTITY_TYPE, 'av')
					->withConditions(Cond::and(
						Cond::equal(Expr::column('av.deleted'), Expr::value(false)),
						Cond::equal(Expr::column('av.entityId'), Expr::column('id')),
						Cond::equal(Expr::column('av.entityType'), Expr::value(LeadCampaign::ENTITY_TYPE)),
						Cond::equal(Expr::column('av.value'), Expr::value((string)$campaign)),
					))
			)
			->findOne();

		return $leadCampaign;
	}

}