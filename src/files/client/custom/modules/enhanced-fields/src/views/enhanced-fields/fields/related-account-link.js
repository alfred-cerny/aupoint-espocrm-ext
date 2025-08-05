define('enhanced-fields:views/enhanced-fields/fields/related-account-link', ['views/fields/link'], (Dep) => {
    class RelatedAccountLink extends Dep {
        getSelectFilters() {
            let accountsIds = [];

            const accountId = this.model.get('accountId');
            if (accountId) {
                accountsIds.push(accountId);
            }

            const additionalAccountIds = this.model.get('accountsIds');
            if (additionalAccountIds && Array.isArray(additionalAccountIds)) {
                accountsIds = accountsIds.concat(additionalAccountIds);
            }

            if (accountsIds.length === 0) {
                return null;
            }

            return {
                'id': {
                    'type': 'in',
                    'value': accountsIds,
                }
            };
        }
    }

    return RelatedAccountLink;
});