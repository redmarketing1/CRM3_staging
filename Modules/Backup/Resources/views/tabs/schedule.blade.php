<div class="tab-pane fade" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
    <h1 class="backup-header">Schedule Settings</h1>

    <div class="row">
        <div class="col-xl-10 col-md-12">
            <div class="backup-rounded">
                <div class="font-medium text-xl">Schedule Time</div>
                <div class="d-flex gap-5 justify-content-between my-2 w-100 text-xl">
                    <div>
                        <input type="radio" name="backupType" id="backup-all">
                        <label for="backup-all">12Hours</label>
                    </div>
                    <div>
                        <input type="radio" name="backupType" id="backup-files">
                        <label for="backup-files">Daily</label>
                    </div>
                    <div>
                        <input type="radio" name="backupType" id="backup-db">
                        <label for="backup-db">Weekly</label>
                    </div>
                    <div>
                        <input type="radio" name="backupType" id="backup-db">
                        <label for="backup-db">Monthly</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-10 col-md-12">
            <div class="backup-rounded">
                <div class="font-medium text-xl">Backup Type</div>
                <div class="d-flex gap-5 justify-content-between my-2 w-100 text-xl">
                    <div>
                        <input type="radio" name="backupType" id="backup-all">
                        <label for="backup-all">Files + Database</label>
                    </div>
                    <div>
                        <input type="radio" name="backupType" id="backup-files">
                        <label for="backup-files">Only Files (Exclude Database)</label>
                    </div>
                    <div>
                        <input type="radio" name="backupType" id="backup-db">
                        <label for="backup-db">Only Database</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-10 col-md-12">
            <div class="backup-rounded">
                <div class="font-medium text-xl">Target Location</div>
                <div class="d-flex flex-column gap-3 justify-content-between my-2 text-xl w-100">
                    <div class="align-items-start d-flex gap-3">
                        <input type="radio" name="backupType" class="mt-2" id="backup-all">
                        <label for="backup-all"> Save backups on localhost (web server)</label>
                    </div>
                    <div class="align-items-start d-flex gap-3">
                        <input type="radio" name="backupType" class="mt-2" id="backup-files">
                        <label for="backup-files">Send backups to remote storage (You can choose whether to keep the
                            backup in localhost after it is uploaded to cloud storage in Settings.)</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-md-12">
            <div class="d-flex flex-column gap-3">
                <div class="group">
                    <input type="checkbox" />
                    <label for="" class="font-medium text-xl">Enable backup schedule</label>
                </div>
                <button type="submit" class="btn btn-primary w-25 font-medium">Save Changes</button>
            </div>
        </div>
    </div>
</div>
