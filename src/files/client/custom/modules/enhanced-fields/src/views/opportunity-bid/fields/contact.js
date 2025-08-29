define(['views/fields/link'], (Dep) => {
		class ContactField extends Dep {

			getSelectFilters() {
				const accountId = this.model.get('accountId');
				if (typeof accountId !== 'string') {
					return null;
				}

				const nameHash = {};
				nameHash[accountId] = this.model.get('accountName');

				return {
					accounts: {
						type: 'linkedWith',
						attribute: 'accounts',
						value: [accountId],
						data: {
							type: 'linkedWith',
							nameHash
						}
					}
				};
			}
		}

		return ContactField;
	}
);