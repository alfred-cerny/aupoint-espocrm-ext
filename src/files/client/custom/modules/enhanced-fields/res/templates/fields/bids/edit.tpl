{{#if bidsData}}
<div class="opportunity-bids-container">
    <div class="opportunity-bids-header">
        <span class="badge badge-info">{{bidsDataCount}} {{translate 'items'}}</span>
    </div>

    <div class="opportunity-bids-content">
        <div>
            <table class="table table-panel">
                <thead>
                    <tr>
                        {{#each availableFields}}
                        <th>{{translate this category='fields' scope='OpportunityBid'}}</th>
                        {{/each}}
                        <th class="text-center width-auto">{{translate 'Actions'}}</th>
                    </tr>
                </thead>
                <tbody>
                    {{#each bidsData}}
                    <tr class="opportunity-bid-item {{#if className}}{{className}}{{/if}}" data-bid-id="{{id}}" data-index="{{@index}}">
                        {{#each ../availableFields}}
                        <td>
                            <div data-name="{{this}}-{{@../index}}"></div>
                        </td>
                        {{/each}}
                        <td class="text-center width-auto">
                            <button type="button" class="btn btn-sm btn-danger" data-action="removeBid" data-bid-id="{{id}}" title="{{translate 'Remove'}}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    {{/each}}
                </tbody>
            </table>
        </div>
    </div>

    <div class="opportunity-bids-footer">
        <div class="button-container">
            <button type="button" class="btn btn-sm btn-default" data-action="addBid">
                <i class="fas fa-plus"></i> {{translate 'Add'}}
            </button>
            <button type="button" class="btn btn-sm btn-primary" data-action="selectBid">
                <i class="fas fa-check"></i> {{translate 'Select'}}
            </button>
        </div>
    </div>
</div>

{{else}}
<div class="opportunity-bids-empty text-center">
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-inbox"></i>
        </div>
        <h4 class="text-muted">{{translate 'No data available'}}</h4>
        <p class="text-muted">{{translate 'Start by adding your first bid'}}</p>
        <div class="button-container">
            <button type="button" class="btn btn-primary" data-action="addBid">
                <i class="fas fa-plus"></i> {{translate 'Add'}}
            </button>
        </div>
    </div>
</div>
{{/if}}