define('enhanced-fields:views/fields/contact-address', ['views/fields/base', 'ui/select'], (Dep, Select) => {
		class ContactAddressField extends Dep {
			type = 'contactAddress';

			editTemplate = 'enhanced-fields:fields/contact-address/edit';
			detailTemplate = 'enhanced-fields:fields/contact-address/detail';
			listTemplate = 'enhanced-fields:fields/contact-address/list';

			addressFormat = null;

			validations = ['addressData'];

			events = {
				'click [data-action="addContactAddress"]': () => {
					this.addContactAddress();
				},
				'click [data-action="removeContactAddress"]': (e) => {
					const $block = $(e.currentTarget).closest('.contact-address-block');
					const index = $block?.attr('data-id') || 0;
					this.removeContactAddress(index, $block);
				},
				'click [data-action="setPrimary"]': () => {
					this.trigger('change');
				},
				'change input': () => {
					this.trigger('change');
				},
			};

			setup() {
				super.setup();
				this.dataFieldName = this.name + 'Data';
				this.addressFormat = this.getConfig().get('addressFormat') || 1;
			}

			getIndexedFieldName(type, index) {
				return `${this.name}${type}-${index}`;
			}

			afterRender() {
				super.afterRender();
				this.$el.find('.contact-address-block').each((i, el) => {
					const $block = $(el);
					if (this.mode === 'edit') {
						this.initAddressAutocomplete($block);
					}
					const accountFieldName = this.getIndexedFieldName('Account', i);
					this.getModelFactory().create(this.model.name, model => {
						model.set('accountsIds', this.model.get('accountsIds'));
						model.set('accountId', $block.find('.contact-address-account-id').val() || null);
						model.set('accountName', $block.find('.contact-address-account-name').val() || null);

						this.createView(accountFieldName, 'enhanced-fields:views/enhanced-fields/fields/related-account-link', {
							model,
							mode: this.mode,
							name: 'account',
							selector: `div[data-name="${accountFieldName}"]`,
							foreignScope: 'Account',
							defs: {
								params: {
									required: true
								}
							}
						}).then((view) => {
							view.render();
						});

						let type = $block.find('.contact-address-type-val').val() || null;
						if (type && !Array.isArray(type)) {
							type = [type];
						}
						model.set('type', type);

						const addressTypeFieldName = this.getIndexedFieldName('Type', i);
						this.createView(addressTypeFieldName, 'views/fields/multi-enum', {
							model,
							mode: this.mode,
							name: 'type',
							selector: `.contact-address-block[data-id="${i}"] .contact-address-type`,
							defs: {
								params: {
									maxCount: 1,
									allowCustomOptions: true,
									optionsReference: 'ContactAddress.type',
								}
							}
						}).then((view) => {
							view.render();
						});
					});
				});
			}

			initAddressAutocomplete($block) {
				const config = this.getConfig();
				const lists = [
					['.contact-address-country', config.get('addressCountryList') || []],
					['.contact-address-state', config.get('addressStateList') || []],
					['.contact-address-city', config.get('addressCityList') || []],
				];

				for (const [selector, list] of lists) {
					if (list.length) {
						this.setupAutocomplete($block.find(selector), list);
					}
				}
			}

			setupAutocomplete($input, list) {
				if (!$input.length) return;
				$input.autocomplete({
					minChars: 0,
					lookup: list.map(item => ({value: item, data: item})),
				});
				$input.on('focus', () => {
					if (!$input.val()) $input.autocomplete('onValueChange');
				});
			}

			addContactAddress() {
				const data = this.fetchFieldData();
				const addressData = {
					primary: data.length === 0,
					contactAddressId: null
				};

				if (this.model.name === 'Account') {
					addressData.accountId = this.model.get('id');
					addressData.accountName = this.model.get('name');
				}

				data.push(addressData);

				this.model.set(this.dataFieldName, data, {silent: true});
				this.reRender().then(() => {
					this.$el.find('.contact-address-street').last().focus();
				});
			}

			removeContactAddress(index, $block) {
				const data = this.fetchFieldData();
				if (data.length <= 1) return;

				const wasPrimary = data[index]?.primary;
				data.splice(index, 1);

				if (wasPrimary && data.length > 0) {
					data[0].primary = true;
				}

				$block?.remove();
				this.trigger('change');
			}

			fetchFieldData() {
				return this.$el.find('.contact-address-block').map((i, el) => {
					const $block = $(el);
					const accountFieldName = `${this.name}Account-${i}`;

					let type = $block.find('.contact-address-type .item').attr('data-value') || null;
					if (type && !Array.isArray(type)) {
						type = [type];
					}

					return {
						contactAddressId: $block.find('.contact-address-id').val() || null,
						description: $block.find('.contact-address-description').val() || null,
						accountId: $block.find(`div[data-name="${accountFieldName}"] input[data-name="accountId"]`).val() || null,
						accountName: $block.find(`div[data-name="${accountFieldName}"] input[data-name="accountName"]`).val() || null,
						street: $block.find('.contact-address-street').val().trim() || null,
						city: $block.find('.contact-address-city').val().trim() || null,
						state: $block.find('.contact-address-state').val().trim() || null,
						country: $block.find('.contact-address-country').val().trim() || null,
						postalCode: $block.find('.contact-address-postal-code').val().trim() || null,
						type: type,
						primary: $block.find(`input[name="${this.name}-primary"]`).is(':checked'),
					};
				}).get();
			}

			data() {
				let contactAddressData = this.model.get(this.dataFieldName);

				if (this.mode === 'edit') {
					contactAddressData = contactAddressData || [];

					if (!contactAddressData.length) {
						contactAddressData.push({
							street: null,
							city: null,
							state: null,
							country: null,
							postalCode: null,
							primary: true,
							contactAddressId: null,
							contactAddressName: null,
							accountId: null,
							description: null
						});
					}
				}

				return {
					contactAddressData: contactAddressData,
					name: this.name,
				};
			}

			fetch() {
				const addressDataList = this.fetchFieldData()
					.filter(d => d.street || d.city || d.state || d.country || d.postalCode);

				const primaryAddress = addressDataList.find(item => item.primary) || addressDataList[0];

				return {
					[this.dataFieldName]: addressDataList,
					[this.name]: primaryAddress ? this.formatAddress(primaryAddress) : null,
				};
			}

			formatAddress(data) {
				const {street, city, state, postalCode, country} = data;
				const cityStateParts = [city, state, postalCode].filter(Boolean).join(', ');
				return [street, cityStateParts, country].filter(Boolean).join(', ');
			}


			validateAddressData() {
				const addressData = this.model.get(this.dataFieldName) ?? [];

				if (!Array.isArray(addressData)) {
					return true;
				}

				return addressData.some((address, i) => {
					/*if (!address.accountId) {
						const msg = this.translate('accountIsRequired', 'messages', 'ContactAddress')
							.replace('{field}', this.getLabelText());
						const accountFieldName = `${this.name}Account-${i}`;
						this.showValidationMessage(msg, `[data-name="${accountFieldName}"] input`);

						return true;
					}*/
					const addressAccountFieldName = this.getIndexedFieldName('Account', i);
					const accountInvalid = this.getView(addressAccountFieldName)?.validate();

					const addressTypeFieldName = this.getIndexedFieldName('Type', i);
					const typeInvalid = this.getView(addressTypeFieldName)?.validate();

					return accountInvalid || typeInvalid;
				});
			}
		}

		return ContactAddressField;
	}
);