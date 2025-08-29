<?php

namespace Espo\Modules\EnhancedFields\Entities;

use Espo\Core\ORM\Entity;

class LeadCampaign extends Entity {
	public const ENTITY_TYPE = 'LeadCampaign';
	public const DEFAULT_LANGUAGE = 'English';

	public function getEmailTemplateId(?string $language): ?string {
		if (!empty($language) && $language !== self::DEFAULT_LANGUAGE) {
			return $this->get('emailTemplate' . $language . 'Id');
		}

		return $this->get('emailTemplateId');
	}

	public function getFromAddress(?string $language): ?string {
		return $this->get('fromAddress'); //@todo: maybe implement language-based from addresses?
	}

	public function getFromName(): ?string {
		return $this->get('fromName');
	}

}