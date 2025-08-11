define([], () => {
	class RelationHandler {
		buttons = [];
		relationFieldName = 'relation';

		constructor(view) {
			this.view = view;
			this.model = view.model;
		}

		process() {
			this.view.listenTo(this.model, 'change:' + this.relationFieldName, () => {
				this.reloadButtons();
				this.reloadHeader();
			});
			this.reloadButtons();
			this.reloadHeader();
		}

		reloadHeader() {
			this.view?.getHeaderView()?.reRender();
		}

		reloadButtons() {
			const relationsNames = this.getRelationsNames();

			// Remove buttons that no longer exist in relationsNames
			this.buttons = this.buttons.filter(buttonName => {
				const exists = relationsNames.includes(buttonName);
				if (!exists) {
					this.view.removeMenuItem(buttonName, true);
				}
				return exists;
			});

			// Add missing buttons
			relationsNames.forEach((relationName) => {
				if (!this.buttons.includes(relationName)) {
					this.buttons.push(relationName);
					this.view.addMenuItem('buttons', {
						name: relationName,
						text: this.view.getLanguage().translateOption(relationName, this.relationFieldName, 'Account'),
						iconClass: 'fas fa-tools fa-sm',
					}, true, true);
				}
			});
		}

		getRelationsNames() {
			const relationsNames = this.model.get(this.relationFieldName);
			if (!Array.isArray(relationsNames)) {
				return [];
			}
			return relationsNames;
		}
	}

	return RelationHandler;
});