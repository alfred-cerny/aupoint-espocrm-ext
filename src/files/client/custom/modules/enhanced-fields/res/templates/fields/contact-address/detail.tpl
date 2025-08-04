{{#if contactAddressData}}
    {{#each contactAddressData}}
        <div class="contact-address-item" style="padding-bottom: 15px; margin-bottom: 10px; border-bottom: 1px solid #ddd;">
            {{#if primary}}
                <span style="float: right; font-size: 0.8em; font-weight: bold; color: #555;">{{translate 'Primary'}}</span>
            {{/if}}

            <div class="address-details" {{#if primary}}style="font-weight: bold;"{{/if}}>
                {{#if street}}{{street}}<br>{{/if}}
                {{#if city}}{{city}}{{/if}}{{#if state}}{{#if city}}, {{/if}}{{state}}{{/if}}{{#if postalCode}} {{postalCode}}{{/if}}
                {{#if country}}{{#if city}}<br>{{else}}{{#if state}}<br>{{else}}{{#if postalCode}}<br>{{/if}}{{/if}}{{/if}}{{country}}{{/if}}
            </div>

            <div class="metadata" style="font-size: 0.9em; color: #666; margin-top: 5px;">
                <span>
                    {{translate 'Account'}}:
                    {{#if accountId}}
                        <a data-name="{{../name}}Account-{{@index}}" href="/#Account/view/{{accountId}}">{{accountId}}</a>
                    {{else}}
                        <span class="none-value">{{translate 'None'}}</span>
                    {{/if}}
                </span>
                <span style="margin: 0 8px;">|</span>
                {{#if type}}<span>{{translate 'Type'}}: {{type}}</span>{{/if}}
                {{#if description}}
                    <span style="margin: 0 8px;">|</span>
                    <span>{{translate 'Description'}}: {{description}}</span>
                {{/if}}
            </div>
        </div>
    {{/each}}
{{else}}
    <div class="none-value">{{translate 'None'}}</div>
{{/if}}