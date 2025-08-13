define(['views/fields/link-multiple'], (Dep) => {
		class BidsField extends Dep {
			detailTemplate = 'enhanced-fields:fields/bids/detail';
			editTemplate = 'enhanced-fields:fields/bids/edit';
			relationTypeFieldName = 'relationType';
			relationClassNameMapping = {
				Partner: "label-info",
				Competitor: "label-warning"
			};
			listSmall = null;

			setup() {
				super.setup();
				this.listenTo(this.model, 'change:' + this.relationTypeFieldName, () => {
					this.reRender();
				});
			}

			afterRender() {
				super.afterRender();
				if (!this.isEditMode()) {
					return;
				}

				const bidsData = this.getBidsData();
				debugger;
				if (!bidsData) {
					return;
				}
				this.fetchAvailableBidFields().then(listSmall => {
					this.getModelFactory().create('OpportunityBid', model => {
						Object.entries(bidsData).forEach(([id, bid]) => {
							listSmall.forEach((field) => {
								const fieldName = field.name;
								const fieldValue = bidsData[fieldName] || null;
								const composedFieldName = this.getComposedFieldName(fieldName, id);
								model.set(composedFieldName, fieldValue);

								const type = model.getFieldType(fieldName) || 'base';
								const viewName = model.getFieldParam(fieldName, 'view') ||
									this.getFieldManager().getViewName(type);
								this.createView(composedFieldName, viewName, {
									model,
									mode: this.MODE_EDIT,
									name: composedFieldName,
									selector: `div[data-name="${composedFieldName}"]`,
									defs: {
										params: {
											...(model?.defs?.fields[fieldName] || {})
										}
									}
								}).then((view) => {
									view.render();
								});
							});
						});
					});
				});
			}

			fetchFieldData() {
				const results = [];

				this.$el.find('.opportunity-bids-content .opportunity-bid-item').each((i, itemEl) => {
					const $item = $(itemEl);
					const bidId = $item.attr('data-bid-id');

					this.fetchAvailableBidFields().then((listSmall) => {
						const bidData = {};
						listSmall.forEach((field) => {
							const fieldName = field.name;
							bidData[fieldName] = $item.find(`div[name="${this.getComposedFieldName(fieldName, bidId)}"]`).text();
						});
						debugger;
						results.push({bidId, data: bidData});
					});
				});

				return results;
			}

			fetch() {
				const data = super.fetch();
				data[this.getBidsFieldName() + 'Data'] = this.fetchFieldData();
				debugger;
				return data;
			}

			fetchAvailableBidFields() {
				return new Promise(resolve => {
					if (this.listSmall) {
						resolve(this.listSmall);
						return;
					}
					this.getHelper().layoutManager.get('OpportunityBid', 'listSmall', (list) => {
						this.listSmall = list;
						resolve(list);
					});
				});
			}

			data() {
				const data = super.data();
				const bidsData = this.getBidsData();

				if (bidsData) {
					const relationType = this.getRelationType();
					data.bidsData = Array.isArray(bidsData) ? bidsData : Object.values(bidsData);
					if (relationType) {
						data.bidsData = data.bidsData.filter(bid => bid.relation === relationType);
					}

					data.bidsData = data.bidsData.map(bid => {
						if (bid.relation === 'Partner') {
							const {amount, ...bidWithoutAmount} = bid;
							return bidWithoutAmount;
						}
						bid.partnershipNature = null;
						bid.className = this.relationClassNameMapping[bid.relation || ''] || null;

						return bid;
					});
					data.bidsDataCount = data.bidsData.length;
				}

				return data;
			}

			getBidsFieldName() {
				if (this.model.name === 'Opportunity') {
					return 'bids';
				}
				return 'opportunityBids';
			}

			getBidsData() {
				return this.model.get(this.getBidsFieldName() + 'Data');
			}

			getRelationType() {
				return this.model.get(this.relationTypeFieldName);
			}

			getComposedFieldName(fieldName, value) {
				return `${fieldName}-${value}`;
			}
		}

		return BidsField;
	}
);