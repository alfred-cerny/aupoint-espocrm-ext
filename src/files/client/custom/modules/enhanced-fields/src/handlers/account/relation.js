define([], () => {
	class RelationHandler {
		buttons = [];
		relationClassNameMapping = {};

		constructor(view) {
			this.view = view;
			this.model = view.model;
		}

		process() {
			this.relationClassNameMapping = this.view.getMetadata().get("entityDefs.Account.fields.type.style") || {};
			this.view.listenTo(this.model, 'change:type', () => {
				this.reloadButtons();
				this.reloadHeader();
			});
			this.reloadButtons();

			if (this.buttons.length > 0) {
				this.onRelationChange(this.buttons[0]);
			}

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
						text: this.view.getLanguage().translateOption(relationName, 'type', 'Account'),
						iconClass: 'fas fa-tools fa-sm',
						onClick: () => {
							this.onRelationChange(relationName);
						}
					}, true, true);
				}
			});
		}

		onRelationChange(relationName) {
			this.model.set('relationType', relationName);
			this.model.trigger('change:relationType');

			this.view.menu.buttons.forEach((button) => {
				const relationsNames = this.getRelationsNames();
				if (button.name && !relationsNames.includes(button.name)) {
					return;
				}
				if (relationName && button.name === relationName) {
					button.style = this.relationClassNameMapping[relationName] || 'default';
				} else {
					button.style = 'default';
				}
			});
			this.reloadHeader();
		}

		getRelationsNames() {
			const relationsNames = this.model.get('type');
			if (!Array.isArray(relationsNames)) {
				return [];
			}
			return relationsNames;
		}
	}

	return RelationHandler;
});