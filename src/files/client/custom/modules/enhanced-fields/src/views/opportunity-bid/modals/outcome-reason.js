define(['views/modals/edit'], (Dep) => {
		class OutcomeReasonModal extends Dep {
			actionSave(data = {}) {
				//Espo.Ui.notify(this.translate('Outcome reason updated'), 'success', 4500, {suppress: true});
				this.trigger('after:edit', this.model, {bypassClose: data.bypassClose});
			}
		}

		return OutcomeReasonModal;
	}
);