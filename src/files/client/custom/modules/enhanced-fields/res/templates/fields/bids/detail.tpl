{{#if bidsData}}
<div class="opportunity-bids-container">
    <div class="opportunity-bids-header">
        <span class="badge badge-info">{{bidsDataCount}} {{translate 'items'}}</span>
    </div>

    <div class="opportunity-bids-content">
        {{#each bidsData}}
        <div class="opportunity-bid-item {{#if className}}{{className}}{{/if}}" data-bid-id="{{id}}" style="margin-bottom: 3px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 3px;">
            <div class="row">
                <div class="col-sm-7">
                    {{#if opportunityId}}
                    <strong>
                        <a href="#Opportunity/view/{{opportunityId}}" title="{{translate 'View Opportunity'}}" style="color:#000">
                            {{#if opportunityName}}{{opportunityName}}{{else}}{{opportunityId}}{{/if}}
                        </a>
                        <span style="margin: 0 8px;">•</span>
                    </strong>
                    {{/if}}
                    <span style="margin-left: 8px; font-size: 0.9em;">
                        {{translate relation category='options' field='relation'}}
                    </span>
                    {{#if partnershipNature}}
                    <span style="margin: 0 8px;">•</span>
                    <span style="font-size: 0.9em;">
                        {{translate partnershipNature category='options' field='partnershipNature'}}
                    </span>
                    {{/if}}
                    {{#if contactId}}
                    <span style="margin: 0 8px;">•</span>
                    <span style="font-size: 0.9em;">
                        <a href="#Contact/view/{{contactId}}" title="{{translate 'View Contact'}}" style="color:#000">
                            <i class="fas fa-user" style="margin-right: 4px;"></i>
                            {{#if contactName}}{{contactName}}{{else}}{{contactId}}{{/if}}
                        </a>
                    </span>
                    {{/if}}
                </div>
                <div class="col-sm-5 text-right">
                     {{#if amount}}<span class="currency-amount" style="margin-right: 8px;">{{amount}} {{amountCurrency}}</span>{{/if}}
                    <span class="label label-{{#ifEqual status 'Win'}}success{{/ifEqual}}{{#ifEqual status 'Lose'}}danger{{/ifEqual}}{{#ifEqual status 'Pending'}}warning{{/ifEqual}}{{#ifEqual status 'Qualified'}}info{{/ifEqual}}">
                        {{translate status category='options' scope='Opportunity' field='stage'}}
                    </span>
                    <a href="#OpportunityBid/view/{{id}}" title="{{translate 'View Bid Details'}}" style="margin-left: 8px; color:#000">
                        <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
        {{/each}}
    </div>
</div>

{{else}}
<div class="opportunity-bids-empty text-center">
    <span class="text-muted">{{translate 'No data available'}}</span>
</div>
{{/if}}