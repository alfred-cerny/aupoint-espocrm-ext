define(['views/fields/multi-enum'], (Dep) => {
		class SubsectorsField extends Dep {

			sectorMapping = {
				"Industrial": [
					"Agriculture, livestock and fishing",
					"Aeronautics",
					"Aerospace",
					"National security",
					"Chemical",
					"Construction materials",
					"Mining and quarrying",
					"Metallurgy"
				],
				"Infrastructure": [
					"Energy",
					"Transport",
					"Telecommunications",
					"Drinking water and sanitation",
					"Drainage"
				],
				"Real Estate (management and development)": [
					"Residential – Single-family",
					"Residential – Multiplex",
					"Commercial",
					"Industrial – manufacturing",
					"Warehousing and logistics",
					"Data center",
					"SQI and other real estate managers"
				],
				"Territory Management": [
					"ZEC",
					"ZIP (Watersheds)",
					"Regional park"
				],
				"Municipal Institution": [
					"City / Municipality",
					"MRC",
					"Local development center"
				],
				"Health and Social Services": [
					"CISSS / CIUSSS",
					"Residence for seniors (RPA)",
					"Specialized support establishment",
					"Clinic / medical center",
					"Medical laboratory"
				],
				"Educational and Research Institution": [
					"University",
					"Cégep",
					"Professional and technical training school",
					"INRS"
				],
				"Primary and Secondary Education": [
					"School service centers (CSS)",
					"School board (anglophone)",
					"Private schools"
				],
				"Early Childhood": [
					"Early childhood center (CPE)",
					"Daycare",
					"Kindergarten",
					"Pre-school"
				],
				"Commerce and Services": [
					"Restaurant",
					"Retail trade",
					"Garages and mechanical workshop"
				],
				"Tourism": [
					"Sépaq",
					"Hotel industry",
					"Convention and exhibition center",
					"Outdoor establishment",
					"Outfitter",
					"Camping",
					"Summer or thematic camps",
					"Regional tourist association"
				],
				"Culture and Heritage": [
					"Museum",
					"Interpretation center",
					"Theaters and performance hall",
					"Production establishment (studio, etc.)"
				],
				"Religious and Spiritual": [
					"Diocese / Fabrique (church, presbytery, etc.)",
					"Spiritual retreat center"
				]
			};

			setup() {
				super.setup();
				this.listenTo(this.model, 'change:sector', () => {
					this.model.set(this.name, null);
					this.setupOptions();

					if (this.params.isSorted && this.translatedOptions) {
						this.params.options = Espo.Utils.clone(this.params.options) || [];

						this.params.options = this.params.options.sort((v1, v2) => {
							return (this.translatedOptions[v1] || v1)
								.localeCompare(this.translatedOptions[v2] || v2);
						});
					}

					this.reRender();
				});
			}

			setupOptions() {
				const sector = this.getSector();

				if (!sector || !this.sectorMapping[sector]) {
					this.params.options = [];
					return;
				}

				this.params.options = this.sectorMapping[sector];
				this.setupTranslatedOptions();
			}

			setupTranslatedOptions() {
				this.translatedOptions ??= {};

				this.params.options.forEach(option => {
					this.translatedOptions[option] ??= this.getLanguage().translateOption(option, 'subsectors', 'Account');
				});
			}

			getSector() {
				return this.model.get('sector');
			}
		}

		return SubsectorsField;
	}
);