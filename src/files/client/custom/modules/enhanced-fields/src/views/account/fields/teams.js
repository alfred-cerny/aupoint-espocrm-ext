define(['views/fields/teams'], (Dep) => {
		class TeamsField extends Dep {
			preferredPrefix = 'Off_';

			getSelectFilters() {
				if (typeof this.preferredPrefix !== 'string') {
					return null;
				}

				return {
					name: {
						type: 'startsWith',
						attribute: 'name',
						value: this.preferredPrefix
					}
				};
			}
		}

		return TeamsField;
	}
);