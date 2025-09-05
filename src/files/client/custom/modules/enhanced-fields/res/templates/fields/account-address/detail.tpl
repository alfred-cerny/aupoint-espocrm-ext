{{#if accountAddressData}}
    {{#each accountAddressData}}
        <div class="account-address-item" style="padding-bottom: 15px; margin-bottom: 10px; border-bottom: 1px solid #ddd; display: flex; align-items: flex-start;">
            <div class="address-label" style="font-weight: bold; min-width: 150px; margin-right: 20px; flex-shrink: 0; {{#if primary}}color: #0066cc;{{/if}}">
                {{#if primary}}★ {{/if}}{{#if description}}{{{description}}}{{/if}}
                {{#if primary}}
                    <span style="font-size: 0.8em; color: #0066cc; font-weight: normal; display: block;">({{translate 'Primary'}})</span>
                {{/if}}
            </div>

            <div class="address-content" style="flex: 1; {{#if primary}}border-left: 3px solid #0066cc; padding-left: 10px;{{/if}}">
                <div class="address-details">
                    {{#if street}}{{street}}<br>{{/if}}
                    {{#if city}}{{city}}{{/if}}{{#if state}}{{#if city}}, {{/if}}{{state}}{{/if}}{{#if postalCode}} {{postalCode}}{{/if}}
                    {{#if country}}{{#if city}}<br>{{else}}{{#if state}}<br>{{else}}{{#if postalCode}}<br>{{/if}}{{/if}}{{/if}}{{country}}{{/if}}
                </div>

                <div class="metadata" style="font-size: 0.9em; color: #666; margin-top: 5px;">
                    {{#if showAccountInfo}}
                    <span>
                        {{translate 'Account'}}:
                        {{#if accountId}}
                            <a data-name="{{../name}}Account-{{@index}}" href="/#Account/view/{{accountId}}">{{accountName}}</a>
                        {{else}}
                            <span class="none-value">{{translate 'None'}}</span>
                        {{/if}}
                    </span>
                    {{/if}}
                    {{#if hasLabels}}
                        {{#if showAccountInfo}}<span style="margin: 0 8px;">|</span>{{/if}}
                        {{#each labels as | label |}}
                        <span>{{translate 'Labels' category='fields' scope='AccountAddress'}}: {{label}}</span>
                        {{/each}}
                        {{#if labelOtherDescription}}
                        <span style="margin: 0 8px;">⇒</span>
                        <span>{{labelOtherDescription}}</span>
                        {{/if}}
                    {{/if}}
                </div>
            </div>
        </div>
    {{/each}}
{{else}}
    <div class="none-value">{{translate 'None'}}</div>
{{/if}}