<div class="account-address-container">
    {{#each accountAddressData}}
        <div class="account-address-block" data-id="{{@index}}">
            <input type="hidden" class="account-address-id" value="{{accountAddressId}}">
            <div class="row">
                <div class="col-sm-1">
                    <input type="radio" class="primary-radio" name="{{../name}}-primary" {{#if primary}}checked{{/if}}>
                </div>
                <div class="col-sm-10">
                    <div class="row" style="margin-top: 5px;">
                        <div class="col-sm-12">
                            <input type="text" class="form-control account-address-description" placeholder="{{translate 'description' category='fields' scope='AccountAddress'}}" value="{{description}}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" class="form-control account-address-street" placeholder="{{translate 'street' category='fields' scope='AccountAddress'}}" value="{{street}}">
                        </div>
                    </div>
                    <div class="row" style="margin-top: 5px;">
                        <div class="col-sm-4">
                            <input type="text" class="form-control account-address-city" placeholder="{{translate 'city' category='fields' scope='AccountAddress'}}" value="{{city}}">
                        </div>
                        <div class="col-sm-3">
                            <input type="text" class="form-control account-address-state" placeholder="{{translate 'state' category='fields' scope='AccountAddress'}}" value="{{state}}">
                        </div>
                        <div class="col-sm-3">
                            <input type="text" class="form-control account-address-postal-code" placeholder="{{translate 'postalCode' category='fields' scope='AccountAddress'}}" value="{{postalCode}}">
                        </div>
                        <div class="col-sm-2">
                            <input type="text" class="form-control account-address-country" placeholder="{{translate 'country' category='fields' scope='AccountAddress'}}" value="{{country}}">
                        </div>
                    </div>
                    <div class="row" style="margin-top: 5px;">
                        <input type="hidden" class="account-address-account-id" value="{{accountId}}">
                        <input type="hidden" class="account-address-account-name" value="{{accountName}}">
                        <div class="col-sm-6" data-name="{{../name}}Account-{{@index}}"></div>
                        <input type="hidden" class="account-address-labels-val" value="{{labels}}">
                        <div class="col-sm-6 account-address-labels"></div>
                    </div>
                </div>
                <div class="col-sm-1">
                    <button type="button" class="btn btn-link" data-action="removeAccountAddress" title="{{translate 'Remove'}}">
                        <span class="fas fa-times"></span>
                    </button>
                </div>
            </div>
            <hr style="margin: 10px 0;">
        </div>
    {{/each}}
</div>

<button type="button" class="btn btn-default" data-action="addAccountAddress">
    <span class="fas fa-plus"></span>
    {{translate 'Add'}}
</button>

<button type="button" class="btn btn-default" data-action="linkAccountAddress">
    <span class="fas fa-plus"></span>
    {{translate 'Select'}}
</button>