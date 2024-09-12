<div class="container hide mt-5" id="backup_progress">
    <div class="bg-white border-1 border-grey px-4 py-2 d-hide rounded">
        <div class="my-3">
            <div class="h-50 progress">
                <div class="progress-bar text-white text-xl" role="progressbar" style="width: 25%;" aria-valuenow="25"
                    aria-valuemin="0" aria-valuemax="100">Staring...</div>
            </div>
        </div>
        <div class="d-flex gap-5 my-4">
            <div class="text-lg">
                <span class="space-right">Total Size:</span>
                <span>N/A</span>
            </div>
            <div class="text-lg">
                <span class="space-right">Uploaded:</span>
                <span>N/A</span>
            </div>
            <div class="text-lg">
                <span class="space-right">Speed:</span>
                <span>N/A</span>
            </div>
            <div class="text-lg">
                <span class="space-right">Network Connection:</span>
                <span>N/A</span>
            </div>
        </div>

        <div class="my-4">
            <p class="text-xl"><span id="backup_time"></span></p>
        </div>

        <div class="my-1">
            <button type="submit" value="Cancel" class="btn btn-gradient-dark px-3 py-2">
                Cancel
            </button>
        </div>
    </div>
</div>
