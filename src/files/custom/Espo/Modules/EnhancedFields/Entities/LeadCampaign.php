<?php

namespace Espo\Modules\EnhancedFields\Entities;

use Espo\Core\ORM\Entity;

class LeadCampaign extends Entity {
	public const ENTITY_TYPE = 'LeadCampaign';
	public const TYPE_EMAIL_CAMPAIGN = 'Email Campaign';
	public const DEFAULT_LANGUAGE = 'English';

	public function getEmailTemplateId(?string $language): ?string {
		if (!empty($language) && $language !== self::DEFAULT_LANGUAGE) {
			return $this->get('emailTemplate' . $language . 'Id');
		}

		return $this->get('emailTemplateId');
	}

}