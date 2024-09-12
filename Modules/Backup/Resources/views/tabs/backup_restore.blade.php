<div class="tab-pane fade show active" id="backup-restore" role="tabpanel" aria-labelledby="backup-restore-tab">
    <div class="backup-header">Back Up Manually</div>

    <div class="row">
        <div class="col-md-6">
            <div class="flex">
                <div class="d-grid gap-1 text-xl">
                    <div>
                        <input type="radio" name="backupType" id="backup-all" checked>
                        <label for="backup-all">Database + Files (Laravel Files)</label>
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
        <div class="col-md-6">
            <!-- Backup Button -->
            <div class="form-group">
                <button id="takes_backup" class="btn btn-primary backup-now-btn">Backup Now</button>
            </div>
        </div>
    </div>
</div>
