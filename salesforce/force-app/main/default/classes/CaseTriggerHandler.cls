/**
 * @description Deterministic Apex Trigger Handler for Case.
 * Decouples logic from execution, enabling unit testing and safe bypasses.
 */
public inherited sharing class CaseTriggerHandler {

    public void afterInsert(List<Case> newRecords) {
        processYourlsIntegration(newRecords);
    }

    public void afterUpdate(List<Case> newRecords, Map<Id, Case> oldMap) {
        // Evaluate if criteria met for YOURLS integration
        List<Case> eligibleCases = new List<Case>();
        for(Case c : newRecords) {
             Case oldCase = oldMap.get(c.Id);
             // Example condition: Generate when status changes to 'Escalated' and Short URL is null
             if(c.Status == 'Escalated' && oldCase.Status != 'Escalated' && String.isBlank(c.Short_URL__c)) {
                 eligibleCases.add(c);
             }
        }
        processYourlsIntegration(eligibleCases);
    }

    private void processYourlsIntegration(List<Case> eligibleCases) {
        if (eligibleCases.isEmpty()) {
            return;
        }

        Map<Id, String> caseIdToLongUrl = new Map<Id, String>();
        for (Case c : eligibleCases) {
            // Assume we want to shorten a standard view URL for the case
            String longUrl = 'https://' + URL.getSalesforceBaseUrl().getHost() + '/' + c.Id;
            caseIdToLongUrl.put(c.Id, longUrl);
        }

        if (!caseIdToLongUrl.isEmpty()) {
            // Enqueue the job. We decouple here per The Asynchronous Imperative.
            System.enqueueJob(new YourlsIntegrationService.YourlsCalloutQueueable(caseIdToLongUrl));
        }
    }
}
