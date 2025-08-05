<div class="contact-address-container">
    {{#each contactAddressData}}
        <div class="contact-address-block" data-id="{{@index}}">
            <input type="hidden" class="contact-address-id" value="{{contactAddressId}}">
            <div class="row">
                <div class="col-sm-1">
                    <input type="radio" class="primary-radio" name="{{../name}}-primary" {{#if primary}}checked{{/if}}>
                </div>
                <div class="col-sm-10">
                    <div class="row" style="margin-top: 5px;">
                        <div class="col-sm-12">
                            <input type="text" class="form-control contact-address-description" placeholder="{{translate 'description' category='fields' scope='ContactAddress'}}" value="{{description}}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" class="form-control contact-address-street" placeholder="{{translate 'street' category='fields' scope='ContactAddress'}}" value="{{street}}">
                        </div>
                    </div>
                    <div class="row" style="margin-top: 5px;">
                        <div class="col-sm-4">
                            <input type="text" class="form-control contact-address-city" placeholder="{{translate 'city' category='fields' scope='ContactAddress'}}" value="{{city}}">
                        </div>
                        <div class="col-sm-3">
                            <input type="text" class="form-control contact-address-state" placeholder="{{translate 'state' category='fields' scope='ContactAddress'}}" value="{{state}}">
                        </div>
                        <div class="col-sm-3">
                            <input type="text" class="form-control contact-address-postal-code" placeholder="{{translate 'postalCode' category='fields' scope='ContactAddress'}}" value="{{postalCode}}">
                        </div>
                        <div class="col-sm-2">
                            <input type="text" class="form-control contact-address-country" placeholder="{{translate 'country' category='fields' scope='ContactAddress'}}" value="{{country}}">
                        </div>
                    </div>
                    <div class="row" style="margin-top: 5px;">
                        <input type="hidden" class="contact-address-account-id" value="{{accountId}}">
                        <input type="hidden" class="contact-address-account-name" value="{{accountName}}">
                        <div class="col-sm-6" data-name="{{../name}}Account-{{@index}}"></div>
                        <input type="hidden" class="contact-address-type-val" value="{{type}}">
                        <div class="col-sm-6 contact-address-type"></div>
                    </div>
                </div>
                <div class="col-sm-1">
                    <button type="button" class="btn btn-link" data-action="removeContactAddress" title="{{translate 'Remove'}}">
                        <span class="fas fa-times"></span>
                    </button>
                </div>
            </div>
            <hr style="margin: 10px 0;">
        </div>
    {{/each}}
</div>

<button type="button" class="btn btn-default" data-action="addContactAddress">
    <span class="fas fa-plus"></span>
    {{translate 'Add'}}
</button>