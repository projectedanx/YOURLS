/**
 * @description Decoupled, bulkified service for integrating with YOURLS.
 * Adheres to The Law of Bulkification and The Asynchronous Imperative.
 */
public inherited sharing class YourlsIntegrationService {

    // Abstract interface ensuring boundary between Data layer and Callout Layer
    public interface IYourlsCallout {
        Map<Id, String> generateShortUrls(Map<Id, String> caseIdToLongUrl);
    }

    /**
     * @description Queueable Apex implementation to handle asynchronous HTTP callouts
     * Decouples the Service Cloud Case trigger context from external API latencies.
     */
    public class YourlsCalloutQueueable implements Queueable, Database.AllowsCallouts {
        private Map<Id, String> caseIdToLongUrl;

        public YourlsCalloutQueueable(Map<Id, String> caseIdToLongUrl) {
            this.caseIdToLongUrl = caseIdToLongUrl;
        }

        public void execute(QueueableContext context) {
            List<Case> casesToUpdate = new List<Case>();
            for (Id caseId : caseIdToLongUrl.keySet()) {
                HttpRequest req = new HttpRequest();
                req.setEndpoint('callout:YOURLS_API/yourls-api.php');
                req.setMethod('POST');
                String longUrl = caseIdToLongUrl.get(caseId);
                String body = 'action=shorturl&url=' + EncodingUtil.urlEncode(longUrl, 'UTF-8') + '&format=json';
                req.setBody(body);
                req.setHeader('Content-Type', 'application/x-www-form-urlencoded');

                Http http = new Http();
                HttpResponse res = http.send(req);
                if(res.getStatusCode() == 200) {
                    Map<String, Object> responseBody = (Map<String, Object>) JSON.deserializeUntyped(res.getBody());
                    if(responseBody.containsKey('shorturl')) {
                         casesToUpdate.add(new Case(Id = caseId, Short_URL__c = (String)responseBody.get('shorturl')));
                    }
                }
            }
            if (!casesToUpdate.isEmpty()) {
                update casesToUpdate;
            }
        }
    }
}
