{{#if value~}}
    {{~#if copyToClipboard~}}
        <a
            role="button"
            data-action="copyToClipboard"
            class="pull-right text-soft"
            title="{{translate 'Copy to Clipboard'}}"
        ><span class="far fa-copy"></span></a>
    {{~/if~}}
	{{#if iconClassName}}
	<span class="{{iconClassName}}" {{#if iconColor}}style="color:{{iconColor}}"{{/if}}></span>
	{{/if}}
	<a
        href="{{url}}"
        target="_blank"
        rel="noopener noreferrer"
    >{{value}}</a>
{{~else}}
    {{#if valueIsSet}}<span class="none-value">{{translate 'None'}}</span>{{else}}
    <span class="loading-value"></span>{{/if}}
{{/if}}
