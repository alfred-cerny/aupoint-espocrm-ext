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
				const promiseToLoadAvailableBidFields = this.loadAvailableBidFields();
				this.listenTo(this.model, 'change:' + this.relationTypeFieldName, () => {
					this.reRender();
				});
				this.addActionHandler('selectLink', () => {
				});
				this.addActionHandler('clearLink', () => {
				});
				this.addActionHandler('selectBid', () => this.selectBid());
				this.addActionHandler('addBid', () => this.addBid());
				this.addActionHandler('removeBid', (e, target) => {
					const index = $(target)?.closest("[data-index]")?.attr("data-index") || null;
					if (index) {
						this.removeBid(index);
					} else {
						console.error(`Invalid index ${index} found.`, target);
					}
				});
				this.wait(promiseToLoadAvailableBidFields);
			}

			selectBid() {
				const data = this.fetch();
				const linkedBidsIds = data[this.name + 'Ids'] || null;
				let filters = {};

				if (linkedBidsIds && linkedBidsIds.length > 0) {
					filters = {
						id: {
							type: 'notIn',
							value: linkedBidsIds,
						}
					};
				}
				this.createView('modal', 'views/modals/select-records', {
					entityType: 'OpportunityBid',
					createButton: true,
					multiple: true,
					filters,
					onSelect: models => {
						models.forEach(model => {
							this.addBid(model.attributes);
						});
					}
				}).then((view) => {
					view.render();
				});
			}

			addBid(bidData = {}) {
				const data = this.fetchFieldData();

				if (!bidData.id) {
					this.listSmall.forEach(field => {
						const fieldName = field.name;
						bidData[fieldName] ??= null;
					});
				} else if (data.some((item) => bidData.id === item.id)) {
					return;
				}
				bidData.index ??= data.length;
				data.push(bidData);
				this.model.set(this.name + 'Data', data, {silent: true});
				this.reRender();
			}

			removeBid(index) {
				const data = this.fetchFieldData();

				if (index < 0 || index >= data.length) {
					console.warn(`Invalid index '${index}'. No bid removed.`);
					return;
				}
				const removedItems = data.splice(index, 1);
				if (removedItems.length > 0) {
					this.model.set(this.name + 'Data', data, {silent: true});
					this.reRender();
				} else {
					console.warn(`No bid removed at index '${index}'.`);
				}
			}

			afterRender() {
				super.afterRender();
				if (!this.isEditMode()) {
					return;
				}

				const bidsData = this.getBidsData();
				if (!bidsData) {
					return;
				}
				this.getModelFactory().create('OpportunityBid', model => {
					bidsData.forEach((bidData, index) => {
						this.listSmall.forEach((field) => {
							const fieldName = field.name;
							const fieldValue = bidData[fieldName] || null;
							const fieldType = model.getFieldType(fieldName);
							const composedFieldName = this.getComposedFieldName(fieldName, index);
							const additionalFields = fieldType === 'link'
								? ['Id', 'Name']
								: fieldType === 'linkMultiple'
									? ['Ids', 'Names']
									: [];
							model.set(composedFieldName, fieldValue);
							additionalFields.forEach(additionalFieldSuffix => {
								const composedFieldNameWithSuffix = composedFieldName + additionalFieldSuffix;
								const fieldNameWithSuffix = fieldName + additionalFieldSuffix;
								model.set(composedFieldNameWithSuffix, bidData[fieldNameWithSuffix] || null);
							});

							const type = model.getFieldType(fieldName) || 'base';
							const viewName = model.getFieldParam(fieldName, 'view') ||
								this.getFieldManager().getViewName(type);
							this.createView(composedFieldName, viewName, {
								model,
								mode: this.MODE_EDIT,
								name: composedFieldName,
								selector: `div[data-name="${composedFieldName}"]`,
								foreignScope: model?.defs?.links[fieldName]?.entity || null,
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
			}

			fetchFieldData() {
				const results = [];

				this.$el.find('.opportunity-bids-content .opportunity-bid-item').each((index, itemEl) => {
					const $item = $(itemEl);
					const bidId = $item.attr('data-bid-id');

					const itemData = {
						...(bidId && {id: bidId}),
						index,
						...Object.fromEntries(
							this.listSmall.flatMap(field => {
								const {name: fieldName} = field;
								const fieldType = this.getMetadata().data.entityDefs.OpportunityBid.fields[fieldName].type;
								const additionalFields = fieldType === 'link'
									? ['Id', 'Name']
									: fieldType === 'linkMultiple'
										? ['Ids', 'Names']
										: [];
								const composedFieldName = this.getComposedFieldName(fieldName, index);
								const fieldFetchedData = this.getView(composedFieldName)?.fetch();
								const data = additionalFields.map(additionalFieldSuffix => {
									const composedFieldNameWithSuffix = composedFieldName + additionalFieldSuffix;
									const fieldNameWithSuffix = fieldName + additionalFieldSuffix;
									return [fieldNameWithSuffix, fieldFetchedData?.[composedFieldNameWithSuffix] ?? null];
								});
								data.push([fieldName, fieldFetchedData?.[composedFieldName] ?? null]);
								return data;
							})
						)
					};

					results.push(itemData);
				});

				return results;
			}

			fetch() {
				const data = super.fetch();
				data[this.getBidsFieldName() + 'Data'] = this.fetchFieldData();
				return data;
			}

			loadAvailableBidFields() {
				return new Promise(resolve => {
					if (this.listSmall) {
						resolve(this.listSmall);
						return;
					}
					let listName = 'listSmall';
					if (this.model.name === 'Opportunity') {
						listName = 'listForOpportunity';
					} else if (this.model.name === 'Account') {
						listName = 'listForAccount';
					}
					this.getHelper().layoutManager.get('OpportunityBid', listName, (list) => {
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

					data.bidsData = data.bidsData.map((bid, index) => {
						bid.index = index;
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

				data.availableFields = this.listSmall.map((field) => field.name);
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