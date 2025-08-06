{{#if accountAddressData}}
    {{#each accountAddressData}}
        {{#if primary}}
            {{#if street}}{{street}}, {{/if}}{{#if city}}{{city}}{{/if}}{{#if state}}{{#if city}}, {{/if}}{{state}}{{/if}}{{#if country}}{{#if city}}, {{else}}{{#if state}}, {{/if}}{{/if}}{{country}}{{/if}}
        {{/if}}
    {{/each}}
{{else}}
    <span class="none-value">{{translate 'None'}}</span>
{{/if}}