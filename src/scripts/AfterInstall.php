<?php

use Espo\Core\Container;
use Espo\Core\Utils\Log;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Expression as Expr;

class AfterInstall {
	protected Container $container;
	protected EntityManager $entityManager;
	protected Log $log;

	protected array $fieldsToMigrate = [
		Account::ENTITY_TYPE => [
			"cLanguage" => "preferredLanguage",
			"cRegistrationNumber" => "registrationNumber",
			"cSecteurActivite" => "sector",
			"cTypeAutre" => "type",
			"type" => "type",
			"cDevise" => "currency",
			"cTaxesApplicables" => "applicableTaxes",
			"cTermesPaiement" => "paymentTerms",
			"cNiveauRisque" => "riskLevel",
			"cTailleOrganisation" => "organizationSize",
			"cMarcheActuel" => "currentMarket",
			"cPerspectiveCroissance" => "growthPerspective",
			"cComptesPointContact" => "accountsPointContact",
			"cNbAnneesRelation" => "yearsOfRelationship",
			"cCompteProjetsTotal" => "accountProjectsTotal",
			"cCompteProjetsValeurtotale" => "accountProjectsTotalValue",
			"cCompteProjetsTypique" => "accountProjectsTypical",
			"cRelationAppreciation" => "relationshipAppreciation",
			"cRelationDescription" => "relationshipDescription",
			"cPotentielNiveau" => "potentialLevel",
			"cPotentielCibles" => "potentialTargets",
			"cAccountStatut" => "accountStatus",
			"cStatutDetail" => "statusDetail",
			"cStatutNotes" => "statusNotes"
		],
		Opportunity::ENTITY_TYPE => [
			"cCompteProjetsNombre" => "accountProjectsCount",
			"cComplementaryProgram" => "complementaryProgram",
			"cConstructionkickoffdate" => "constructionKickoffDate",
			"cDisqualificationChecklist" => "disqualificationChecklist",
			"cAmountInitial" => "initialAmount",
			"cLostReason" => "lostReason",
			"cOdsLeader" => "odsLeader",
			"cCapacitProductionOds" => "odsProductionCapacity",
			"cRisquesOperationnels" => "operationalRisks",
			"cPartnerAuthorizationStatus" => "partnerAuthorizationStatus",
			"cPartners" => "partners",
			"cProbabilite" => "probability",
			"cProjectActivities" => "projectActivities",
			"cProjectGlobalValue" => "projectGlobalValue",
			"cProjectInfantCount" => "projectInfantCount",
			"cProjectLocation" => "projectLocation",
			"cProjectScope" => "projectScope",
			"cProjectSeatCount" => "projectSeatCount",
			"cProjectSector" => "projectSector",
			"cProjectSurface" => "projectSurface",
			"cProjectType" => "projectType",
			"cProjectUnitQt" => "projectUnitQuantity",
			"cQualificationChecklist" => "qualificationChecklist",
			"cResultObservations" => "resultObservations",
			"cSelfWithdrawReason" => "selfWithdrawReason",
			"stage" => "stage",
			"cEvaluationStrategique" => "strategicEvaluation",
			"cValeursEtiques" => "ethicalValues",
			"cValeursStratgiques" => "strategicValues",
			"cTenderQualificationMode" => "tenderQualificationMode",
			"cTenderQuestionsDeadline" => "tenderQuestionsDeadline",
			"cTenderSubmissionDeadline" => "tenderSubmissionDeadline",
			"cTenderSubmissionFormat" => "tenderSubmissionFormat",
			"cTenderType" => "tenderType",
			"cUtmcampaign" => "utmCampaign",
			"cNbAnneesRelation" => "yearsOfRelationship"
		],
		Contact::ENTITY_TYPE => [
			"cLanguage" => "preferredLanguage",
			"cOPTINConsent" => "optinConsent",
			"cOPTINChannels" => "optinChannels",
			"cOPTINQuote" => "optinQuote",
			"cOPTINSource" => "optinSource",
			"cOPTINTimestamp" => "optinTimestamp",
			"cOPTINUnsubscribe" => "optinUnsubscribe",
			"cTitle" => "title",
			"cExpertise" => "expertise",
			"cFormationAcadmique" => "academicBackground",
			"cNbAnneesRelation" => "yearsOfRelationshipWithCompany",
			"cNbAnneesRelationindividuel" => "yearsOfIndividualRelationship",
			"cRelationAppreciationIndividuel" => "individualRelationshipAppreciation",
			"cRelationDescriptionIndividuel" => "individualRelationshipDescription"
		]
	];
	protected array $oldFieldValueMapping = [
		Account::ENTITY_TYPE => [
			"cLanguage" => [
				"english" => "English"
			],
			"cSecteurActivite" => [
				"industriel" => "Industrial",
				"infrastructure" => "Infrastructure",
				"immobilier" => "RealEstate",
				"gestionTerritoire" => "TerritoryManagement",
				"institutionMunicipale" => "MunicipalInstitution",
				"santeServicesociaux" => "HealthSocialService",
				"institutionEnseignementrecherche" => "TeachingResearchInstitution",
				"educationPrimairesecondaire" => "PrimarySecondaryEducation",
				"petiteEnfance" => "EarlyChildhood",
				"commerceServices" => "CommerceServices",
				"tourisme" => "Tourism",
				"culturePatrimoine" => "CultureHeritage",
				"religieuxSpirituel" => "ReligiousSpiritual"
			],
			"cTypeAutre" => [
				"client" => "Client",
				"prospect" => "Prospect",
				"partenaire" => "Partner",
				"competiteur" => "Competitor",
				"autre" => "Other"
			],
			"cDevise" => [
				"cdn" => "CAD",
				"usd" => "USD",
				"eur" => "EUR"
			],
			"cTaxesApplicables" => [
				"taxesQuebec" => "QuebecTaxes",
				"taxesOntario" => "OntarioTaxes",
				"taxesBc" => "BcTaxes",
				"taxesAtlantique" => "AtlanticTaxes",
				"taxesManitoba" => "ManitobaTaxes",
				"taxesSaskatchewan" => "SaskatchewanTaxes",
				"horsCanada" => "OutsideCanada",
				"autresCas" => "OtherCases"
			],
			"cTermesPaiement" => [
				"surReception" => "On Receipt",
				"15jours" => "Fifteen Days",
				"30jours" => "Thirty Days",
				"45jours" => "FortyFive Days",
				"60jours" => "Sixty Days",
				"90jours" => "Ninety Days"
			],
			"cNiveauRisque" => [
				"1" => "One",
				"2" => "Two",
				"3" => "Three",
				"4" => "Four",
				"5" => "Five"
			],
			"cTailleOrganisation" => [
				"micro" => "Micro",
				"petite" => "Small",
				"moyenne" => "Medium",
				"grande" => "Large"
			],
			"cMarcheActuel" => [
				"local" => "Local",
				"regional" => "Regional",
				"provincial" => "Provincial",
				"national" => "National",
				"international" => "International"
			],
			"cPerspectiveCroissance" => [
				"limitee" => "Limited",
				"moyenne" => "Average",
				"elevee" => "High"
			],
			"cRelationAppreciation" => [
				"excellente" => "Excellent",
				"bonne" => "Good",
				"moyenne" => "Average",
				"mauvaise" => "Bad",
				"execrable" => "Terrible"
			],
			"cPotentielNiveau" => [
				"limite" => "Limited",
				"moyenne" => "Average",
				"eleve" => "High"
			],
			"cStatutDetail" => [
				"non-converti" => "Not Converted",
				"perdu" => "Lost",
				"ancien-client" => "Former Client",
				"ancien-partenaire" => "Former Partner",
				"vendu" => "Sold",
				"fusionne" => "Merged",
				"fermé" => "Closed",
				"sortie-prevue" => "Planned Exit"
			],
			"type" => [
				"prospect" => "Prospect",
				"partenaire" => "Partner",
				"competiteur" => "Competitor",
				"client" => "Client"
			]
		],
		Opportunity::ENTITY_TYPE => [
			"stage" => [
				"Analyse AO" => "Prospecting",
				"Acquisition et Analyse (TDR-projet)" => "Qualification",
				"ODS" => "Proposal",
				"Déposée - en attente (client) " => "Negotiation",
				"Gagné - Projet Créé" => "Closed Won",
				"Perdu - ODS refusée" => "Closed Lost",
				"Annulation" => "Cancellation",
				"Non-Participation" => "Non-Participation"
			],
			"cPartnerAuthorizationStatus" => [
				"Autorisé" => "Authorized",
				"Non-autorisé" => "Not Authorized",
				"Non-applicable" => "Not Applicable"
			],
			"cPartners" => [
				"GLCRM" => "GLCRM",
				"LG4 Architecture inc." => "LG4 Architecture Inc",
				"J.Alejandro Lopez Architecture" => "J Alejandro Lopez Architecture",
				"Patriarche" => "Patriarche",
				"Onico" => "Onico",
				"PAR Conseils" => "PAR Conseils"
			],
			"cTenderType" => [
				"Publique" => "Public",
				"Sur invitation" => "Invitation Only",
				"Gré à Gré" => "Direct Contract"
			],
			"cTenderQualificationMode" => [
				"Qualité" => "Quality",
				"Qualité et prix" => "Quality And Price",
				"Prix" => "Price"
			],
			"cTenderSubmissionFormat" => [
				"Électronique" => "Electronic",
				"Papier" => "Paper"
			],
			"cProjectScope" => [
				"Tous" => "All Required",
				"Analyse d’opportunité" => "Feasibility Study",
				"Mise en valeur de site" => "Site Development Analysis",
				"Analyse d’implantation" => "Site Planning",
				"Plan d’ensemble" => "Master Plan",
				"Design urbain pour nouveau développement" => "Urban Design For New Development",
				"Nouvelle(s) construction(s)" => "New Construction",
				"Agrandissement " => "Expansion",
				"Réaménagement " => "Redevelopment",
				"Aménagement " => "Space Planning",
				"Optimisation des espaces" => "Space Optimization",
				"Design d’intérieur" => "Interior Design",
				"Mise aux normes" => "Code Compliance",
				"Réfection" => "Renovation",
				"Expertise associée à une problématique" => "Expert Consulting On Specific Issues"
			],
			"cProjectSector" => [
				"Publique, Intitutionnel " => "Public Institutional",
				"Privé" => "Private"
			],
			"cProjectType" => [
				"Multi-résidentiel " => "Multi Residential",
				"Industriel " => "Industrial",
				"Commercial " => "Commercial",
				"CPE" => "Childcare Facilities"
			],
			"cComplementaryProgram" => [
				"Salle multifonctionnelle " => "Multipurpose Room",
				"Aménagement ou réaménagement d’un vestiaire" => "Locker Room Layout Or Remodeling",
				"Cafétéria" => "Cafeteria",
				"Aménagement d’une cour extérieure avec jeux" => "Outdoor Playground Area Design",
				"NA" => "Not Applicable"
			],
			"cQualificationChecklist" => [
				"Référence " => "Reference",
				"Propriétaire du site" => "Site Owner",
				"Connaissance du milieu de la construction " => "Knowledge Of Construction Industry",
				"Projet relativement concret - besoin défini" => "Concrete Project Defined Need",
				"Financement-budget disponible" => "Funding Budget Available",
				"Position de décision du point de contact" => "Decision Making Authority Of Contact"
			],
			"cDisqualificationChecklist" => [
				"Demande de l'expertise gratuite" => "Brain Picking",
				"Pas de clareté" => "Lack Of Clarity",
				"Faible connaissance de la construction ou peu de dedication" => "Limited Construction Knowledge Or Low Dedication",
				"Demande hors étique " => "Unethical Request",
				"Segment non pertinent" => "Irrelevant Segment",
				"Risque statistique: selon le background du client " => "Statistical Risk Based On Client Background"
			],
			"cSelfWithdrawReason" => [
				"Adéquation du portfolio" => "Portfolio Fit",
				"TDR trop complexes" => "Too Complex"
			],
			"cLostReason" => [
				"Prix" => "Price",
				"Qualité technique" => "Technical Quality",
				"ODS non conforme" => "Conformity"
			],
			"cCapacitProductionOds" => [
				"GO" => "Go",
				"NO-GO" => "No Go"
			],
			"cEvaluationStrategique" => [
				"GO" => "Go",
				"NO GO" => "No Go"
			],
			"cProbabilite" => [
				"GO" => "Go",
				"NO-GO" => "No Go"
			],
			"cRisquesOperationnels" => [
				"Clarté des besoins / du projet" => "Project Needs Clarity",
				"Clarté du contexte d'intervantion" => "Context Clarity",
				"Capacité technique / expertise requise" => "Technical Capacity Expertise",
				"Calendrier de réalisation réaliste" => "Realistic Schedule",
				"Budget réaliste" => "Realistic Budget",
				"Capacité de paiement favorable" => "Payment Capacity",
				"Financement Disponible" => "Funding Available",
				"Références positives du client" => "Positive References",
				"Contrôle ou propriété du site garanti" => "Site Control",
				"Capacité décisionnelle du contact" => "Decision Making Capacity"
			],
			"cValeursEtiques" => [
				"Projet et fonctions prévues en accord à nos valeurs" => "Project Aligns With Values",
				"Projet et Client en accord avec les principes du DD" => "Project Aligns With Sustainable Development",
				"Historique / réputation du Client favorable" => "Favorable History",
				"Obtention de certifications environnementale et/ou sociale ciblées  (LEED, Well, etc.)" => "Environmental Certification Achievement",
				"Compétences professionnelles respectées" => "Professional Skills Respected"
			],
			"cValeursStratgiques" => [
				"Portfolio correspondant" => "Corresponding Portfolio",
				"En accord avec les objectifs de développement de la firme" => "Objectives Alignment",
				"Capacité de mobilisation des compétences requises" => "Required Skills Mobilization Capacity",
				"Valorisation de la créativité pour la création d'espaces uniques favorisant la qualité de vie" => "Creativity Valorization",
				"Intégration positive dans le milieu et le cadre bâti" => "Positive Integration"
			]
		],
		Contact::ENTITY_TYPE => [
			"cLanguage" => [
				"French" => "French",
				"English" => "English"
			],
			"cOPTINChannels" => [
				"email" => "email",
				"sms" => "sms",
				"phone" => "phone"
			],
			"cRelationAppreciationIndividuel" => [
				"excellente" => "Excellent",
				"bonne" => "Good",
				"moyenne" => "Average",
				"mauvaise" => "Bad",
				"mediocre" => "Mediocre"
			]
		]
	];

	public function run($container): void {
		$this->container = $container;

		$this->loadDependencies();
		$this->migrateEntitiesData();
	}

	protected function migrateEntitiesData(): void {
		$batchSize = 1000;

		foreach ($this->fieldsToMigrate as $entityType => $fieldsToMigrate) {
			$entity = $this->entityManager->getNewEntity($entityType);
			foreach ($fieldsToMigrate as $oldFieldName => $newFieldName) {
				if ($oldFieldName === $newFieldName || !$entity->has($oldFieldName)) {
					continue;
				}
				$valueMapping = $this->oldFieldValueMapping[$entityType][$oldFieldName] ?? [];
				if (!empty($valueMapping)) {
					$switchArgs = [];
					foreach ($valueMapping as $oldValue => $newValue) {
						$switchArgs[] = Expr::equal(Expr::column($oldFieldName), $oldValue);
						$switchArgs[] = $newValue;
					}
					$switchArgs[] = Expr::column($oldFieldName);

					$updateSet = [
						$newFieldName => Expr::switch(...$switchArgs)
					];
				} else {
					$updateSet = [
						$newFieldName => Expr::create($oldFieldName)
					];
				}

				do {
					$updateBuilder = $this->entityManager
						->getQueryBuilder()
						->update()
						->in($entityType)
						->set($updateSet)
						->where([
							"$oldFieldName!=" => null,
							$newFieldName => null
						])
						->limit($batchSize);
					$result = $this->entityManager
						->getQueryExecutor()
						->execute($updateBuilder->build());
					$affectedRows = $result->rowCount();

					$affectedRows > 0 && $this->log->debug("Migrated batch of {$affectedRows} records for {$entityType}.{$oldFieldName} -> {$newFieldName}");
				} while ($affectedRows > 0);
			}
		}
	}

	protected function loadDependencies(): void {
		$this->entityManager = $this->container->getByClass(EntityManager::class);
		$this->log = $this->container->getByClass(Log::class);
	}

	protected function clearCache(): void {
		try {
			$this->container->get('dataManager')->clearCache();
		} catch (\Exception $e) {
		}
	}

}
