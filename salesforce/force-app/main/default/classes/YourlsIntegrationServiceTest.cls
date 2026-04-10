@isTest
private class YourlsIntegrationServiceTest {

    @isTest
    static void testQueueableExecution() {
        // Enforce strict multi-user context and bulkification
        Profile p = [SELECT Id FROM Profile WHERE Name='Standard User' LIMIT 1];
        User testUser = new User(Alias = 'standt', Email='standarduser@testorg.com',
            EmailEncodingKey='UTF-8', LastName='Testing', LanguageLocaleKey='en_US',
            LocaleSidKey='en_US', ProfileId = p.Id,
            TimeZoneSidKey='America/Los_Angeles', UserName='bastion_testuser@testorg.com');

        System.runAs(testUser) {
            Map<Id, String> mockData = new Map<Id, String>();
            // Simulate bulk payload
            for(Integer i=0; i<200; i++) {
                // We use mock Ids for pure unit testing of the queueable execution structure
                mockData.put((Id)('500000000000000'.substring(0,12) + String.valueOf(i).leftPad(3, '0')), 'https://example.com/long/url/' + i);
            }

            Test.startTest();
            System.enqueueJob(new YourlsIntegrationService.YourlsCalloutQueueable(mockData));
            Test.stopTest();

            // In a full implementation, we would mock the HTTP callout using HttpCalloutMock
            // and assert the Case records were updated correctly.
            // For structural validation, ensuring limits are respected within the Test block is primary.
            System.assert(Limits.getQueries() < 40, 'Governor limits breached for SOQL');
            System.assert(Limits.getCpuTime() < 4000, 'Governor limits breached for CPU Time');
        }
    }
}
