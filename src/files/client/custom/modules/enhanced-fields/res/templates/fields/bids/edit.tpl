{{#if bidsData}}
<div class="opportunity-bids-container">
    <div class="opportunity-bids-header">
        <span class="badge badge-info">{{bidsDataCount}} {{translate 'items'}}</span>
        <button type="button" class="btn btn-sm btn-default pull-right" data-action="addBid">
            <i class="fas fa-plus"></i> {{translate 'Add Bid'}}
        </button>
    </div>

    <div class="opportunity-bids-content">
        {{#each bidsData}}
        <div class="opportunity-bid-item {{#if className}}{{className}}{{/if}}" data-bid-id="{{id}}" style="margin-bottom: 10px; padding: 12px; border: 1px solid #ddd; border-radius: 3px; background-color: #fafafa;">
            <div class="row">
                <div class="col-sm-10">
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label class="control-label">{{translate 'Opportunity Name'}}</label>
                        <div data-name="opportunityName-{{id}}"></div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group" style="margin-bottom: 8px;">
                                <label class="control-label">{{translate 'Amount'}}</label>
                                <div data-name="amount-{{id}}" style="margin-top: 1px;"></div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group" style="margin-bottom: 8px;">
                                <label class="control-label">{{translate 'Relation'}}</label>
                                <div data-name="relation-{{id}}"></div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group" style="margin-bottom: 8px;">
                                <label class="control-label">{{translate 'Status'}}</label>
                                <div data-name="status-{{id}}"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group" style="margin-bottom: 8px;">
                                <label class="control-label">{{translate 'Partnership Nature'}}</label>
                                <div data-name="partnershipNature-{{id}}"></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group" style="margin-bottom: 8px;">
                                <label class="control-label">{{translate 'Contact'}}</label>
                                <div data-name="contact-{{id}}"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="control-label">{{translate 'Opportunity'}}</label>
                        <div data-name="opportunity-{{id}}"></div>
                    </div>
                </div>
                <div class="col-sm-2 text-right">
                    <button type="button" class="btn btn-sm btn-danger" data-action="removeBid" data-bid-id="{{id}}" style="margin-top: 20px;">
                        <i class="fas fa-times"></i> {{translate 'Remove'}}
                    </button>
                    {{#if ../showBidDetails}}
                    <div style="margin-top: 10px;">
                        <small class="text-muted">ID: {{id}}</small>
                    </div>
                    {{/if}}
                </div>
            </div>
        </div>
        {{/each}}
    </div>

    <!-- Summary Section (Read-only in edit mode) -->
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
<div class="opportunity-bids-empty text-center" style="padding: 20px;">
    <span class="text-muted">{{translate 'No data available'}}</span>
    <div style="margin-top: 10px;">
        <button type="button" class="btn btn-default" data-action="addBid">
            <i class="fas fa-plus"></i> {{translate 'Add First Bid'}}
        </button>
    </div>
</div>
{{/if}}