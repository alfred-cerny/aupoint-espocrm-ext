define(['views/fields/url'], (Dep) => {
		class UrlWithIcon extends Dep {
			detailTemplate = 'enhanced-fields:fields/url-with-icon/detail';
			editTemplate = 'enhanced-fields:fields/url-with-icon/edit';

			iconClassName = null;
			iconColor = '#000';

			setup() {
				super.setup();
				const fieldDefs = this.getMetadata().get('entityDefs.' + this.model.name + '.fields.' + this.name);

				this.iconClassName = this.params.iconClassName ?? fieldDefs.iconClassName;
				this.iconColor = this.params.iconColor ?? fieldDefs.iconColor;
			}

			data() {
				const data = super.data();
				data.iconClassName = this.iconClassName;
				data.iconColor = this.iconColor;
				return data;
			}
		}

		return UrlWithIcon;
	}
);