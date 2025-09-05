define(['crm:views/contact/fields/accounts'], (Dep) => {
		class AccountsField extends Dep {

			setup() {
				super.setup();

				Object.entries(this.columnsDefs).forEach(([key, def]) => {
					if (
						def.type !== this.COLUMN_TYPE_ENUM ||
						def.options
					) {
						return;
					}
					const fieldDef = this.getMetadata().get(['entityDefs', def.scope || this.foreignScope, 'fields', def.field]) || {};
					const optionsReference = fieldDef.optionsReference;

					if (!optionsReference) {
						return;
					}

					const [refEntityType, refField] = optionsReference.split('.');

					def.styleMap ??= this.getMetadata().get(`entityDefs.${refEntityType}.fields.${refField}.style`) || {};
					def.options = Espo.Utils.clone(this.getMetadata().get(`entityDefs.${refEntityType}.fields.${refField}.options`)) || [];

					this.columnsDefs[key] = def;
				});
			}

			getAttributeList() {
				const list = super.getAttributeList();

				list.push('decisionRole');

				return list;
			}
		}

		return AccountsField;
	}
);