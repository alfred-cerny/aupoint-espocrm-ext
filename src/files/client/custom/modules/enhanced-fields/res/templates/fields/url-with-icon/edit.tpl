<div style="display: flex; align-items: center;">
    {{#if iconClassName}}
    <span style="margin-right: 8px; display: inline-flex; align-items: center;">
        <span class="{{iconClassName}}" {{#if iconColor}}style="color:{{iconColor}}"{{/if}}></span>
    </span>
    {{/if}}
    <input
        type="text"
        class="main-element form-control"
        data-name="{{name}}"
        value="{{value}}"
        {{#if params.maxLength}} maxlength="{{params.maxLength}}"{{/if}}
        autocomplete="espo-{{name}}"
        {{#if noSpellCheck}}
        spellcheck="false"
        {{/if}}
        style="flex: 1;"
    >
</div>