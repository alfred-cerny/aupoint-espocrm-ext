define(['views/fields/link-multiple'], (Dep) => {
		class BidsField extends Dep {
			detailTemplate = 'enhanced-fields:account/fields/bids/detail';

			relationTypeFieldName = 'relationType';

			setup() {
				super.setup();
				this.listenTo(this.model, 'change:' + this.relationTypeFieldName, () => {
					this.reRender();
				});
			}

			data() {
				const data = super.data();
				const bidsData = this.model.get('opportunityBidsData');

				if (bidsData) {

					const relationType = this.getRelationType();
					data.bidsData = Object.values(bidsData)
						.filter(bid => bid.relation === relationType)
						.map(bid => {
							if (bid.relation === 'Partner') {
								const {amount, ...bidWithoutAmount} = bid;
								return bidWithoutAmount;
							}
							bid.partnershipNature = null;
							
							return bid;
						});
					data.bidsDataCount = Object.keys(data.bidsData).length;
				}

				return data;
			}

			getRelationType() {
				return this.model.get(this.relationTypeFieldName);
			}
		}

		return BidsField;
	}
);