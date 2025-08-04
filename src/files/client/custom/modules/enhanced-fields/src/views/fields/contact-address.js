define('enhanced-fields:views/fields/contact-address', ['views/fields/base', 'ui/select'], (Dep, Select) => {
		class ContactAddressField extends Dep {
			type = 'contactAddress';

			editTemplate = 'enhanced-fields:fields/contact-address/edit';
			detailTemplate = 'enhanced-fields:fields/contact-address/detail';
			listTemplate = 'enhanced-fields:fields/contact-address/list';

			addressFormat = null;

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

			afterRender() {
				super.afterRender();
				this.$el.find('.contact-address-block').each((i, el) => {
					const $block = $(el);
					if (this.mode === 'edit') {
						this.initAddressAutocomplete($block);
					}
					const accountFieldName = `${this.name}Account-${i}`;
					this.model.set(accountFieldName + 'Id', $block.find('.contact-address-account-id').val() || null);

					this.createView(accountFieldName, 'views/fields/link', {
						model: this.model,
						mode: this.mode,
						name: accountFieldName,
						selector: `div[data-name="${accountFieldName}"]`,
						foreignScope: 'Account'
					}).then((view) => {
						view.render();
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
				data.push({
					primary: data.length === 0,
					contactAddressId: null
				});
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

					return {
						contactAddressId: $block.find('.contact-address-id').val() || null,
						description: $block.find('.contact-address-description').val() || null,
						accountId: $block.find(`div[data-name="${accountFieldName}"] input[data-name="${accountFieldName}Id"]`).val() || null,
						street: $block.find('.contact-address-street').val().trim() || null,
						city: $block.find('.contact-address-city').val().trim() || null,
						state: $block.find('.contact-address-state').val().trim() || null,
						country: $block.find('.contact-address-country').val().trim() || null,
						postalCode: $block.find('.contact-address-postal-code').val().trim() || null,
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
		}

		return ContactAddressField;
	}
);