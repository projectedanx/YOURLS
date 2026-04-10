trigger CaseTrigger on Case (after insert, after update) {
    CaseTriggerHandler handler = new CaseTriggerHandler();

    if (Trigger.isAfter) {
        if (Trigger.isInsert) {
            handler.afterInsert(Trigger.new);
        } else if (Trigger.isUpdate) {
            handler.afterUpdate(Trigger.new, Trigger.oldMap);
        }
    }
}
