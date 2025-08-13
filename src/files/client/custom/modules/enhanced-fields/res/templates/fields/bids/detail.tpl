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
                    <strong>
                        {{#if opportunityId}}
                            <a href="#Opportunity/view/{{opportunityId}}" title="{{translate 'View Opportunity'}}">
                                {{opportunityName}}
                            </a>
                        {{else}}
                            {{opportunityName}}
                        {{/if}}
                    </strong>
                    <span style="margin: 0 8px;">•</span>
                    <span class="currency-amount">{{amount}}</span>
                    <span style="margin-left: 8px; font-size: 0.9em;">
                        {{translate relation category='options' field='relation'}}
                    </span>
                    {{#if partnershipNature}}
                    <span style="margin: 0 8px; ">•</span>
                    <span style="font-size: 0.9em;">
                        {{translate partnershipNature category='options' field='partnershipNature'}}
                    </span>
                    {{/if}}
                    {{#if contactId}}
                    <span style="margin: 0 8px; ">•</span>
                    <span style="font-size: 0.9em;">
                        <i class="fas fa-user" style="margin-right: 4px;"></i>
                        <a href="#Contact/view/{{contactId}}" title="{{translate 'View Contact'}}">
                            {{contactName}}
                        </a>
                    </span>
                    {{/if}}
                </div>
                <div class="col-sm-5 text-right">
                    <span class="label label-{{#ifEqual status 'Win'}}success{{/ifEqual}}{{#ifEqual status 'Lose'}}danger{{/ifEqual}}{{#ifEqual status 'Pending'}}warning{{/ifEqual}}{{#ifEqual status 'Qualified'}}info{{/ifEqual}}">
                        {{translate status category='options' scope='Opportunity' field='stage'}}
                    </span>
                    <a href="#OpportunityBid/view/{{id}}" title="{{translate 'View Bid Details'}}" style="margin-left: 8px;">
                        <i class="fas fa-external-link-alt" aria-hidden="true"></i>
                    </a>
                    {{#if ../showBidDetails}}
                    <small class="text-muted" style="margin-left: 8px;">{{id}}</small>
                    {{/if}}
                </div>
            </div>
        </div>
        {{/each}}
    </div>

    <!-- Summary Section -->
    {{#if showSummary}}
    <div class="opportunity-bids-summary" style="margin-top: 10px; padding: 10px; background-color: #f5f5f5; border-radius: 3px;">
        <div class="row">
            <div class="col-sm-3 text-center">
                <strong>{{totalBids}}</strong> {{translate 'Total Bids'}}
            </div>
            <div class="col-sm-3 text-center">
                <strong class="text-success">{{winBids}}</strong> {{translate 'Wins'}}
            </div>
            <div class="col-sm-3 text-center">
                <strong class="text-danger">{{loseBids}}</strong> {{translate 'Losses'}}
            </div>
            <div class="col-sm-3 text-center">
                <strong>{{totalAmount}}</strong> {{translate 'Total Amount'}}
            </div>
        </div>
    </div>
    {{/if}}
</div>

{{else}}
<div class="opportunity-bids-empty text-center">
    <span class="text-muted">{{translate 'No data available'}}</span>
</div>
{{/if}}