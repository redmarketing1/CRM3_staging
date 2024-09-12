@extends('layouts.main')

@push('css')
    <style>
        .backup-header {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .backup-options {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .backup-options input[type="radio"] {
            margin-right: 0.5rem;
        }

        .backup-options label {
            margin-right: 2rem;
        }

        .backup-options input[type="checkbox"] {
            margin-left: 1rem;
        }

        .backup-now-btn {
            margin-top: 1rem;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .backup-container>.nav-tabs {
            border-bottom: 0;
        }

        .backup-container>.tab-content .tab-pane.active {
            background: white;
            padding: 50px 30px;
            border-color: #dee2e6;
            border-width: 1px;
            border-style: solid;
            margin-top: 0px;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
            border-top-right-radius: 10px;
        }

        .backup-container>.nav-tabs .nav-link.active {
            background: #ffffff !important;
            box-shadow: none;
            border-bottom: 2px solid #fff;
        }

        .backup-container~.nav-item:first-child~.nav-link:is(.active)+.tab-content {
            background-color: rgb(254, 0, 0) !important;
            color: #000;
        }

        .backup-container>.nav-tabs button:hover,
        .backup-container>.nav-tabs button:focus {
            filter: none !important;
            cursor: pointer;
            user-select: none;
        }

        .backup-container>.nav-tabs .nav-link {
            font-size: 20px;
            font-weight: 500;
        }

        .backup-rounded {
            border: 1px solid #eee;
            padding: 15px 30px;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
    </style>
@endpush

@section('content')
    <div class="container" id="backup-container">
        <div class="backup-container">
            <ul class="nav nav-tabs" id="backupTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="backup-restore-tab" data-bs-toggle="tab"
                        data-bs-target="#backup-restore" type="button" role="tab" aria-controls="backup-restore"
                        aria-selected="true">
                        Backup & Restore
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule"
                        type="button" role="tab" aria-controls="schedule" aria-selected="false">Schedule</button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="remote-storage-tab" data-bs-toggle="tab" data-bs-target="#remote-storage"
                        type="button" role="tab" aria-controls="remote-storage" aria-selected="false">Remote
                        Storage</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings"
                        type="button" role="tab" aria-controls="settings" aria-selected="false">Settings</button>
                </li>
            </ul>

            <div class="tab-content" id="backupTabContent">

                @include('backup::tabs.backup_restore')
                @include('backup::tabs.schedule')

                @include('backup::tabs.settings')

            </div>
        </div>
    </div>

    @include('backup::tabs.progress')
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            //Load if backup section
            if ($('#backup-container').length) {

                $("#takes_backup").click(function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: "Are you sure to run Backup?",
                        showCancelButton: true,
                        confirmButtonText: "Yes, Backup Now"
                    }).then((result) => {

                        if (result.isConfirmed) {

                            fetch('/start-backup', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({})
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.backupId) {
                                        $('#backup_progress').show();
                                        trackBackupProgress(data.backupId);
                                    }
                                });
                        }

                    });
                });

                function trackBackupProgress(backupId) {
                    const pollingInterval = 5000; // Polling every 5 seconds
                    const expectedBackupTime = 100000; // Expected time for backup (e.g., 100 seconds)
                    const totalSteps = expectedBackupTime / pollingInterval; // Number of steps to complete backup
                    let progressPercentage = 0;
                    let startTime = Date.now(); // Store the start time of the backup

                    const intervalId = setInterval(() => {
                        fetch(`/check-backup-status/${backupId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'in-progress') {
                                    // Calculate percentage
                                    progressPercentage += (100 /
                                        totalSteps); // Increment by percentage step
                                    if (progressPercentage > 100) {
                                        progressPercentage = 100; // Cap at 100%
                                    }

                                    // Calculate elapsed time in seconds
                                    let elapsedTime = Math.floor((Date.now() - startTime) / 1000);
                                    let minutes = Math.floor(elapsedTime / 60);
                                    let seconds = elapsedTime % 60;
                                    let timeString =
                                        `${minutes}m ${seconds}s`; // Format time as "X minutes Y seconds"

                                    // Update the progress bar and running time
                                    $('.progress-bar').text(
                                        `Backup in progress... (${progressPercentage.toFixed(0)}%)`);
                                    $('.progress-bar').css('width', `${progressPercentage}%`)
                                    $('#backup_time').text(`Running Time: ${timeString}`);
                                } else if (data.status === 'completed') {
                                    $('.progress-bar').css('width', '100%')
                                    $('.progress-bar').text('Backup is completed.');
                                    setTimeout(() => {
                                        $('#backup_progress').hide();
                                    }, 9000);
                                    clearInterval(intervalId); // Stop polling once backup is done
                                }
                            });
                    }, pollingInterval);
                }
            }

        });
    </script>
@endpush
