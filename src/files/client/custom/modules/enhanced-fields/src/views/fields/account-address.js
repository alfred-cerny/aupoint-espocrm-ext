define('enhanced-fields:views/fields/account-address', ['views/fields/base', 'ui/select'], (Dep, Select) => {
		class AccountAddressField extends Dep {
			type = 'accountAddress';

			editTemplate = 'enhanced-fields:fields/account-address/edit';
			detailTemplate = 'enhanced-fields:fields/account-address/detail';
			listTemplate = 'enhanced-fields:fields/account-address/list';

			addressFormat = null;

			validations = ['addressData'];

			events = {
				'click [data-action="addAccountAddress"]': () => {
					this.addAccountAddress();
				},
				'click [data-action="removeAccountAddress"]': (e) => {
					const $block = $(e.currentTarget).closest('.account-address-block');
					const index = $block?.attr('data-id') || 0;
					this.removeAccountAddress(index, $block);
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
				this.$el.find('.account-address-block').each((i, el) => {
					const $block = $(el);
					if (this.mode === 'edit') {
						this.initAddressAutocomplete($block);
					}
					const accountFieldName = this.getIndexedFieldName('Account', i);
					this.getModelFactory().create(this.model.name, model => {
						model.set('accountsIds', this.model.get('accountsIds'));
						model.set('accountId', $block.find('.account-address-account-id').val() || null);
						model.set('accountName', $block.find('.account-address-account-name').val() || null);

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

						let type = $block.find('.account-address-type-val').val() || null;
						if (type && !Array.isArray(type)) {
							type = [type];
						}
						model.set('type', type);

						const addressTypeFieldName = this.getIndexedFieldName('Type', i);
						this.createView(addressTypeFieldName, 'views/fields/multi-enum', {
							model,
							mode: this.mode,
							name: 'type',
							selector: `.account-address-block[data-id="${i}"] .account-address-type`,
							defs: {
								params: {
									maxCount: 1,
									allowCustomOptions: true,
									optionsReference: 'AccountAddress.type',
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
					['.account-address-country', config.get('addressCountryList') || []],
					['.account-address-state', config.get('addressStateList') || []],
					['.account-address-city', config.get('addressCityList') || []],
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

			addAccountAddress() {
				const data = this.fetchFieldData();
				const addressData = {
					primary: data.length === 0,
					accountAddressId: null
				};

				if (this.model.name === 'Account') {
					addressData.accountId = this.model.get('id');
					addressData.accountName = this.model.get('name');
				}

				data.push(addressData);

				this.model.set(this.dataFieldName, data, {silent: true});
				this.reRender().then(() => {
					this.$el.find('.account-address-street').last().focus();
				});
			}

			removeAccountAddress(index, $block) {
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
				return this.$el.find('.account-address-block').map((i, el) => {
					const $block = $(el);
					const accountFieldName = `${this.name}Account-${i}`;

					let type = $block.find('.account-address-type .item').attr('data-value') || null;
					if (type && !Array.isArray(type)) {
						type = [type];
					}

					return {
						accountAddressId: $block.find('.account-address-id').val() || null,
						description: $block.find('.account-address-description').val() || null,
						accountId: $block.find(`div[data-name="${accountFieldName}"] input[data-name="accountId"]`).val() || null,
						accountName: $block.find(`div[data-name="${accountFieldName}"] input[data-name="accountName"]`).val() || null,
						street: $block.find('.account-address-street').val().trim() || null,
						city: $block.find('.account-address-city').val().trim() || null,
						state: $block.find('.account-address-state').val().trim() || null,
						country: $block.find('.account-address-country').val().trim() || null,
						postalCode: $block.find('.account-address-postal-code').val().trim() || null,
						type: type,
						primary: $block.find(`input[name="${this.name}-primary"]`).is(':checked'),
					};
				}).get();
			}

			data() {
				let accountAddressData = this.model.get(this.dataFieldName);

				if (this.mode === 'edit') {
					accountAddressData = accountAddressData || [];

					if (!accountAddressData.length) {
						accountAddressData.push({
							street: null,
							city: null,
							state: null,
							country: null,
							postalCode: null,
							primary: true,
							accountAddressId: null,
							accountAddressName: null,
							accountId: null,
							description: null
						});
					}
				}

				return {
					accountAddressData: accountAddressData,
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
						const msg = this.translate('accountIsRequired', 'messages', 'AccountAddress')
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

		return AccountAddressField;
	}
);