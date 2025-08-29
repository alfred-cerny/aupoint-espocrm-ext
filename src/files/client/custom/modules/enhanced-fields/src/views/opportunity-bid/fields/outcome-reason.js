define(['views/fields/text'], (Dep) => {
		class OutcomeReasonField extends Dep {
			setup() {
				super.setup();

				this.listenTo(this.model, 'change:status', () => {
					if (!this.model.get('status') || this.model.get(this.name)) {
						return;
					}
					this.openOutcomeReasonModal();
				});
			}

			openOutcomeReasonModal() {
				this.createView('outcomeReasonModal', 'enhanced-fields:views/opportunity-bid/modals/outcome-reason', {
					scope: this.model.name,
					model: this.model,
					attributes: this.model.attributes,
					layoutName: 'detailOutcomeReason'
				}).then((modalView) => {
					modalView.render();
					modalView.listenTo(modalView, 'after:edit', () => {
						const model = modalView.model;
						this.model.set(this.name, model.get(this.name));
						modalView.close();
					});
				});
			}
		}

		return OutcomeReasonField;
	}
);