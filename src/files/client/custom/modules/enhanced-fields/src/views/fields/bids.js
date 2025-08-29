define(['views/fields/link-multiple'], (Dep) => {
		class BidsField extends Dep {
			detailTemplate = 'enhanced-fields:fields/bids/detail';
			editTemplate = 'enhanced-fields:fields/bids/edit';
			relationTypeFieldName = 'type';
			relationClassNameMapping = {};
			models = [];
			validations = ['models'];
			listSmall = null;

			setup() {
				super.setup();
				this.relationClassNameMapping = this.getMetadata().get("entityDefs.Account.fields.type.style") || {};
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
				if (this.getFieldRelationType()) {
					filters.type = {
						type: 'in',
						value: [this.getFieldRelationType()]
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

				if (!bidData?.id) {
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
				this.models = [];
				bidsData.forEach((bidData, index) => {
					this.getModelFactory().create('OpportunityBid', model => {
						this.models.push(model);
						/*
						* @todo: think harder about this
						*/
						if (this.model.name === 'Account') {
							model.set('accountId', this.model.get('id'));
							model.set('accountName', this.model.get('name'));
						} else if (this.model.name === 'Opportunity') {
							model.set('opportunityId', this.model.get('id'));
							model.set('opportunityName', this.model.get('name'));
						}

						this.listSmall.forEach((field) => {
							const fieldName = field.name;
							const fieldValue = bidData[fieldName] || null;
							const fieldType = model.getFieldType(fieldName);
							const additionalFields = fieldType === 'link'
								? ['Id', 'Name']
								: fieldType === 'linkMultiple'
									? ['Ids', 'Names']
									: [];
							model.set(fieldName, fieldValue);
							additionalFields.forEach(additionalFieldSuffix => {
								const fieldNameWithSuffix = fieldName + additionalFieldSuffix;
								model.set(fieldNameWithSuffix, bidData[fieldNameWithSuffix] || null);
							});

							const type = model.getFieldType(fieldName) || 'base';
							const viewName = model.getFieldParam(fieldName, 'view') ||
								this.getFieldManager().getViewName(type);
							this.createView(this.getComposedFieldName(fieldName, index), viewName, {
								model,
								mode: this.MODE_EDIT,
								name: fieldName,
								selector: `.opportunity-bid-item[data-index="${index}"] div[data-name="${fieldName}"]`,
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

						if (this.getFieldRelationType()) {
							model.set('type', this.getFieldRelationType());
						}
					});
				});
			}

			fetchFieldData() {
				return this.$el.find('.opportunity-bids-content .opportunity-bid-item').map((index, itemEl) => {
					const model = this.models[index];
					if (!model) {
						console.error("Model not found.");
						debugger;
						return null;
					}
					const $item = $(itemEl);
					const bidId = $item.attr('data-bid-id');

					return {
						...(bidId && {id: bidId}),
						index,
						...model.attributes
					};
				}).get();
			}

			fetch() {
				const data = super.fetch();
				data[this.name + 'Data'] = this.fetchFieldData();
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
					const fieldRelationType = this.getFieldRelationType();
					if (fieldRelationType) {
						listName += fieldRelationType;
					}
					this.getHelper().layoutManager.get('OpportunityBid', listName, (list) => {
						this.listSmall = list;
						resolve(list);
					});
				});
			}

			validateModels() {
				return this.listSmall.some(field => {
					const fieldName = field.name;
					return this.models.some((model, index) => {
						const fieldView = this.getView(this.getComposedFieldName(fieldName, index));
						return /* is not valid */ fieldView?.validate();
					});
				});
			}

			data() {
				const data = super.data();
				const bidsData = this.getBidsData();
				data.availableFields = this.listSmall.map((field) => field.name);
				if (!bidsData) {
					return data;
				}
				data.bidsData = Array.isArray(bidsData) ? bidsData : Object.values(bidsData);

				const relationType = this.getRelationType();
				if (!this.getFieldRelationType() && relationType) {
					data.bidsData = data.bidsData.filter(bid => bid.type === relationType);
				}

				data.bidsData = data.bidsData.map((bid, index) => {
					bid.index = index;
					const relationClass = this.relationClassNameMapping[bid.type || ''];
					if (relationClass) {
						bid.className = 'label-' + relationClass.toLowerCase();
					}
					if (bid.type === 'Partner') {
						const {amount, ...bidWithoutAmount} = bid;
						return bidWithoutAmount;
					}
					bid.partnershipNature = null;

					return bid;
				});
				data.bidsDataCount = data.bidsData.length;

				return data;
			}

			getFieldRelationType() {
				const fieldRelationship = this.name.replaceAll(this.getBidsFieldName(), '');
				if (fieldRelationship !== this.name) {
					return fieldRelationship;
				}
				return null;
			}

			getBidsFieldName() {
				if (this.model.name === 'Opportunity') {
					return 'bids';
				}
				return 'opportunityBids';
			}

			getBidsData() {
				return this.model.get(this.name + 'Data');
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